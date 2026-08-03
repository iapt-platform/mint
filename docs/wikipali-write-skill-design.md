# WikiPali 写入型 Skill 设计文档

> 目标：提供一个可复用的 Claude Code Skill，使**任意外部项目**都能通过 Claude Code 以「AI 模型身份」把句子写入 WikiPali 数据库（`SentenceController`），并保持正确的作者署名与权限边界。
>
> 状态：设计草案（未实施）
> 对应后端：`api-v13`（Laravel 13，路由前缀 `/api/v2`）

---

## 1. 背景与目标

现状下，只有仓库内部的组件（`ai-translate` Python worker、`app/Console/Commands/*`、`app/Services/AIAssistant/*`）能以 AI 身份写入句子库，因为它们能直接调用 `AuthService::getUserToken()` 生成模型身份 token。外部项目无此能力。

本 Skill 要达成：

1. 外部项目只需安装该 Skill，即可获得对 WikiPali 数据库的写入能力；
2. 写入的句子 `editor_uid` 为 **AI 模型的 uid**（而非操作者本人），保证署名与审计正确；
3. 权限不被放大：AI 模型只能写入「操作者本人有编辑权的 channel」，且受 `access_token` 中的 book 范围约束；
4. 凭据管理安全、可复用，不污染用户项目仓库。

### 非目标

- 不提供绕过 channel 权限的写入路径；
- 不在本期实现 wbw / sentpr / attachment 等其他资源的写入（见 §9 后续规划）。

---

## 2. 现有 API 盘点

以下均已对照源码核实。基址记为 `{API}`，形如 `https://host/api`（参见 `ai-translate/config.orig.toml` 的 `api-url`）。

### 2.1 登录 —— 可用 ✅

`POST {API}/v2/sign-in`

```json
{ "username": "<用户名或邮箱>", "password": "<明文密码>" }
```

返回 `{ "ok": true, "data": "<JWT 字符串>", "message": "" }`。

- 实现：`AuthController::signIn()`（`app/Http/Controllers/AuthController.php:70`）
- JWT payload：`{nbf, exp, uid: userid, id: 主键 id}`，**有效期 365 天**（`AuthController.php:84`）
- 校验入口：`AuthService::current()`（`app/Services/AuthService.php:43`），读取 `Authorization: Bearer <token>`

`GET {API}/v2/auth/current`（Bearer）→ `data: {id, nickName, realName, avatar, token, roles}`。
其中 `realName` 即 `user_info.username`，**后续 `studio_name` 参数要用它**（`AuthController.php:101`）。

### 2.2 查询 / 创建 AI Model —— 部分可用 ⚠️

`GET {API}/v2/ai-model?view=studio&name={studioName}&keyword={modelName}`（Bearer）

- 实现：`AiModelController::index()`（`app/Http/Controllers/AiModelController.php:22`）
- `view` 仅支持 `all` / `studio` / `usable` / `chat`；`keyword` 是 `like %kw%` **模糊**匹配，客户端需自行做精确 `name` 比对
- ⚠️ 缺陷：`view` 传入非法值时 `$table` 未定义 → 500（`AiModelController.php:29-45`）

`POST {API}/v2/ai-model`（Bearer）body `{name, studio_name}`

- 实现：`AiModelController::store()`（`AiModelController.php:75`）
- ⚠️ 只写入 `name / uid / real_name / owner_id / editor_id`，**不接受 `model` / `url` / `key` / `privacy` / `description`**
- ⚠️ 无重名校验，重复调用会产生多条同名记录
- 鉴权：`canEdit($user_uid, $studioId)` 要求 `user_uid === studioId`，即**只有个人 studio 可用，group studio 会 403**（`AiModelController.php:157`）

`PUT {API}/v2/ai-model/{uid}`（Bearer）

- 路由模型绑定按 `uid`（`AiModel::$primaryKey = 'uid'`）
- ⚠️ 用 `$request->input()` 无保护地整体覆盖，**未提供的字段会被置为 null**（`AiModelController.php:125-132`）。客户端必须一次性提交全部字段

### 2.3 获取 AI Model 的 user token —— **缺失** ❌

`AuthService::getUserToken(string $userUid)`（`app/Services/AuthService.php:14`）会：
- 先查 `UserApi::getByUuid`，查不到再查 `AiAssistantApi::getByUuid`（后者查 `ai_models.uid`）；
- 签发与用户 token 同构、有效期 365 天的 JWT。

目前**没有任何 HTTP 路由暴露它**，调用点全在服务端内部（`AiTaskPrepare.php:112`、`AiTranslateService.php:596`、`UpgradeAITranslation.php:108` 等）。

