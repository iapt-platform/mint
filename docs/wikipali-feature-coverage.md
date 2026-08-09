# WikiPali 功能覆盖清单

> 人类做佛教研究与翻译时用到的 WikiPali 功能（用户 2026-08-08 给出的清单），
> 对照 `wikipali` 插件当前的实现状态。
>
> 用途：逐项落实的工作清单。**标 ⬜ 的需要提供一个能跑通的完整 API URL**——
> 照着反推参数比读控制器快，也不会猜错。
>
> 插件版本基准：0.5.0（2026-08-08）

**图例**

| 记号 | 含义 |
|---|---|
| ✅ | 已实现，有命令 |
| 🔧 | 端点已确认可用，只差封装成命令 |
| ⚠️ | 能做，但体验或粒度不到位 |
| ⬜ | **需要 API URL**（端点可能存在但参数未知，或路由里没找到）|

---

## 一、研究（读取）

### 1. 字典

| 功能 | 状态 | 实现 / 端点 | 备注 |
|---|---|---|---|
| 词典释义 | ✅ | `wikipali word` → `GET /v2/dict?word=&lang=` | 释义在 `note` 字段，不是 `description` |
| 词形展开 | ✅ | `wikipali forms` → `GET /v2/case/{词}` | 检索的必经前置；拿词典形直接搜会 0 条且不报错 |

### 2. 术语

| 功能 | 状态 | 实现 / 端点 | 备注 |
|---|---|---|---|
| 术语表 | ✅ | `wikipali terms` → `GET /v2/term-vocabulary?view=&lang=` | 全表 17074 条（zh-Hans），本地缓存后过滤 |
| 单个术语查询 | ⬜ | `GET /v2/system-term/{lang}/{word}`？ | 实测返回 `{"ok":false,"message":"no channel"}` —— **缺参数** |

### 3. 三藏

| 功能 | 状态 | 实现 / 端点 | 备注 |
|---|---|---|---|
| 分类目录（如「长部的复注有哪些」）| ⬜ | `tag` / `tags-in-chapter` / `tag-map`？ | `GET /v2/tag?view=public` → 500。`GET /v2/book-title?view=public` 可用（200）。**缺参数** |
| 某本书的目录 | ✅ | `wikipali toc` → `GET /v2/palitext?view=book-toc&book=&para=` | 返回整套丛书，客户端按 book 过滤 |
| 章节内容 · 查有哪些版本 | ⚠️ | `wikipali versions` → `GET /v2/channel?view=paragraphs&book_id=&para=` | **只能按段落查**；按章节查目前用章节起始段近似 |
| 章节内容 · 读某一版本 | ✅ | `wikipali chapter <坐标> --fetch --channel <uid>` | 先报体量（`chapter_strlen`）再取 |
| 段落内容 · 多版本 | ✅ | `wikipali versions` → `wikipali get <坐标> --channel <uid>` | 查存在的版本，一次读一个 |
| 句子内容 · 多版本 | ✅ | 同上 | 句子是 `get` 的最小返回粒度，带 `word_start`/`word_end` |
| 相似句 | ⬜ | `GET /v2/sent-sim`？ | 实测 500。库里 `sent_sims` 表约 3.6 GB。**缺参数** |

### 4. 文章

| 功能 | 状态 | 实现 / 端点 | 备注 |
|---|---|---|---|
| 文章 | 🔧 | `GET /v2/article?view=public&limit=` | 实测 200 可用，未封装 |
| 文集 | 🔧 | `GET /v2/anthology?view=public&limit=` | 实测 200 可用，未封装（控制器是 `CollectionController`）|

### 5. 相关经文（根本 ↔ 义注 ↔ 复注）

