# 巴利文检索召回优化

服务对象是 agent（问藏后端、wikipali-plugins）：它们按问题选几个巴利词去检索，读前若干段回答。
召回差就答不出来，或拿不相干的段落凑答案。本文记录已做的优化、评测方法与结果，以及下一波计划。

相关代码：`app/Services/OpenSearchService.php`（`search()` 及 `build*Query`、`wordRarityWeights`），
测试 `tests/Feature/OpenSearchQueryBuilderTest.php`；插件 `wikipali find` / `phrase`。

## 一、现状（2026-11）

### 检索入口

| 入口 | 做法 | 擅长 |
| --- | --- | --- |
| `v2/search-pali-wbw` | 一个词的全部词形（`v2/case` 展开）在 `wbw_templates.real` 上 OR，按 `sum(weight)` 排 | 单个词查全；`bold=on` 只看黑体 |
| `v3/search?search_mode=fuzzy` | 多词按原始词逐项累加固定分：整句原样、相邻两词原样、拼写相同、同义词可替代 | 几个概念同时出现的段落；找某句话 |
| `v3/search?search_mode=definition` | 每个词命中单独黑体 / 多词黑体给分，正文只作次级排序 | 义注里把某词当词条解释的段落 |
| 插件 `wikipali find` | 逐词 + fuzzy 按名次融合（RRF，k=60），前 3 段沿用逐词排序 | 回答具体问题的第一轮检索 |

### 本波做了什么（社区实践 → 本项目的落地）

1. **IDF 式罕见度加权**（BM25 的 IDF 公式）：多词查询里常见词（到处是黑体的 `bojjhaṅga`）
   与罕见的目标词同分，把前排占满。现在每个词的单词子句乘上罕见度权重：文档频率一次
   `_msearch` 取齐、逐词缓存一天；IDF 取对数后差距太小，归一后再取 `RARITY_EXPONENT` 次幂；
   下限 `RARITY_WEIGHT_FLOOR`。只作用于巴利文多词的 fuzzy / definition。
2. **多路检索 + Reciprocal Rank Fusion**（RAG-Fusion）：逐词检索与合并检索各有对方漏掉的题，
   用 RRF 按名次合并（`1/(60+rank)` 相加），不用对齐两边分数的尺度。前 3 段（agent 读全文的
   部分）沿用逐词检索的轮流排序，其余名额按 RRF 补入。实现在插件 `find`。
3. **选词规则写进 agent 的规程**（query rewriting 的人工版）：选答案段落里会出现的词、
   引文用原文词形、列举类带成员、不用泛用词。只写原则，不写具体查询。

## 二、评测方法

两套评测，脚本见下节「复现」。

- **调参集**（11 题）：问藏里的典型问题。标准段落 = 答案必须同时出现的几个概念，各自用
  `v2/case` 展开、`search-pali-wbw` 取全部段落再取交集——只用 v2 逐词表，不经 OpenSearch，
  避免拿新接口给自己判卷。分两组跑：agent 当时实际选的词、选对的词。
- **留出集**（150 题）：问藏真实问题，排除调参集，固定种子随机抽。标准 = agent 历次回答里
  实际引用过的段落坐标；关键词 = 该问题出现最多的 `pali_terms` 前 3 个。这个标准**偏向现状**
  （引用本来就是现有检索找到的），新方法在这里持平即说明不倒退。

指标：在**相同阅读窗口**（现状逐词检索合并后的段数）里命中几个标准段落；读全文的前 3 段命中几个；
逐题统计相对现状「更好 / 持平 / 更差」。

## 三、结果（2026-11-11）

### 留出集（148 题，判定标准：大部分题更好或持平）

融合方式离线比较（同一份检索结果，只换合并方法）：

| 方法 | 窗内命中 | 相对现状 好/平/差 | 前 3 命中 | 相对现状 好/平/差 |
| --- | --- | --- | --- | --- |
| 现状：逐词检索 | 296 | — | 88 | — |
| 只用 definition | 200 | 18 / 67 / 63 | 74 | 27 / 83 / 38 |
| 只用 fuzzy | 215 | 36 / 49 / 63 | 65 | 28 / 71 / 49 |
| RRF 等权（逐词 + fuzzy + definition） | 311 | 31 / 98 / 19 | 80 | 24 / 93 / 31 |
| **保头部 + RRF（逐词 + fuzzy）← 采用** | **325** | **33 / 100 / 15（89%）** | **88** | **0 / 148 / 0** |