**这是本方案唯一的阻塞性缺口，必须新增端点。**

### 2.4 签发 channel access token —— 可用 ✅

`POST {API}/v2/access-token`（Bearer = **用户 token**）

```json
{ "payload": [ { "res_type": "channel", "res_id": "<channel uid>", "power": "edit", "book": 0 } ] }
```

返回 `data: { rows: [ { payload, token } ], count }`。

- 实现：`AccessTokenController::store()`（`app/Http/Controllers/AccessTokenController.php:33`）
- 鉴权：`ChannelApi::userCanEdit(user_uid, res_id)`，无权则该条被静默跳过（`continue 2`）→ **rows 可能为空数组，客户端必须判空**
- 签名密钥：`AccessToken.token`（uuid）**重复两次拼接**，算法 HS512（`AccessTokenController.php:78`）
- ⚠️ payload 中不含 `exp`，**签出的 token 永不过期**

### 2.5 写入句子 —— 可用 ✅

`POST {API}/v2/sentence`（Bearer = **AI model token**）

```json
{
  "sentences": [
    {
      "book_id": 1,
      "paragraph": 10,
      "word_start": 0,
      "word_end": 12,
      "channel_uid": "<channel uid>",
      "content": "译文",
      "content_type": "markdown",
      "access_token": "<§2.4 签出的 JWT>"
    }
  ]
}
```

返回 `data: { rows: [SentResource...], count }`。

- 实现：`SentenceController::store()`（`app/Http/Controllers/SentenceController.php:303`）
- 权限判定 `UserCanEdit()`（`SentenceController.php:272`）：
  1. bearer 身份是 channel owner → 放行；
  2. 否则查协作权限 `ShareApi::getResPower(...) >= 20` → 放行；
  3. 否则用 `AccessToken.token` 重复两次作为密钥验签 `access_token`，并校验 book 范围。
- 语义：按 `(book_id, paragraph, word_start, word_end, channel_uid)` 做 `firstOrNew`，**存在即更新，不存在则新建**（天然幂等）
- 副作用：写入 `sent_history`、清 Redis 缓存、`Mq::publish('progress', ...)`
- 另一种调用形态：把 `channel` / `book` / `access_token` 放在顶层，句子数组内不再重复（`SentenceController.php:315-326`）
- 现成参考实现：`ai-translate/ai_translate/service.py:429`

**⚠️ book 字段类型陷阱**：校验用严格比较
`if (isset($jwt->book) && $jwt->book !== 0 && $jwt->book !== $book)`，而 `$book` 已被 `(int)` 转换。
因此签发 access token 时 `book` **必须是整数**（`0` 表示不限 book）；写成 `"1"` 字符串会导致 `"1" !== 1` 恒真而被拒绝。

---

## 3. 端到端流程

```
用户                Skill 脚本                     API
 |                     |                            |
 |-- 交互式输入口令 --->|                            |
 |                     |-- POST /v2/sign-in ------->|
 |                     |<-- userToken (365d) -------|
 |                     |-- GET /v2/auth/current --->|   取 realName 作为 studio_name
 |                     |                            |
 |                     |-- GET /v2/ai-model?view=studio&name=&keyword= -->
 |                     |<-- rows（精确匹配 name）---|
 |                     |   未命中 → POST /v2/ai-model  → PUT /v2/ai-model/{uid}
 |                     |                            |
 |                     |-- GET /v2/ai-model/{uid}/token ★新增 -->
 |                     |<-- modelToken (365d) ------|
 |-- 提供 channel_id ->|                            |
 |                     |-- POST /v2/access-token（Bearer=userToken）-->
 |                     |<-- accessToken ------------|
 |                     |                            |
 |                     |-- POST /v2/sentence（Bearer=modelToken，句内带 accessToken）-->
 |                     |<-- {rows, count} ----------|
```

三种 token 的职责必须区分清楚：

| Token | 签发者 | 作用 | 用在哪 |
|---|---|---|---|
| userToken | `sign-in` | 代表**人类操作者** | 查/建 ai-model、签 access token |
| modelToken | 新增端点 | 代表 **AI 模型身份** | 写句子时的 `Authorization` |
| accessToken | `access-token` | **委托** channel 编辑权给持有者 | 写句子时的 body 字段 |

---

## 4. 可行性结论

**可行。** 五个步骤中四步已有现成 API，剩余一步需新增约 15 行后端代码。

必须做的服务端改动只有 §5.1 一项；其余为质量/安全修补，建议一并处理，因为 Skill 会高频调用这些接口，缺陷会被放大。

