# WikiPali 功能覆盖清单

> `wikipali` 插件对 WikiPali API 的封装进度，按 **Library / Workspace** 两大块组织。
>
> 用途：逐项落实的工作清单。**标 ⬜ 的需要提供一个能跑通的完整 API URL**——
> 照着反推参数比读控制器快，也不会猜错。
>
> 插件版本基准：**0.8.2**（2026-08-10，未发布；marketplace 上是 0.7.0）

## 两大块的分界

| | **Library** | **Workspace** |
|---|---|---|
| 身份 | **无需登录** | 需登录，操作自己账号里的数据 |
| 语义 | 读公共语料与公开内容 | 管理属于你的东西 |
| 产出去向 | **只能输出到控制台或本地文件** | 写回 WikiPali |
| 出错的代价 | 读到错的东西 | **改坏别人看得见的数据** |

这条线不是「读 / 写」——公开文章的阅读属于 Library，而「列出我可编辑的 channel」
虽然是读，却属于 Workspace，因为它依赖身份。**凡是需要 token 的都在 Workspace。**

**图例**：✅ 已实现 · 🔧 端点可用只差封装 · ⚠️ 能做但不到位 · ⬜ 需要 API URL

---

## 一、Library（读取，无需登录）

### 1. 字典

| 功能 | 状态 | 命令 → 端点 |
|---|---|---|
| 词形展开 | ✅ | `forms` → `GET /v2/case/{词}` |
| 词典释义与形态分析 | ✅ | `word` → `GET /v2/dict?word=&lang=` |
| 词频合计 | ✅ | `count` → 同 `case` |

### 2. 术语

| 功能 | 状态 | 命令 → 端点 |
|---|---|---|
| 术语表（社区总表） | ✅ | `terms` → `GET /v2/term-vocabulary?view=&lang=`（17074 条，本地缓存后过滤） |
| **单个术语查询** | 🔧 | `GET /v2/terms?view=word&word={词}` —— **2026-08-10 实测：不需要登录**，返回各 channel 下该词的译义 |
| **按 tag 查术语** | 🔧 | `GET /v2/terms?view=tag&tag={tag}` —— 同样无需登录（`vinaya` 21 条） |
| ~~`system-term`~~ | — | `GET /v2/system-term/{lang}/{word}` 实测报 `no channel`；上面两个已够用，不再需要它 |

⚠ `term-vocabulary` 与 `terms` 是**两套东西**：前者是社区总表（一次拉全量），
后者是 `dhamma_terms`，按 channel / studio / 用户组织，同一个词在不同 channel 下
可以有不同译义。

### 3. 三藏

| 功能 | 状态 | 命令 → 端点 |
|---|---|---|
| 分类目录（按 tag 找书） | ✅ | `books` → `GET /v2/book-title`（服务端已扩展返回 toc/tags/related_name） |
| 某本书的章节目录 | ✅ | `toc` → `GET /v2/palitext?view=book-toc` |
| 章节体量与导航 | ✅ | `chapter` → `GET /v2/palitext/{book}-{para}` |
| 整章内容 | ✅ | `chapter --fetch` → `GET /v2/tipitaka-content/{book}-{para}` |
| 按坐标取句 | ✅ | `get` → `GET /v2/sentence?view=paragraph` |
| 某坐标有哪些版本 | ✅ | `versions` → `GET /v2/channel?view=paragraphs` |
| 检索 | ✅ | `search` → `GET /v2/search-pali-wbw` |
| 出处分布 | ✅ | `dist` → `GET /v2/search-pali-wbw-books` |
| 短语检索 | ⬜ | `/v3/search`（OpenSearch）调试中 |
| 相似句 | ⬜ | `GET /v2/sent-sim`？实测 500 |

### 4. 相关经文（根本 ↔ 义注 ↔ 复注）

| 功能 | 状态 | 命令 → 端点 |
|---|---|---|
| 相关段落 | ✅ | `related` → `GET /v2/related-paragraph` |
| 相关章节 | ⬜ | 路由里没找到 |
| 相关书 | ⬜ | 路由里没找到 |

### 5. 文章（公开）

| 功能 | 状态 | 命令 → 端点 |
|---|---|---|
| 文章列表与搜索 | ✅ | `articles` → `GET /v2/article?view=public` |
| 读单篇 | ✅ | `article <uid>` → `GET /v2/article/{uid}` |
| 文集 | ✅ | `anthology` → `GET /v2/anthology` |

### 6. 讨论（公开阅读）

| 功能 | 状态 | 命令 → 端点 |
|---|---|---|
| 读某句/段/章的讨论 | ⬜ | `GET /v2/discussion`？实测 500，**缺参数** |

---

## 二、Workspace（需登录，管理自己账号里的数据）