| 功能 | 状态 | 实现 / 端点 | 备注 |
|---|---|---|---|
| 相关段落 | 🔧 | `GET /v2/related-paragraph?book=&para=` | 已评估：63 万行 / 217 部书，段落覆盖率 97.9–99.9%，双向可用，带 tags 可标层次。服务端「无关联时 500」已修复。**命令待做（0.6.0）** |
| 相关章节 | ⬜ | ? | 路由里没找到 |
| 相关书 | ⬜ | ? | 路由里没找到；`related-paragraph` 的返回里有书级信息，但不确定是否等价 |

### 6. 评论

| 功能 | 状态 | 实现 / 端点 | 备注 |
|---|---|---|---|
| 章节评论 | ⬜ | `discussion` / `discussion-count`？ | **缺参数** |
| 段落评论 | ⬜ | 同上 | **缺参数** |
| 句子评论 | ⬜ | `discussion` / `sent-discussion-tree`（POST）/ `discussion-anchor/{id}`？ | `GET /v2/discussion?view=sentence&res_id=x` → 500。**缺参数** |

---

## 二、写入

| 功能 | 状态 | 实现 / 端点 | 备注 |
|---|---|---|---|
| 句子 | ✅ | `wikipali write` | 模型身份署名、写前确认、count 核对、401 自动重签一次 |
| 术语 | ⬜ | `POST /v2/terms`（`DhammaTermController`）？ | `GET /v2/terms?view=public&key=` → 500。**缺参数** |
| 评论 · 句子 | ⬜ | `POST /v2/discussion`？ | **缺参数** |
| 修改建议 | ⬜ | ? | 路由里没找到独立端点；`SentResource` 里有 `suggestionCount`，代码里有 `SuggestionApi` |
| 文章 | 🔧 | `POST /v2/article` | 端点在，未封装 |
| 文集 | 🔧 | `POST /v2/anthology` | 端点在，未封装 |

---

## 三、需要提供 URL 的清单（共 8 项）

按对研究流程的价值排序：

1. **相似句** `sent-sim` —— 对读与校勘的核心能力，数据量最大（3.6 GB）
2. **分类目录** `tag` 系列 —— 「长部的复注有哪些」这类浏览，是研究的起点
3. **单个术语查询** `system-term/{lang}/{word}` —— 现在只能靠全表缓存过滤
4. **相关章节** —— 有了相关段落，章节级对应能省大量往返
5. **相关书** —— 同上
6. **句子评论（读）** `discussion` —— 前人对某句的讨论是重要的二手材料
7. **术语写入** `terms` —— 研究产出的术语能回流
8. **修改建议** —— 写入侧的协作能力

每项给一个**能跑通的完整 URL**即可（含参数与示例值），我照着反推。

---

## 四、已排期

| 版本 | 内容 | 依赖 |
|---|---|---|
| 0.6.0 | `related`（相关段落）· `article` / `anthology`（文章与文集读取） | 无，可立即开工 |
| 待定 | 上面 8 项，收到 URL 后按价值排 | 用户提供 URL |
| 待定 | 按章节聚合分布（`dist --by chapter`），方案见 `wikipali-research-agent-design.md` §3.7 | 方案待定 |
| 待定 | 短语检索改走 `/v3/search`（OpenSearch），见 §3.6 | v3 调试完成 |
| 待定 | `versions` 支持按章节查（现在只能按段落，章节用起始段近似） | 可能需要服务端支持 |

---

## 五、两个记录在案的判断

**多版本不做并排对照。** 一度考虑把巴利原文、缅文 nissaya、汉译按 `(book, paragraph,
word_start, word_end)` 四元组对齐后并排输出。用户 2026-08-09 否定：正确做法是
**先查有哪些版本，一次只读一个**。理由是上下文预算——一次拉多个完整版本会直接撑爆，
而研究时本来就是逐个版本读。所以 `versions` → `get/chapter --channel` 这条链就是最终形态。

**`versions` 的粒度缺口。** 它按段落查，而用户的用法是「给章节编号，查存在的版本」。
目前只能用章节起始段近似——同一章内不同段落的版本覆盖可能不同（某译本只译了半章）。
是否需要章节级的查询，取决于实际使用中这个近似会不会出错。