### 备选方案（若不想改后端）

用 **userToken 直接写句子**，跳过 model token。代价：
- `editor_uid` 变成人类用户，**丧失 AI 署名与审计能力**——这与本设计的核心目的冲突；
- 若操作者是 channel owner，连 access_token 都不需要，流程退化为两步。

可作为 Skill 的降级路径（`--as-self`），但不应是默认行为。

---

## 5. 需要的服务端改动

### 5.1 新增：获取 AI Model 的 user token（P0，阻塞）

路由（`routes/api.php` v2 组内）：

```php
Route::get('ai-model/{ai_model}/token', [AiModelController::class, 'token']);
```

控制器（`AiModelController`）：

```php
/**
 * 签发 AI 模型的身份 token，供外部客户端以模型身份写入数据。
 */
public function token(Request $request, AiModel $aiModel): JsonResponse
{
    $user = AuthService::current($request);
    if (! $user) {
        return $this->error(__('auth.failed'), 401, 401);
    }
    if (! StudioApi::userCanManage($user['user_uid'], $aiModel->owner_id)) {
        return $this->error(__('auth.failed'), 403, 403);
    }

    return $this->ok([
        'uid'   => $aiModel->uid,
        'name'  => $aiModel->name,
        'token' => AuthService::getUserToken($aiModel->uid),
    ]);
}
```

要点：
- 鉴权用 `StudioApi::userCanManage()` 而非 `AiModelController::canEdit()`，以支持 group studio；
- 该 token 等价于一个 365 天的模型身份凭据，**应记入 ops 日志**（参考 `App\Tools\OpsLog`）；
- 建议后续支持 `?ttl=` 缩短有效期（当前 `getUserToken` 硬编码 365 天，`AuthService.php:21`）。

### 5.2 修补（P1，强烈建议）

| # | 位置 | 问题 | 建议 |
|---|---|---|---|
| a | `AiModelController::show()` (`:102`) | **完全没有鉴权**，任何人可读任意模型 | 加 `AuthService::current` + 权限判定 |
| b | `AiModelResource::toArray()` | `parent::toArray()` 把 `key`（第三方 API key）原样返回 | 白名单字段，移除 `key` / `system_prompt`；仅 owner 请求时才附带 |
| c | `AiModelController::index()` (`:29-45`) | 非法 `view` → `$table` 未定义 → 500 | `default:` 分支返回 400 |
| d | `AiModelController::store()` (`:75`) | 不接受完整字段；无重名校验 | 接受 `model/url/key/privacy/description`；同 studio 内 `name` 唯一（`firstOrCreate` + 唯一索引） |
| e | `AiModelController::update()` (`:125`) | 未提供字段被置 null | 改用 `$request->filled()` / `only()` 增量更新 |
| f | `AccessTokenController::store()` (`:78`) | 签出的 token **永不过期** | payload 注入 `exp`（如 7 天），并在 `UserCanEdit` 中校验 |
| g | `AiModelController` 的 `Store/UpdateAiModelRequest` | `rules()` 为空，无任何校验 | 补 `name` 必填、`privacy` in 枚举等 |

**(b) 和 (f) 是真实的安全问题，应优先于 Skill 本身完成。**

---

## 6. Skill 设计

### 6.1 目录结构

```
wikipali-write/
├── SKILL.md              # 触发条件 + 流程说明（给模型读）
├── references/
│   └── api.md            # 本文 §2 的精简版：端点、字段、陷阱
└── scripts/
    ├── wp_login.py       # 交互式登录，仅此脚本接触密码
    └── wp.py             # 客户端：ensure-model / grant / write
```

选 Python 是为了与既有 `ai-translate` 保持一致（同一套请求格式，便于复用与对照）。

### 6.2 凭据存储

路径：`~/.wikipali/credentials.json`（**不放在用户项目目录内**，避免被误提交），权限 `0600`。

```json
{
  "api_url": "https://.../api",
  "user": { "uid": "...", "username": "...", "token": "..." },
  "model": { "uid": "...", "name": "claude-opus-5", "token": "..." },
  "access_tokens": {
    "<channel_uid>": { "token": "...", "book": 0, "granted_at": "2026-08-03T10:00:00Z" }
  }
}
```

原则：
- **Claude 永不接触明文密码**。登录由用户自己执行 `python scripts/wp_login.py`（`getpass` 读取），或在 Claude Code 中用 `! python .../wp_login.py` 前缀运行；
- Skill 读取凭据文件时只取 token，不回显到对话中（日志里 token 一律打码）；
- 任一 token 收到 401 → 提示重新登录，而不是自动重试。

