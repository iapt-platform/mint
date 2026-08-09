# WikiPali 研究型 Agent 设计文档

> 目标：让 Claude 这类 agent 用 WikiPali 的语料完成巴利文献研究——检索、取证、引用，最终产出可信的论文级文本。
>
> 与写入型 skill（`docs/wikipali-write-skill-design.md`）同属 `wikipali` 插件，共用坐标系、channel 模型与凭据。
>
> 状态：需求已定（§1 来自用户的真实工作流），API 盘点完成（§2 均已实测）。**主检索链路无阻塞，可以开工**（§3 修订：原列的三个缺口有两个已证伪）。
>
> 日期：2026-08-06

---

## 1. 需求：一次真实的论文写作

用户给的样本任务是《别住在律藏中的案例分析》，成文三部分：**定义与执行流程 → 案例分类列举 → 案例规律总结**。人工做法是 11 步：

| # | 动作 | 本质 |
|---|---|---|
| 1 | LLM 给出「别住」的巴利拼写（可能多个：名词、动词），**用词典验证** | 词形确认 |
| 2 | 全文检索，取前 50 条：标题 + 章节路径 + 巴利段落 | 定位 |
| 3 | 结果按黑体字加权排序，义注的名词解释自然排前 | 排序语义 |
| 4 | 据此写「定义与执行流程」 | 产出 |
| 5 | 同一检索取前 200 条，**分析出处分布** | 分布统计 |
| 6 | 提取这 200 条的段落内容 | 批量取证 |
| 7 | 对结果密集的章节，取**整章巴利全文** | 上下文展开 |
| 8 | 据 6、7 做案例分类 | 归纳 |
| 9 | 写「案例分类列举」 | 产出 |
| 10 | 查相关 channel（缅文逐词解析 nissaya、泰文译本等）**核对并补充引用** | 交叉验证 |
| 11 | 写「案例规律总结」 | 产出 |

这个序列有三个特征，决定了工具形态：

- **漏斗型**：定位（宽）→ 取证（窄）→ 展开（深）。不是「取一堆数据交给模型」，而是逐步收窄。
- **两次检索、两种用途**：第一次要**排序质量**（前 50 拿定义），第二次要**覆盖面**（前 200 看分布）。同一端点，不同参数。
- **交叉验证是最后一步不是第一步**：先用巴利原文做出判断，再拿译本核对。工具不该在早期就把多语版本一股脑塞进上下文。

---

## 2. API 盘点（2026-08-06 逐个实测）

基址 `{API}`，全部实测于 `https://www.wikipali.org/api`。**读端一律不需要凭据**——`PaliTextController` / `SearchController` / `SentencesInChapterController` 里 `AuthService::current` 出现 0 次，实测未登录直接返回数据。

| 步骤 | 端点 | 状态 |
|---|---|---|
| 1 词形展开 | `GET /v2/case/{词}` | ✅ **主链路第一步**：猜 lemma + 列出全部实际词形 |
| 1 释义验证 | `GET /v2/dict?word={词}&lang=zh` | ✅ 可用，附形态分析（形 → 根） |
| 2/5 检索 | `GET /v2/search-pali-wbw?key={词形,词形,…}&bold=&limit=&offset=&book=` | ✅ **主链路第二步** |
| 5 出处分布 | `GET /v2/search-pali-wbw-books?key={词形,…}` | ✅ 带 `paliTitle` 与 tags |
| — 词组全文检索 | `GET /v2/search?view=pali&key=`、`/v2/search-book-list` | ❌ **500**，走 gRPC（§3.1，非阻塞） |
| — 标题检索 | `GET /v2/search?view=title&key=` | ✅ 可用（纯 DB，不走 gRPC） |
| 6 取段落 | `GET /v2/sentence?view=paragraph&book=&para=1,2,3&channels=` | ✅ 可用 |
| 7 取整章 | `GET /v2/sentence?view=chapter&book=&para=&channels=` | ✅ 可用（实测 22 句） |
| 7 目录导航 | `GET /v2/palitext?view=book-toc\|chapter\|children\|paragraph` | ✅ 可用 |
| 10 找译本 | `GET /v2/channel?view=public`、`sentence?view=paragraph&lang=` | ⚠️ `lang=` 分支待验 |

### 2.0 主检索链路（用户提供，2026-08-06 实测）