### 1. auth —— 身份与凭据

| 功能 | 状态 | 命令 → 端点 |
|---|---|---|
| 登录 | ✅ | `wikipali-login` → `POST /v2/sign-in` |
| 当前用户 | ✅ | `whoami --check` → `GET /v2/auth/current` |
| 建立/更新 AI 模型记录 | ✅ | `ensure-model` → `GET/POST/PUT /v2/ai-model` |
| 取模型身份 token | ✅ | `ensure-model` → `GET /v2/ai-model-token/{uid}` |
| 撤销模型全部 token | ✅ | `revoke` → `DELETE /v2/ai-model-token/{uid}` |
| 忘记/重置密码 | ⬜ | `/v2/auth/forgot-password`、`/v2/auth/reset-password`。**不打算封装**——涉及密码流程，应走网页 |

### 2. channel —— 译本/版本的容器

| 功能 | 状态 | 命令 → 端点 |
|---|---|---|
| 列出我可编辑的 channel | ✅ | `channels` → `GET /v2/channel?view=user-edit` |
| 签发 access token | ✅ | `grant` → `POST /v2/access-token` |
| 新建 channel | ⬜ | `POST /v2/channel`（源码已读：需 `studio`/`name`/`type`/`lang`） |
| 修改 channel | ⬜ | `PUT /v2/channel/{uid}`、`PATCH /v2/channel` |
| 我的 channel 数量 | ⬜ | `GET /v2/channel-my-number` |
| 按名字查 channel | ⬜ | `GET /v2/channel-name/{name}` |
| 进度统计 | ⬜ | `POST /v2/channel-progress` |
| 协作授权 | ⬜ | `/v2/share`（power ≥ 20 即可编辑） |

### 3. tipitaka —— 句子与修改建议

| 功能 | 状态 | 命令 → 端点 |
|---|---|---|
| 写入/覆盖句子 | ✅ | `write` → `POST /v2/sentence` |
| 逐句多版本结构（写入侧用） | ✅ | `chapter --fetch --via chapter-content` → `GET /v2/chapter-content/{id}` |
| 取自己 channel 的句子 | ✅ | `get --channel` → `GET /v2/sentence?view=paragraph` |
| 修改建议（sentpr） | ⬜ | `/v2/sentpr`、`POST /v2/sent-pr-tree`。**缺参数** |
| 逐词标注（wbw） | ⬜ | `/v2/wbw-sentence`、`/v2/editable-sentence` |
| 章节内句子批量 | ⬜ | `/v2/sentences-in-chapter`、`/v2/sent-in-channel` |

### 4. article —— 自己的文章与文集

| 功能 | 状态 | 命令 → 端点 |
|---|---|---|
| 新建/修改/删除文章 | ⬜ | `POST/PUT/DELETE /v2/article` |
| 新建/修改文集 | ⬜ | `POST/PUT /v2/anthology` |
| 预览 | ⬜ | `PUT /v2/article-preview/{id}` |
| 我的文章数量 | ⬜ | `GET /v2/article-my-number`、`/v2/anthology-my-number` |
| 文章进度 / 导航 / 映射 | ⬜ | `/v2/article-progress`、`/v2/article-nav`、`/v2/article-map` |

### 5. terms —— 自己的术语表

同一个词在不同 channel 下可以有不同译义，所以术语是挂在 channel / studio / 用户上的
（`dhamma_terms` 表），与 Library 里那张社区总表不是一回事。

| 功能 | 状态 | 命令 → 端点 |
|---|---|---|
| 读我的术语 | 🔧 | `GET /v2/terms?view=user&search={关键词}` —— **需登录**，实测 test161 为 0 条 |
| 读某 studio 的术语 | 🔧 | `GET /v2/terms?view=studio&name={studio}` —— 需登录 |
| 读某 channel 的术语 | 🔧 | `GET /v2/terms?view=channel&id={channel}` |
| 新建 / 修改 / 删除术语 | ⬜ | `POST/PUT/DELETE /v2/terms`。**缺字段说明** |
| 按 channel 批量建术语 | ⬜ | `GET /v2/terms?view=create-by-channel` |
| 导入 / 导出 | ⬜ | `/v2/terms-export`、`GET /v2/terms-import` |
| 常用译义统计 | ⬜ | `GET /v2/terms?view=hot-meaning` |

行字段（实测）：`guid` / `word` / `meaning` / `other_meaning` / `note` / `tag` /
`language` / `channal`（原字段名如此拼写）/ `owner` / `editor_id`。

### 6. discussion —— 讨论

| 功能 | 状态 | 命令 → 端点 |
|---|---|---|
| 发表讨论 | ⬜ | `POST /v2/discussion`。**缺参数** |
| 讨论树 | ⬜ | `POST /v2/sent-discussion-tree` |
| 定位锚点 | ⬜ | `GET /v2/discussion-anchor/{id}` |
| 未读 / 计数 | ⬜ | `/v2/discussion-count` |

