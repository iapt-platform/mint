# WikiPali 研究型 Agent 设计文档

> 目标：让 Claude 这类 agent 用 WikiPali 的语料完成巴利文献研究——检索、取证、引用，最终产出可信的论文级文本。
>
> 与写入型 skill（`docs/wikipali-write-skill-design.md`）同属 `wikipali` 插件，共用坐标系、channel 模型与凭据。
>
> 状态：需求已定（§1 来自用户的真实工作流），API 盘点完成（§2 均已实测），三个阻塞缺口待决（§3）。**尚未动工**。
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
| 1 词形确认 | `GET /v2/dict?word={词}&lang=zh` | ✅ 可用，且能**词形还原** |
| 2/5 全文检索 | `GET /v2/search?view=pali&key=&limit=&offset=` | ❌ **500**（§3.1） |
| 5 出处分布 | `GET /v2/search-book-list?view=pali&key=` | ❌ **500**（同上） |
| 2' 逐词检索 | `GET /v2/search-pali-wbw?key=&bold=on\|off&book=&limit=` | ⚠️ 可用但只匹配**确切词形**（§3.2） |
| 2' 逐词分布 | `GET /v2/search-pali-wbw-books?key=` | ⚠️ 同上 |
| 标题检索 | `GET /v2/search?view=title&key=` | ✅ 可用（纯 DB，不走 gRPC） |
| 6 取段落 | `GET /v2/sentence?view=paragraph&book=&para=1,2,3&channels=` | ✅ 可用 |
| 7 取整章 | `GET /v2/sentence?view=chapter&book=&para=&channels=` | ✅ 可用（实测 22 句） |
| 7 目录导航 | `GET /v2/palitext?view=book-toc\|chapter\|children\|paragraph` | ✅ 可用 |
| 10 找译本 | `GET /v2/channel?view=public`、`sentence?view=paragraph&lang=` | ⚠️ `lang=` 分支待验 |

### 2.1 词典 —— 可用，而且比预想的强

`GET /v2/dict?word=parivāsaṃ&lang=zh` 返回的不只是释义，还有**形态分析**：

```
word: parivāsaṃ → parent: parivāsa,  type: .adj.,  grammar: .m.$.sg.$.acc.,  factors: parivāsa+[aṃ]
                 → parent: parivāseti, type: .v.,   grammar: .1p.$.sg.$.aor.
```

即：**给一个变化形，能还原出词根与语法**。步骤 1 的「验证拼写」因此是可靠的。

但注意方向：它做的是 **形 → 根**。研究场景真正需要的是 **根 → 全部形**（见 §3.2）。

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

## 3. 三个阻塞缺口

### 3.1 全文检索线上返回 500 —— 阻塞步骤 2/5，即整个流程的骨干

实测（www 与 next 均如此）：

```
GET /v2/search?view=pali&key=parivāsa&limit=3           → HTTP 500
GET /v2/search?view=pali&key=parivāsa dātabbo&limit=2   → HTTP 500
GET /v2/search-book-list?view=pali&key=parivāsa         → HTTP 500
GET /v2/search?view=title&key=parivasa                  → HTTP 200 ✅
```

只有走 gRPC 的分支挂，纯 DB 的分支正常 → 指向 `tulip` 服务不可达或 PHP 的 grpc 扩展缺失。**需要先确认线上搜索页现在是否正常**：如果网站上能搜，那是前端走了别的路；如果也不能，这是一个正在影响真实用户的故障，优先级高于本项目。

代码里还留着 `SearchController::pali()`——同样逻辑的纯 SQL 版（直接查 `fts_texts` 表），没有路由指向它。**这是现成的降级路径**：加一条路由或让 `pali_rpc` 在 gRPC 失败时回落到 `pali()`，就能在 tulip 修好前先用起来。

### 3.2 缺「词根 → 全部词形」的展开 —— 决定检索召回率

`wbw_templates.real` 存的是**变化形**：

| real | 出现 | 其中黑体 |
|---|---|---|
| parivāsaṃ | 221 | 7 |
| parivāso | 170 | 5 |
| parivāre | 174 | 67 |
| parivasanto | 146 | 0 |

所以按词典形 `parivāsa` 去逐词检索，**返回 0 条**（实测如此）。而词典端点只能做「形 → 根」，反过来没有端点。

后果：agent 若只用步骤 1 拿到的词典形去检索，会**静默漏掉绝大部分材料**——这是最危险的一类错误，因为它看起来成功了。

三条可能的解法，需要你定：

1. **服务端加端点**：`GET /v2/dict-forms?lemma=parivāsa` → 返回该词根在语料中出现过的全部 `real` 及频次（一条 `GROUP BY` 就够）。最干净；
2. **客户端前缀展开**：先 `search-pali-wbw?key=` 试几个常见词尾——脆弱，且要客户端懂巴利构词；
3. **靠全文检索的词干化**：Postgres 的 `pali` tsvector 配置若已做词干还原，则 §3.1 修好后此问题自动消失一半。**需要确认 `pali` 这个 text search configuration 到底做了什么**。

### 3.3 泰文语料未上传

已确认。公开 channel 里泰文只有 1 个、97 句；实际语料是缅文 68.7 万句 > 中文 20.6 万 > 英文 8.2 万。

对设计的影响：**工具必须能诚实回答「该段落在该语言下没有译文」**，而不是返回空数组让 agent 自己脑补。步骤 10 的「泰文译本（如果有）」在语料到位前应明确报「无」。

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
| `word <词>` | 1 | 词典 + 形态分析；输出词根、词性、语法 |
| `forms <词根>` | 1→2 | 展开该词根在语料中的全部实际词形（依赖 §3.2） |
| `search <词...>` | 2、5 | 全文/逐词二选一，回坐标 + 标题 + 路径 + 高亮摘要 |
| `dist <词...>` | 5 | 出处分布（按书/按部） |
| `get <坐标...>` | 6 | 按坐标批量取文，可指定 channel |
| `chapter <book> <para>` | 7 | 展开整章，先报体量再取 |
| `versions <坐标>` | 10 | 该坐标有哪些语言/译本，明确列出「没有的」 |

`research` skill 的规程重点不在于怎么调这些命令，而在于**怎么把结果变成可信引用**，以及**什么时候该收窄、什么时候该展开**。

---

## 6. 分阶段

| 阶段 | 内容 | 依赖 |
|---|---|---|
| R0 | 确认 §3.1 线上搜索故障范围；决定是修 tulip 还是先接 `pali()` 降级路径 | 你 |
| R0 | 决定 §3.2 的词形展开方案（倾向：服务端加 `dict-forms`） | 你 |
| R1 | `word` / `search` / `dist` / `get` 四个命令 + `research` skill 规程 | R0 |
| R2 | `chapter` / `versions` / `forms` | R1 |
| R3 | 用《别住在律藏中的案例分析》做**验收**：agent 独立跑完 11 步，人工核对每条引用的坐标真实性 | R2 |
| R4 | 视情况把读端改造为 MCP tools（检索链式调用更适合 tool 形态），skill 保留规程部分 | R3 |

R3 是这个项目真正的验收标准：**不是「命令都能调通」，而是「产出的论文里每一条引用都能回溯到真实坐标」**。