**第一步：`GET /v2/case/{被搜索词}`** —— 输入可以是任意变格形，程序推测可能的词典原型，按可能性排序。

```
GET /v2/case/parivāsa  →  data: { rows: [ {word, count, case: [...] }, ... ], count }
```

取 `rows[0]`（可能性最高的 lemma），其 `case` 数组就是该词在语料中出现过的**全部实际词形**，每项带 `count` 与 `bold` 计数：

```
parivāsa (13 形): parivāsaṃ×221(黑7) · parivāso×170(黑5) · parivāse×22 · parivāsā×7 · parivāsesu×7 …
```

**第二步：`GET /v2/search-pali-wbw?key={把这些词形用逗号连起来}`**

```
count: 281 段落。rows 每项：
{ book, paragraph, rank, path[章节路径,含 level], paliTitle, highlight }
```

实测细节：

- `limit=200` 正常返回 200 行（步骤 5 取前 200 无碍；本例全库也就 281 段）；
- `view` 与 `type` 参数**实测无影响**，可省略（源码 `SearchPaliWbwController::index` 也没读它们）；
- `highlight` 用 `<span class='hl'>` 包命中词，并**保留原文的 `<span class="bld">`**——黑体信息在返回里可见；
- `rank` = `sum(weight)`，`bold=on|off` 直接按 `style='bld'` 筛。**`bold=on` 让本例命中从 281 降到 13**；
- 范围限定 `book=<id,id>` 或 `tags=<tag1,tag2;tag3>`（组间 OR、组内 AND）。

**分布**：`GET /v2/search-pali-wbw-books?key={词形,…}` 返回 43 部书，每项带 `paliTitle` 和 **tags**：

| 书 | 命中 | tags |
|---|---|---|
| (VN)Cūḷavaggapāḷi | 126 | vinaya, mūla, pāḷi, khandhaka, cūḷavagga |
| Vinayālaṅkāra-ṭīkā | 40 | ṭīkā, vinaya |
| (SP) Cūḷavagga-aṭṭhakathā | 28 | vinaya, aṭṭhakathā, samantapāsādikā |

tags 里的 `mūla` / `aṭṭhakathā` / `ṭīkā` 让 agent 能直接区分**本文、义注、复注**——步骤 5 的「分析出处分布」和步骤 8 的分类都要靠它。

**这条链路对步骤 3/4 有个更好的做法**：用户原方案是「取前 50，靠黑体加权让义注的名词解释排前面」。但既然有 `bold=on`，可以直接**只取黑体命中**（本例 13 条），那正是被注释书当作词条标出来的地方——比靠排序精准，而且省 90% 的上下文。

### 2.1 词典 —— 可用，而且比预想的强

`GET /v2/dict?word=parivāsaṃ&lang=zh` 返回的不只是释义，还有**形态分析**：

```
word: parivāsaṃ → parent: parivāsa,  type: .adj.,  grammar: .m.$.sg.$.acc.,  factors: parivāsa+[aṃ]
                 → parent: parivāseti, type: .v.,   grammar: .1p.$.sg.$.aor.
```

即：**给一个变化形，能还原出词根与语法**。步骤 1 的「验证拼写」因此是可靠的。

但注意方向：`dict` 做的是 **形 → 根**，用来确认「我选对了 lemma」。检索需要的 **根 → 全部形** 是 `case` 干的活（§2.0）。两者配合：`case` 给候选 lemma 和词形，`dict` 给释义和语法帮你确认选哪个候选。

### 2.2 全文检索 —— 形状正好对得上，但服务不通

`SearchController::index` 的 `view=pali` 分支走 `PaliSearch::pali_rpc()`，即 gRPC 调 `tulip` 服务（`config('mint.server.rpc.tulip.*')`）。返回经 `SearchResource` 整形为：

```json
{ "book": 93, "paragraph": 757, "rank": 0.87,
  "highlight": "……~~parivāsaṃ~~……",   // 命中词用 ~~ 包围
  "path": [...章节路径...], "paliTitle": "章节标题" }
```

**这正是步骤 2 要的三样东西**（标题、章节路径、巴利段落），客户端不用二次查询。几个契约细节：