---

## 三、通往 1.0 的版本规划

原则：**版本号跟着实际能力走**，每个小版本对应一块能独立验收的能力；验收不过就不
升版本号。1.0 的含义是「Library 与 Workspace 的常用 API 都已封装，且都在生产环境
验证过」。

| 版本 | 内容 | 验收标准 | 依赖 |
|---|---|---|---|
| **0.8.x** ✅ | Library 主体 + Workspace 的 auth / channel 只读 / 写句子 | staging 端到端 15 项全过（2026-08-10） | 合 PR、发版 |
| **0.8.3** | next 生产环境复测 | 在 next 上重跑 staging 那 15 项，结果一致 | next 部署完成 |
| **0.9.0** | **sentpr 修改建议** | 对他人 channel 的句子提建议；列出收到的建议 | ⬜ 需要 URL |
| **0.9.1** | **article Workspace** | 建一篇文章 → 编进文集 → 改 → 删，全程可回滚 | 端点已在，需定参数 |
| **0.9.2** | **terms 全套**：Library 的单词/按 tag 查（无需登录）+ Workspace 的我的术语读写 | 查到某词在各 channel 下的译义；建一条自己的术语并读回、改、删 | 端点已确认，写侧需字段说明 |
| **0.9.3** | **短语检索切 `/v3/search`** | 词组检索可用；规程里「拆词绕行」的说明删除 | v3 调试完成 |
| **0.9.4** | **wbw 逐词标注**（读 + 写） | 读某句的逐词解析；提交一次修改 | 待评估 |
| **0.9.5** | **discussion** 全套（Library 读 + Workspace 写） | 读到某句的讨论串；发一条并读回 | ⬜ 需要 URL |
| **0.9.6** | **Library 补完**：相似句、相关章节 / 相关书 | 三项各跑通并进 `research` 规程 | ⬜ 需要 URL |
| **0.9.7** | **channel 管理** | 建一个 channel 并授权他人编辑 | 端点已在 |
| **1.0.0** | 全部封装 + **生产环境全量复测** + 文档定稿 | 四个线上站点重跑全部命令；`research` 规程用一篇真实论文任务验收 | 线上部署 |

### 每个版本都要做的三件事

1. **实测每个端点的三种响应**：正常 / 空结果 / 错误。**空结果必须与故障区分开**——
   这是本项目反复踩到的坑（`access-token` 的 `count: 0`、`chapter-content` 的空占位、
   `related-paragraph` 查无关联时的 500）。
2. **契约写进 references**，含踩过的坑。写侧的内容归 `api-write.md`。
3. **规程只写判断，事实进 references**；SKILL.md 超 150 行就往外搬。

### 1.0 之后

- **MCP server 形态**：读端的链式调用（检索 → 取章 → 对读）更适合 tool 而非 CLI。
  同一个插件可以同时带 `.mcp.json`——届时读走 MCP、写仍走 skill 规程，因为写入需要
  的是「确认再动手」的流程约束，那是 skill 的强项而非 tool 的。
- **新流程（如佛教百科）**：按既定结构加一个 `skills/<name>/SKILL.md` 即可，
  不复制代码、用户 `/plugin update` 就拿到。

---

## 四、待用户决定的规范问题

### ⬜ 引用格式：是否采用 `{{book-para-start-end}}`

平台原生格式（见用户所写《表24：三种别住》），实测精确到句、能在平台上解析定位。
待定：是否全面采用？论文里给人读的场合是否需要「可读书名 + `{{坐标}}`」的组合写法？

定案后改 `references/conventions.md` 的「引用格式」一节。

### ⬜ 译名分歧：术语表 vs 实际使用

`samodhānaparivāsa` 在术语表里是「合并别住」，用户文章里用「合一别住」。规程现在
要求「与术语表一致，不一致要说明理由」，若术语表并非唯一权威则需改写。

---

## 五、记录在案的判断

**多版本不做并排对照。** 正确做法是先查有哪些版本、一次只读一个——一次拉多个完整
版本会撑爆上下文，而研究本来就是逐个版本读。`versions` → `get/chapter --channel`
这条链就是最终形态。

**`versions` 的粒度缺口。** 它按段落查，而实际用法常是按章节查，目前用章节起始段
近似。同一章内不同段落的版本覆盖可能不同（某译本只译半章）。

**站点不是同一个库。** 线上四站共享库与密钥；`staging` 与 `local` 各是另一个库，
凭据与本地缓存都按桶隔离，自动 fallback 只在线上四站之间发生。**在 staging 上查到
的坐标不能直接拿到线上引用。**