- 单用新接口都不如逐词，**只能补充，不能替代**。
- 直接 RRF 会把前 3 段换掉；前 3 段是 agent 读全文的部分，标准又偏向现状，所以采用
  「前 3 段沿用逐词的轮流排序，其余按 RRF」——窗内命中 +10%，前 3 段不变。
- 再加 definition 一路没有额外收益（18 道定义类问题上也一样），所以 `find` 不放它。

罕见度加权（指数 4 对比不加权，逐题）：fuzzy 好 25 / 平 113 / 差 10，definition 好 24 / 平 117 / 差 7。
指数 1 介于两者之间。采用指数 4。

### 调参集（11 题）

| 关键词 | 现状 窗内 / 前3 | `find` 窗内 / 前3 | 只用 fuzzy 窗内 |
| --- | --- | --- | --- |
| agent 当时选的词 | 21 / 5 | 20 / 5 | 26 |
| 选对的词 | 28 / 8 | 33 / 8 | 80 |

- 选词决定上限：同样是逐词检索，选对词从 5 题有命中提高到 8 题；这只能靠规程（选词规则）解决。
- 选对词时，合并检索的增益明显（只用 fuzzy 窗内 80，约为现状的 3 倍）；`find` 为了保住前 3 段，
  只吸收了其中一部分。

### 已知局限

- 两套标准都是近似：调参集用「概念同时出现」代替人工相关性；留出集的引用来自现状检索，偏向现状。
- 留出集的关键词是 agent 当时选的，没有体现新的选词规则——规则的效果要等 agent 用新规程重跑才能测。

## 四、下一波：向量与混合检索

本波只用现有索引能力。下面几项需要向量、模型或重建索引，放到下一波。

### 4.1 混合检索：BM25 + 向量，RRF 融合

- 索引已有 `content.vector` / `summary.vector` / `title.vector`（OpenAI embedding，1536 维，
  faiss HNSW innerproduct），`search_mode=semantic` / `hybrid` 已存在。
- 现在的 `hybrid` 是把 fuzzy 包成 `constant_score` 与 knn 分数直接相加，两边尺度不同，
  权重靠 `fuzzy_ratio` / `semantic_ratio` 硬调。社区做法是按名次融合（RRF）。
- OpenSearch 2.19 起有 `score-ranker-processor`（search pipeline 里的 RRF）；本地是 2.18，
  要么升级，要么在 PHP 里按名次合并（与插件 `find` 同一套算法）。
- 价值：用户的中文问题可以直接召回巴利段落，**不再依赖 agent 选对巴利词**——这是评测里
  头号瓶颈（调参集 11 题里 6 题因选词失败）。

### 4.2 换领域向量模型

- OpenAI 通用 embedding 对巴利文的效果没有评测过。Dharmamitra 发布了佛典专用的跨语言模型
  （`buddhist-nlp/mitra-qwen35-embedder`，4096 维；2B 版 2048 维），支持巴利 / 梵 / 藏 / 汉互检，
  模型卡称在跨语言检索上优于 BGE-M3、Qwen3-Embedding；**没有找到针对巴利文的公开评测**。
- 做法：抽样几千段建临时索引，用本文的两套评测对比 OpenAI 与 MITRA，再决定是否全量重算。
- 维度变化要重建 knn 字段（新索引 + 别名切换）。

### 4.3 重排（rerank）

- agent 只读前 3 段全文；融合后取前 50–150 段，用 cross-encoder（多语言如 bge-reranker-v2-m3）
  或便宜的 LLM 对「问题 × 片段」逐段打分再排。重排只能调整顺序，找不回没召回的段落，
  要放在 4.1 之后。

### 4.4 段落补上下文（Contextual Retrieval）

- 建索引时把章节路径（`path`）、段落标题拼进被检索的文本与向量输入；`summary.text` 现在是空的，
  可放中文摘要。解决「答案段落里没有主语」（偈颂里没出现人名，人名只在章节标题里）。
- 本波评测里试过只在查询时匹配 `path`：失败题的标准段落路径里没有可借力的词，收益不明显，
  所以没做；随 4.1 一起在建索引时做。

### 4.5 词根字段

- 现在全部词形靠同义词词典（`pali_synonyms`）展开，词典缺词形（如 `vitakkāni`）、复合词与
  连音（`koṭisanthāra`、`vassaṃvutthānaṃ`）会漏。`wbw_templates` 里已有拆好的词与词根，
  可给每段写一个词根字段进索引，比维护同义词表可靠。需要重建索引。