- `key` 用 `;` 分隔多词 = OR；
- `match` = `case`（默认）/ `complete` / `similar`（去变音符号）；
- 范围限定用 `book=<单个 id>` 或 `tags=<tag1,tag2;tag3>`（tag 组间 OR、组内 AND）；
- 高亮标记是 `~~…~~`（`ts_headline` 的 `StartSel`），客户端要自己解析；
- ⚠️ `orderby` 参数**只在废弃的 `pali()` 方法里生效**，`pali_rpc()` 不读它——排序由 tulip 决定；
- ⚠️ `key` 以 `para` 开头或首字母是 `M/P/T/V/O` 会被劫持到**页码检索** `page()`。研究用词若撞上（如 `Para…`）会得到莫名其妙的结果。

排序权重 `{0.1, 1, 0.3, 0.2}` 对应 tsvector 的 D/C/B/A 四档，配合索引里的 `bold1/bold2/bold3` 字段——**这就是「黑体字加权」**，用户步骤 3 依赖的行为在服务端是坐实的。

### 2.3 逐词检索 —— 另一条路，语义不同

`search-pali-wbw` 走 `wbw_templates` 表：`WHERE real IN (逗号分隔词表) GROUP BY book,paragraph ORDER BY sum(weight)`，并支持 `bold=on|off` 直接筛黑体（`style='bld'`）。

它与全文检索是**两套东西**：前者匹配逐词解析的词形，后者匹配段落全文。dashboard 的做法是：关键词含空格 → 全文；单词 → 逐词。

### 2.4 取原文与译文 —— 同一个端点

关键结构事实：**巴利原文本身就是一个 channel**（`_System_Pali_VRI_`，uid `00b577c0-13b9-11ee-a05a-b7307efd9ee6`，`type=original`、`lang=pali`）。`SearchResource` 在没有 highlight 时也是去这个 channel 拼段落文本。

于是「取原文」「取汉译」「取缅文 nissaya」是**同一个调用换 channel**：

```
GET /v2/sentence?view=chapter&book=93&para=757&channels=<uid[,uid...]>
GET /v2/sentence?view=paragraph&book=93&para=757,758&channels=<uid>
```

读端与写端共用同一套坐标 `(book, paragraph, word_start, word_end, channel_uid)`。这意味着 agent 检索到的任何位置，都能直接对应到写入型 skill 能写的位置——**检索与产出天然闭环**。

现成的交叉验证资源（`status=30` 公开）：

| channel | lang | type |
|---|---|---|
| `Nissaya` / `nissaya` | my | translation / nissaya |
| `nissaya in En` | en | nissaya |
| `Norbu AI Translations (Nissaya)` | en-US | translation |

---

## 3. 缺口（2026-08-06 修订：原先列的三个，两个已被证伪）

主检索链路（`case` → `search-pali-wbw`）**完全可用，www 与 next 都有**。下面第一条降级为非阻塞，第二条不成立。

### 3.1 词组全文检索返回 500 —— 非阻塞，但确实坏了

实测（www 与 next 均如此）：

```
GET /v2/search?view=pali&key=parivāsa&limit=3           → HTTP 500
GET /v2/search?view=pali&key=parivāsa dātabbo&limit=2   → HTTP 500
GET /v2/search-book-list?view=pali&key=parivāsa         → HTTP 500
GET /v2/search?view=title&key=parivasa                  → HTTP 200 ✅
```

只有走 gRPC（`PaliSearch` → `tulip` 服务）的分支挂，纯 DB 的分支正常 → 指向 tulip 不可达或 PHP 的 grpc 扩展缺失。

**为什么不阻塞**：dashboard 只在关键词**含空格**（词组）时才走这条路，单词走 `search-pali-wbw`。研究流程的主链路是后者。所以坏的是「词组/短语检索」这一项能力。

**但它确实是坏的**，且影响真实用户。修法有二：修 tulip 服务；或接上代码里已有的 `SearchController::pali()`——同样逻辑的纯 SQL 版（直接查 `fts_texts` 表 + `ts_rank`），目前没有路由指向它，加一条路由或让 `pali_rpc` 在 gRPC 失败时回落即可。

### 3.2 ~~缺「词根 → 全部词形」的展开~~ —— 已证伪

原判断错在：我只看到 `dict` 能做「形 → 根」，没找到反向的端点，于是以为 agent 会用词典形检索而静默漏掉材料。