### 6.3 幂等 model 记录

`name` 取当前模型标识（如 `claude-opus-5`），流程：

1. `GET /v2/ai-model?view=studio&name={username}&keyword={modelName}`；
2. 在 `rows` 中做 `name` **精确**比对；
3. 命中 → 用其 `uid`；未命中 → `POST` 创建，再 `PUT` 补齐 `model` / `privacy` 等字段（在 §5.2d 落地前必须这么做两步）；
4. `GET /v2/ai-model/{uid}/token` 取 modelToken，写入凭据文件缓存。

### 6.4 写入前的确认

写库属于对外的、不易回滚的操作。Skill 必须在 `POST /v2/sentence` 之前：
- 展示：目标 channel（uid + name）、book、句子条数、前若干条的 `id` 与 content 摘要；
- 明确提示「已存在的相同句子将被覆盖」（`firstOrNew` 语义）；
- 取得用户确认后才发送。批量写入建议分批（如每批 50 条）并报告累计 `count`。

### 6.5 错误处理约定

| 现象 | 含义 | 处置 |
|---|---|---|
| 401 | token 失效/过期 | 引导重新登录，勿自动重试 |
| 403 | 无 channel 编辑权，或非 studio owner | 明确指出缺哪一项权限 |
| `access-token` 返回 `count: 0` | 用户对该 channel 无编辑权（被静默跳过） | 当作 403 报错，**不可继续写入** |
| `sentence` 返回 `count` 小于提交条数 | 部分句子鉴权失败被 `continue` 跳过 | 逐条对比返回的 rows，报告被跳过的句子 id |
| `no date` (200) | 请求缺 `sentences` 字段 | 客户端 bug |

注意 `SentenceController::store()` 对**逐句失败是静默跳过**的（`:341`），所以「HTTP 200」不等于「全部写入成功」，必须核对 `count`。

---

## 7. 安全考量

1. **权限不放大**：AI 模型自身不是任何 channel 的 owner/协作者，其全部写权限来自用户签发的 access token，且受 book 范围限制。用户无权的 channel，签发阶段就会失败。
2. **模型 token 是长效凭据**（365 天）。落地 §5.1 时应记 ops 日志；后续支持可撤销（例如在 `ai_models` 上加 `token_version` 参与 JWT payload，改版即失效）。
3. **access token 永不过期**是当前实现的既有风险（§5.2f）。在修复前，Skill 应把它视为高敏感数据，仅存本地、不进日志、不进对话。
4. **密码零留存**：不写入任何文件，不进入对话上下文。
5. **审计**：所有写入都会进 `sent_history`（`SentenceService::saveHistory`），`editor_uid` 为模型 uid，可追溯。

---

## 8. 实施计划

| 阶段 | 内容 | 依赖 |
|---|---|---|
| P0 | 服务端：新增 `GET /v2/ai-model/{uid}/token` + 测试 | — |
| P0 | 服务端安全修补：§5.2 (a)(b)(f) | — |
| P1 | Skill：`wp_login.py` + 凭据存储 + `auth/current` 校验 | P0 |
| P1 | Skill：`ensure-model`（查/建/补字段/取 token） | P0 |
| P1 | Skill：`grant`（签 access token，缓存） | — |
| P1 | Skill：`write`（分批 + 确认 + count 核对） | 以上全部 |
| P2 | 服务端质量修补：§5.2 (c)(d)(e)(g) | — |
| P2 | Skill 扩展：读取能力（`GET /v2/sentence`、channel 列表、`sentences-in-chapter`）与 `sentpr` PR 提交 | P1 |

测试要求（`api-v13` 使用 Pest）：
- Feature test 覆盖新端点的 401 / 403 / 200 三条路径；
- 一条端到端 test：登录 → 建模型 → 取 model token → 签 access token → 写句子 → 断言 `editor_uid == 模型 uid`。

---

## 9. 待确认问题

1. 模型记录挂在**个人 studio** 还是**共享 group studio** 下？前者简单（现有 `canEdit` 即可），后者更适合团队复用，但需要 §5.1 采用 `StudioApi::userCanManage`。
2. 是否需要为「AI 模型」提供撤销 token 的机制？若需要，应在 P0 就把 `token_version` 设计进去，避免后续 JWT 结构变更。
3. channel uid 的获取方式：用户直接提供，还是由 Skill 通过 `GET /v2/channel`（列出可编辑 channel）交互式选择？后者体验更好，需先确认该接口的 `view` 参数取值。
4. Skill 的分发形态：随本仓库发布，还是独立成一个可 `git clone` 到 `~/.claude/skills/` 的仓库？