### 4.6 章节标题检索走 OpenSearch（取代 `/v3/tipitaka-chapters` 里的 PHP 匹配）

2026-11-11 新建了 `GET /v3/tipitaka-chapters`（取代 v2 `search-title-index`），`q` 的容错匹配写在 PHP 里
（`App\Services\V3\TipitakaChapterService`：去变音符号、去序号与丛书缩写、三字母粗筛 + 编辑距离，
外加公开译本的中文章节名），**只用来先验证「按章节名定位」的效果**。验证结果见 agent-poc
`agent-tuning/docs/retrieval-recall-2026-11.md` 第七节：插件 `locate` 一条路就让 148 道真实问题里
能作答的从 40 题（`find` 前 3 段）到 54 题，与 `find` 互补后 74 题。

正式实现要放到 OpenSearch，插件 `locate` 改调 `/v3/search`。

**已有的基础**：`opensearch:index-tipitaka --granularity=chapter`（`IndexTipitaka::indexChapter`）为每个
「章节 × channel」建一份文档（`tipitaka_chapter_{book}-{para}_{channel}`，跳过逐词 channel）：标题是该 channel
在章节起始段的第一句——巴利原文 channel 就是三藏标题，译文 channel 就是译文标题——正文是这一章到下一个标题之前
的文本，`related_id = "{book}-{para}"`，`granularity` 是 `chapter`（level 1 为 `book`）。所以**三藏标题与各译本的
章节标题都已在索引设计里**，不需要另建字段。差距（本地索引 `wikipali_20260813` 只建了 paragraph 与 session，
章节文档要先补建）：

| 需要 | 现状 | 要做 |
| --- | --- | --- |
| 一章只出一条 | 每个 channel 一份文档，同一章会出几十条 | 查询用 `collapse: {field: related_id}`（keyword，可折叠），`inner_hits` 带回命中的是哪个标题 |
| 不出现私有译本 | 索引不分 channel 公开与否，私有译本的标题、正文都会被搜到；文档里也没写 `metadata.channel`（`search()` 的 channel 过滤对章节文档无效） | 建索引时写 `metadata.channel` 与公开状态，查询按公开过滤（或只索引公开 channel） |
| 不分变音符号 | `title.text.pali` 是 standard + lowercase，`ā` ≠ `a` | 加子字段：asciifolding + 去序号与括号缩写的 char_filter |
| 名字的一部分（`parinibbāna` 找 Mahāparinibbānasuttaṃ） | 复合词标题是一个 token | 加 3-gram 子字段，或对折叠后的 keyword 用 wildcard |
| 拼错一两个字母 | — | 折叠子字段用 `fuzziness: AUTO`；长复合词错得多时靠 ngram 的 `minimum_should_match` |
| 译文标题进对的语言字段 | 只有 `zh*` 写进 `title.text.zh`，其余语言（en、my…）都写进 `title.text.pali`（代码里有 TODO） | 按 channel 语言分字段；非巴利、非中文的另设字段或不进标题检索 |
| 坐标与体量 | 只有 `related_id` 字符串；章节文档没有 `path`、段数、字符数、layer | 写入 book / para / level / chapter_len / chapter_strlen / path / layer，`locate` 才能直接给出体量与路径 |
| 每本书最后一章 | `indexChapter` 求最后一章的结束段时调用了 `$model->value('paragraph')`，它会新开一个不带条件的查询，返回的是全表第一行的段号（实测 book 162 得到 858，真实最后一段是 4669），于是最后一章的范围为空、**没有被索引** | 改成 `PaliText::where('book', $book)->max('paragraph')` |

做法：新 `search_mode=title`（或 `granularity=chapter` 时的默认），`OpenSearchService` 里建查询；插件 `locate`
改调 `/v3/search`，输出保持不变。**验收**：用 `agent-tuning/eval/recall/eval_navigate.py` + `eval_judge.py`
重跑，locate 一路不低于 PHP 版（留出集 54/148、调参集 10/11）。`/v3/tipitaka-chapters` 继续作为 v2 替代与
`toc`（按书列标题）的数据源，PHP 匹配不再扩展。

## 复现

评测脚本目前放在开发机，没有进仓库：

- `eval_questions.py`：调参集；`python3 -I eval_questions.py out.json [good]`
- `eval_holdout.py`：留出集；`python3 -I eval_holdout.py qa_items.json out.json 150`
- `qa_items.json`：从 `agent-poc/agent-tuning/data/raw/mail/*/body.txt` 抽出的问答条目

都只调用本地 API（`http://127.0.0.1:8000/api`）。