实际上 **`GET /v2/case/{词}` 就是反向展开**：给任意形，返回候选 lemma 及每个 lemma 在语料中的全部实际词形（带 count 与 bold 计数）。这条链路是平台既有的，不需要任何服务端改动。

保留这段记录是因为**结论虽错，风险是真的**：按词典形 `parivāsa` 直接查 `search-pali-wbw` 确实返回 0 条且不报错。所以 skill 规程必须写死「**检索前一律先过 `case` 展开词形**，不得直接拿词典形去搜」——否则 agent 会以为自己搜过了。

### 3.3 泰文语料未上传

已确认。公开 channel 里泰文只有 1 个、97 句；实际语料是缅文 68.7 万句 > 中文 20.6 万 > 英文 8.2 万。

对设计的影响：**工具必须能诚实回答「该段落在该语言下没有译文」**，而不是返回空数组让 agent 自己脑补。步骤 10 的「泰文译本（如果有）」在语料到位前应明确报「无」。

---

## 3.4 待办：引用格式规范

当前 `research` skill 用的是临时格式（用户 2026-08-06 同意暂用）：

```
Cūḷavaggapāḷi, Pārivāsikakkhandhaka (VN 216:35)
Samantapāsādikā, Pārivāsikavattakathā (SP-aṭṭ 141:63)
```

**发现（2026-08-09）**：平台自己就有引用格式。用户写的文章《表24：三种别住》里，
引用巴利原文用的是 `{{141-120-17-40}}` —— 即 `{{book-paragraph-word_start-word_end}}`，
**精确到句**。实测该坐标正是义注里讲 `odhānasamodhāna` 的那一句。

这比我临时定的格式好：它是平台原生的，写成这样的引用在 wikipali 上能直接解析定位。
**待用户确认是否采用**——若采用，`conventions.md` 的「引用格式」一节改为这个，
`research` 规程要求产出中的巴利原文引用一律用它。

**⬜ TODO：用户之后会给出正式的引用格式规范**，届时改 `skills/research/SKILL.md` 的「引用格式」一节。这关系到产出能否被同行接受，属于必改项，不是可选优化。

相关线索：库里有 `page_numbers` 表，`type` 分 `M/P/T/V/O`（缅甸版/PTS 等不同版本的页码），正式规范多半要用到其中某一种；`GET /v2/search?view=page&key=<卷.页>&type=<版本>` 是按页码反查段落的现成端点。

## 3.5 待办：channel 的译文来源标识

引用译文必须能区分人译与机译，但**现有数据两个信号都不可靠**（2026-08-06 实测）：

| channel | 句子数 | `editor_uid` 命中 `ai_models` |
|---|---|---|
| AI-汉译-Nissaya | 11206 | 11205（模型 `[文本生成]-阿里-deepseek-v3`）|
| Nissaya的AI翻译 | 967 | 0 |
| Norbu AI Translations (Nissaya) | 191 | 0 |

后两者是**人工用自己账号上传的机器译文**，`editor_uid` 是人类；只有名字里的 "AI" 泄露了来源。反过来，只靠名字也会漏掉命名里不含 AI 的机器译本。

短期：skill 规程用「两个信号任一命中即按机器译文标注，都不命中时不主动断言是人译」。

**⬜ TODO（服务端）**：给 `channels` 加一个来源字段（如 `provenance`: `human` / `machine` / `mixed`），让判定有据。注意写入型 skill 产生的数据天然带模型署名（`editor_uid` = 模型 uid），所以这个问题只存在于存量数据。

## 3.6 待办：短语检索改走 /v3/search（OpenSearch）

§3.1 记的「词组/短语检索 500」有下文：**`/v3/search` 已实现，改用 OpenSearch，2026-08-08
时点仍在调试**（用户告知）。

所以 §3.1 的两条修法（修 tulip / 接 `SearchController::pali()` 降级）都不必做了——等 v3
稳定后，客户端把短语检索指过去即可。届时要重新盘点 v3 的参数与返回形状，它大概率与
v2 的 `search-pali-wbw` 不同。

在那之前，`research` 规程里「把短语拆成词分别展开词形」的绕行办法继续有效。

## 3.7 待办：按章节聚合分布（`dist --by chapter`）

现在 `dist` 只能按**书**聚合。实跑「别住」时，「命中集中在第 2 犍度（规矩）与第 3 犍度
（授予程序）」这个结构性判断是人看 `search` 结果的 `path` 字段看出来的，工具没帮忙。

做法上有个成本问题：按书聚合服务端有现成端点（`search-pali-wbw-books`），按章节没有，
客户端得**拉全量命中**再按 `path` 的某一层聚合。`parivāsa` 是 281 段还好，几千段的常见词
就要分页拉很多次。

方案待定（用户 2026-08-08 要求稍后给）。可选方向：客户端全量拉 + 本地聚合（简单但慢）、
服务端加一个按章节 group by 的端点（快但要改服务端）、或者只对 `--book` 限定后的结果做
（把量压下来再聚合）。

---

## 4. 设计要点（端点清单看不出来的那些）

1. **引用可信度是第一约束**。论文场景下编造引文是致命错误。因此：任何返回给 agent 的文本片段，都必须携带可验证坐标（`book/paragraph` + channel + 章节路径），且 skill 规程要写死「没有坐标的内容不得写入论文」。这条决定所有命令的返回格式，事后改是全面返工。
2. **上下文预算是第二约束**。一部经几十万 token。命令粒度必须支持漏斗：`search`（只回坐标+摘要）→ `get`（按坐标取指定段落）→ `chapter`（展开整章，需显式请求且要报告体量）。**不提供「取整部书」这种命令。**
3. **channel 即译本/版本**，与写入端共用。「查某语言的译文」= 「查某 channel 在某坐标的句子」。不要为读端发明第二套概念。
4. **空结果必须显式**。区分「该位置没有该语言的译文」与「查询出错」，两者对 agent 的下一步完全不同。
5. **两种检索要都暴露**，并说明差别：全文（词组、黑体加权、义注优先）与逐词（确切词形、可筛黑体）。让 agent 知道什么时候用哪个，比藏起来自动选更可靠。

---

## 5. 命令面草案

沿用 `wikipali` 插件既有结构，读端加一个 skill：

```
plugins/wikipali/
├── bin/wikipali              # 共享入口（写端也迁过来）
├── skills/
│   ├── write/                # 现有
│   └── research/             # 新增：检索、取证、引用规程
```

子命令（对应 §1 的 11 步）：

| 命令 | 对应步骤 | 说明 |
|---|---|---|
| `forms <词>` | 1 | `case` 展开：候选 lemma + 全部实际词形（带 count/bold）。**检索的必经前置** |
| `word <词>` | 1 | `dict` 释义 + 形态分析（词根、词性、语法），用于确认选对了 lemma |
| `search <词形…>` | 2、5 | `search-pali-wbw`；`--bold` 只取黑体（定义）、`--book`/`--tags` 限范围 |
| `dist <词形…>` | 5 | 出处分布，带 tags（`mūla`/`aṭṭhakathā`/`ṭīkā`）便于区分本文与注疏 |
| `get <坐标…>` | 6 | 按坐标批量取文，可指定 channel |
| `chapter <book> <para>` | 7 | 展开整章，先报体量再取 |
| `versions <坐标>` | 10 | 该坐标有哪些语言/译本，明确列出「没有的」 |

一个便利设计：`forms` 的输出可以直接管道进 `search`，或者让 `search` 接受 `--lemma parivāsa` 自动先跑一遍 `case` 再检索。**但不要把展开做成隐式的**——agent 应当看见「我把这 13 个词形搜了」，那是论文方法论的一部分，要能写进正文。

`research` skill 的规程重点不在于怎么调这些命令，而在于**怎么把结果变成可信引用**，以及**什么时候该收窄、什么时候该展开**。

---

## 6. 分阶段

| 阶段 | 内容 | 依赖 |
|---|---|---|
| R1 | `forms` / `word` / `search` / `dist` / `get` 五个命令 + `research` skill 规程 | 无（主链路已可用） |
| R2 | `chapter` / `versions` | R1 |
| R2 | 词组检索的 500（§3.1）：修 tulip 或接 `pali()` 降级路径 | 你定 |
| R3 | 用《别住在律藏中的案例分析》做**验收**：agent 独立跑完 11 步，人工核对每条引用的坐标真实性 | R2 |
| R4 | 视情况把读端改造为 MCP tools（检索链式调用更适合 tool 形态），skill 保留规程部分 | R3 |

R3 是这个项目真正的验收标准：**不是「命令都能调通」，而是「产出的论文里每一条引用都能回溯到真实坐标」**。
