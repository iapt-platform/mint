# WikiPali 写入型 Skill 设计文档

> 目标：提供一个 Claude Code Skill，通过 Claude Code 以「AI 模型身份」把句子写入 WikiPali 数据库（`SentenceController`），并保持正确的作者署名与权限边界。
>
> 分发路径：在本仓库开发调试，成熟后以**整目录复制**方式装到其他项目（§6.7）。不做独立仓库——API 仍需频繁修改，Skill 契约必须与 `api-v13` 同仓演进。
>
> 状态：设计已定案；服务端 P0 已完成（§5.1 端点 + §5.2 abdefg）；Skill P1 已完成（`plugins/wikipali/`）并在开发机上端到端跑通（2026-08-05）；线上四站尚未部署
> 对应后端：`api-v13`（Laravel 13，路由前缀 `/api/v2`）
> 决策定案：2026-08-04（见 §9）

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

以下均已对照源码核实。基址记为 `{API}`，形如 `https://host/api`（参见 `ai-translate/config.orig.toml` 的 `api-url`）。**`{API}` 不是唯一的**：线上四个地址共享同一套数据，差别只在地区（`.org`/`.cc`）与代码版本（`www` 稳定 / `next` 最新）——见 §6.1.2。下文契约以稳定版为准。

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

`POST {API}/v2/ai-model`（Bearer）body `{name, studio_name, model?, url?, key?, privacy?, description?, system_prompt?}`

- 实现：`AiModelController::store()`
- ✅ 已接受全部字段；`privacy` 缺省为 `private`
- ✅ 同一 studio 内 `name` 重复返回 **409**（客户端据此判定「已存在」）
- `name` 必填、`studio_name` 必填，违反则 **422**（`StoreAiModelRequest`）
- 鉴权：`canEdit($user_uid, $studioId)` 要求 `user_uid === studioId`，即**只有个人 studio 可用，group studio 会 403**

`PUT {API}/v2/ai-model/{uid}`（Bearer）

- 路由模型绑定按 `uid`（`AiModel::$primaryKey = 'uid'`）
- ✅ **增量更新**：只改请求里出现的字段，未提交的保持原值，客户端可以只发一两个字段
- 显式传 `null` 仍可清空字段（判定用 `has()` 而非 `filled()`）
- 改名撞上同 studio 内已有名字 → **409**

### 2.3 获取 AI Model 的 user token —— 可用 ✅（本次新增）

`GET {API}/v2/ai-model-token/{uid}`（Bearer = **用户 token**）→ `data: { uid, name, token }`

- 实现：`AiModelTokenController::show()`，见 §5.1
- 鉴权：仅模型 owner 本人（`canEdit`），否则 403；未登录 401
- 底层是 `AuthService::getUserToken()`（`app/Services/AuthService.php`）：先查 `ai_models.uid`，命中即签模型 token，否则按人类用户签发
- **有效期 30 天**（人类登录 token 仍是 365 天），payload 带 `typ: "ai-model"` 与 `ver`（版本号）
- ⚠️ 仍属最高敏感凭据，但已**可撤销**，见 §2.3b

### 2.3b 撤销 AI Model 的全部 token —— 可用 ✅（本次新增）

`DELETE {API}/v2/ai-model-token/{uid}`（Bearer = **用户 token**）→ `data: { uid, name, token_version }`

- 实现：`AiModelTokenController::destroy()`，见 §5.1
- 鉴权同 `show`：仅 owner 本人，否则 403；未登录 401
- 语义是「作废该模型已签出的**所有** token」，不能只废其中一张——凭据泄漏时本就该全废
- 客户端处理：撤销后旧凭据请求一律 401，Skill 应提示重新 `ensure-model` 取 token

### 2.4 签发 channel access token —— 可用 ✅

`POST {API}/v2/access-token`（Bearer = **用户 token**）

```json
{ "payload": [ { "res_type": "channel", "res_id": "<channel uid>", "power": "edit", "book": 0 } ] }
```

返回 `data: { rows: [ { payload, token } ], count }`。

- 实现：`AccessTokenController::store()`（`app/Http/Controllers/AccessTokenController.php:33`）
- 鉴权：`ChannelApi::userCanEdit(user_uid, res_id)`，无权则该条被静默跳过（`continue 2`）→ **rows 可能为空数组，客户端必须判空**
- 签名密钥：`AccessToken.token`（uuid）**重复两次拼接**，算法 HS512
- ✅ payload 现在带 `nbf` / `exp`，**有效期 7 天**（`AccessTokenController::TOKEN_TTL`）。返回的 `payload` 里含 `exp`，客户端可据此判断何时需要重签
- 过期后写句子会得到 403（而非 500）

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
- 副作用：写入 `sent_histories`、清 Redis 缓存、`Mq::publish('progress', ...)`
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
 |                     |-- GET /v2/ai-model-token/{uid} ★新增 -->
 |                     |<-- modelToken (30d, 可撤销) |
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

### 5.1 新增：获取 AI Model 的 user token（P0，阻塞）—— ✅ 已实施

`GET {API}/v2/ai-model-token/{uid}`（Bearer = 用户 token）

路由（`routes/api.php` v2 组内，紧跟 `ai-model` 的 apiResource）：

```php
Route::get('ai-model-token/{ai_model}', [AiModelTokenController::class, 'show']);
```

独立控制器 `AiModelTokenController::show()`，而非挂在 `AiModelController` 上——签发身份凭据与模型的 CRUD 是两件事，分开后前者的鉴权与日志不会被 CRUD 的改动波及。路由参数名 `{ai_model}` 与 apiResource 生成的一致，隐式模型绑定按 `AiModel::$primaryKey = 'uid'` 解析。

```php
public function show(Request $request, AiModel $aiModel): JsonResponse
{
    $user = AuthService::current($request);
    if (! $user) {
        return $this->error(__('auth.failed'), null, 401);
    }
    if (! AiModelController::canEdit($user['user_uid'], $aiModel->owner_id)) {
        return $this->error(__('auth.failed'), null, 403);
    }

    $token = AuthService::getUserToken($aiModel->uid);
    if (! $token) {
        return $this->error('ai model not found', null, 404);
    }

    OpsLog::debug($user['user_uid'], [
        'action' => 'ai-model-token.issue',
        'model_uid' => $aiModel->uid,
        'model_name' => $aiModel->name,
    ]);

    return $this->ok([
        'uid' => $aiModel->uid,
        'name' => $aiModel->name,
        'token' => $token,
    ]);
}
```

返回 `data: { uid, name, token }`。

注意错误响应用的是 `$this->error($msg, null, $status)`。仓库里多数旧代码写成 `$this->error(__('auth.failed'), 401, 401)`，把状态码误当成了 `$data` 参数（`Controller::error(string $message, mixed $data, int $status)`），响应体里因此多一个 `"data": 401`。新代码不沿用这个写法。

要点：
- 鉴权用 `AiModelController::canEdit()`（仅 owner 本人）。**依据 §9 决策 1：模型记录只挂个人 studio**，不支持 group studio，因此无需 `StudioApi::userCanManage()`。与 `show/update/destroy` 口径一致；
- 该 token 是模型身份凭据，签发与撤销都**记入 ops 日志**（`App\Tools\OpsLog`，action 为 `ai-model-token.issue` / `ai-model-token.revoke`）；
- **有效期 30 天**：`AuthService::AI_MODEL_TOKEN_TTL`。人类登录 token 的 365 天不变（`USER_TOKEN_TTL`），两者分开是因为模型 token 要落到外部客户端的凭据文件里，泄漏面大得多；
- **撤销机制**（推翻 §9 决策 2）：`ai_models.token_version` 自增即作废该模型全部已签出 token。
  - 签发时 payload 带 `typ: "ai-model"` + `ver`；
  - `AuthService::current()` 只对带 `typ` 的 token 查一次 `token_version` 比对，人类 token 不额外查库；
  - 迁移 `2026_08_05_110345_add_token_version_to_ai_models_table`，默认值 1。

**兼容性**：引入版本号之前签出的模型 token（无 `typ`/`ver`，payload 里 `id` 恒为 0）一律失效。名义上是破坏性变更，实际破坏面为零——本轮改动尚未部署，线上没有任何存量模型凭据；仓库内部的 `ai-translate`、`app/Console/Commands/*`、`AiTaskPrepare` 都是每次任务现签现用。人类 token 的 `id` 是 `user_infos.id`（≥1），不受影响。

### 5.2 修补（P1，强烈建议）

| # | 状态 | 位置 | 问题 | 建议 |
|---|---|---|---|---|
| a | ✅ 已修 | `AiModelController::show()` (`:103`) | **完全没有鉴权**，任何人可读任意模型 | 已加 `AuthService::current` + `canEdit` 判定（见下方遗留项） |
| b | ✅ 已修 | `AiModelResource::toArray()` | `parent::toArray()` 把 `key`（第三方 API key）原样返回 | 改为字段白名单；`key` / `system_prompt` 仅 owner 请求时附带 |
| c | ⬜ 待修 | `AiModelController::index()` | 非法 `view` → `$table` 未定义 → 500 | `default:` 分支返回 400 |
| d | ✅ 已修 | `AiModelController::store()` | 不接受完整字段；无重名校验 | 已接受 `model/url/key/privacy/description`；同 studio 内重名返回 409 |
| e | ✅ 已修 | `AiModelController::update()` | 未提供字段被置 null | 改为按 `$request->has()` 增量更新 |
| f | ✅ 已修 | `AccessTokenController::store()` | 签出的 token **永不过期** | payload 注入 `exp`（7 天）；`UserCanEdit` 捕获解码异常 |
| g | ✅ 已修 | `AiModelController` 的 `Store/UpdateAiModelRequest` | `rules()` 为空，无任何校验 | 已补 `name` 必填、`privacy` 枚举、各字段长度上限 |

(g) 本属 P2，但 (d) 的重名校验依赖 `name` 必填，只好一并做掉。剩下的 (c) 与 Skill 流程无关，留在 P2。

#### (b) 的实施要点

不能简单删掉 `key` / `system_prompt`：dashboard 的模型编辑页（`AiModelEdit.tsx`）靠 `GET /v2/ai-model/{uid}` 回填这两个字段，删了会导致用户一保存就把 key 清空。故按请求者是否 owner 分别返回。

真正的泄漏面其实比 (a) 大得多：`index()` 的 `view=all` / `view=usable` 分支对**任何登录用户**返回全部模型记录，key 就在里面——(a) 只堵住了 `show` 一个口子。

`isRequestedByOwner()` 的结果按请求缓存在 `$request->attributes` 上：`index()` 一次最多返回 1000 行，逐行解一次 JWT 不可接受。

#### (f) 的连带改动

给 access token 加上 `exp` 之后，`SentenceController::UserCanEdit()` 里的 `JWT::decode()` 会在 token 过期时抛 `ExpiredException`。原代码没有 try/catch，**过期 token 会变成 500 而不是 403**。已补捕获，并顺带处理了 `AccessToken` 查不到记录时 `new Key(null)` 抛异常的情况。

#### (a) 的遗留项

当前实现：

```php
public function show(Request $request, AiModel $aiModel)
{
    $user = AuthService::current($request);
    if (! $user) {
        return $this->error(__('auth.failed'), 401, 401);
    }
    if (! self::canEdit($user['user_uid'], $aiModel->owner_id)) {
        return $this->error(__('auth.failed'), 403, 403);
    }

    return $this->ok(new AiModelResource($aiModel));
}
```

1. ~~**签名缺 `$request`**~~——已修复：初版方法体用了 `$request` 但参数列表没有它，该端点必然 500；现已补上 `Request $request`（路由模型绑定不受影响，Laravel 按类型而非位置注入）。

2. ~~**鉴权口径**~~——已定：§9 决策 1 选个人 studio，`canEdit()` 就是正确口径，与 `token()` / `update()` / `destroy()` 一致，无需改。副作用是 `privacy = public` 的模型对非 owner 也不可读；这与 `index()` 的 `view=usable`（返回 public 模型）不一致，但由于 (b) 落地后 `index` 不再泄漏敏感字段，且 Skill 只读自己的模型，暂不处理。

3. **对 Skill 的影响**——§6.3 第 3 步 `POST` 之后如需回读，以及任何走 `GET /v2/ai-model/{uid}` 的路径，现在都必须带 userToken；沿用 §6.3 的 `view=studio` 列表比对方式则不受影响。

---

## 6. Skill 设计

### 6.1 目录结构

**开发地点：本仓库。分发方式：Claude Code 插件（marketplace）。**（§9 决策 4、决策 7）

理由：API 尚不完善，Skill 与服务端要同步改（§5 的每一项都会反映到 `references/api.md`）。放在 mint 仓库内，一次提交就能同时改 Laravel 代码和 Skill 契约；独立仓库会让两者版本漂移，且改 API 时无法在同一个 Claude Code 会话里读写后端代码。

放在仓库根的 `plugins/` 下，本身就是一个合法插件：

```
plugins/wikipali/
├── .claude-plugin/
│   └── plugin.json       # 插件清单，version 是唯一的版本来源
├── README.md             # 面向安装者：装之前它会动你哪些东西
├── install.sh            # 不走 marketplace 时的后路
└── skills/
    └── write/            # → 调用名 wikipali-write:write
        ├── SKILL.md      # 触发条件 + 流程说明（给模型读）
        ├── references/
        │   └── api.md    # 本文 §2 的精简版：端点、字段、陷阱
        └── scripts/
            ├── wp_login.py   # 交互式登录，仅此脚本接触密码
            └── wp.py         # 客户端：endpoint / whoami / ensure-model /
                              #         revoke / channels / grant / write
```

实现时比原计划多了两个子命令：`whoami`（一屏看清当前站点、三种 token 及其到期时间——排查「为什么 401」的第一步）与 `revoke`（§2.3b 的撤销端点，安全能力做了就该有入口）。`wp_login.py` 通过 `import wp` 复用 HTTP 与凭据代码，两个文件仍在同一目录内，不违反自包含约束。

几个布局上的决定：

- **用 `skills/write/` 而不是把 SKILL.md 放插件根**。后者也合法（单 skill 插件允许），但调用名会变成 `wikipali-write:wikipali-write`；而且 §9 后续规划里还有读取和 sentpr 两个 skill，`skills/` 布局才能容纳。
- **`VERSION` 文件已删**。版本号只留 `plugin.json` 的 `version` 一处，两处必然漂移；`install.sh` 改为从 manifest 读。
- **仓库根留一个 symlink** `.claude/skills/wikipali-write → ../../plugins/wikipali/skills/write`，这样在 mint 里开发时（无论从哪个子目录启动 Claude Code）skill 仍然自动加载。实测普通 skill 的向上查找会跟随 symlink；插件形态则用 `--plugin-dir ./plugins/wikipali` 测。
- 放在**仓库根**而非 `api-v13/` 下：后者已有 `laravel-best-practices` 等目录级 skill，只在编辑 `api-v13/` 时激活；而本 Skill 是对线上 API 的客户端操作，与当前编辑哪个子目录无关。

### 6.1.1 可分发性约束

「能复制给别的项目用」是硬需求，因此以下几条是**约束而非偏好**：

1. **目录自包含**——不引用 `plugins/wikipali/` 之外的任何路径。SKILL.md 里不能出现 `api-v13/...` 这类仓库内引用；需要的 API 事实全部落在 `references/api.md` 里。
2. **零安装依赖，只用 Python 标准库**——用 `urllib.request` 而非 `requests`，`json` / `getpass` / `argparse` 均为内置。**不跟随 `ai-translate` 的 venv + `pip install -e` 模式**（`ai-translate/pyproject.toml` 依赖 `pika`/`requests`/`redis`/`openai`）：那套在目标项目里要求用户先建虚拟环境，与「复制即用」冲突。代价是要自己处理 `urllib` 的 HTTPError/超时/JSON 编码，比 `requests` 啰嗦，但换来 `python3 scripts/wp.py` 开箱可跑。
3. **API 地址不硬编码**——见 §6.1.2。复制到别的项目后无需改代码。
4. **凭据与 Skill 解耦**——凭据在 `~/.wikipali/`（§6.2），多个项目里的 Skill 副本共用同一份登录态，登录一次即可。

依然选 Python（而非 shell）是为了与 `ai-translate` 的请求语义保持一致，便于对照排查。

### 6.1.2 多站点：四个地址，一套数据

线上四个站点**共享同一个数据库和同一把 `jwt_secrets_key`**，区别只有两个维度（2026-08-05 用户确认）：

| api_url | 域名 | 代码版本 |
|---|---|---|
| `https://www.wikipali.org/api` | .org | 稳定版 |
| `https://www.wikipali.cc/api` | .cc | 稳定版 |
| `https://next.wikipali.org/api` | .org | 最新版 |
| `https://next.wikipali.cc/api` | .cc | 最新版 |
| `http://127.0.0.1:8000/api` | 开发机 | 工作副本 |

- `.org` / `.cc` —— 地区可达性，用户按网络情况选；
- `www` / `next` —— **代码版本**，不是数据环境。`next` 跑最新版，出问题可随时降级到 `www`，数据不受影响。

因此 Skill **不需要「按站点分桶」这类结构**——四个地址在数据上是同一个后端：

1. **线上凭据只有一份**。四个地址通用：userToken / modelToken 用同一把密钥签；channel access token 的密钥存在同一张 `access_tokens` 表里；`ai_models` 也是同一张表，模型 uid 在四个地址上都是同一个。§6.2 的凭据文件退化为 `{ online: {...}, local: {...} }` 两桶——`local` 单独一桶是因为开发机是另一个库、另一把密钥。
2. **任意切换，不需要重新登录、不需要重跑 ensure-model、不需要重签 access token**。换地址只是换一条网络路径 + 换一版服务端代码。
3. **允许自动 fallback，但要出声**。四个线上地址之间连不通就换下一个是安全的（同一套数据）。顺序：用户选定的 → 同版本的另一域名 → 另一版本的同域名。**唯独不能自动回退到 `local`**，那是另一套库。切换时打一行提示（`www.wikipali.org 连接失败，已改用 www.wikipali.cc`）——静默切换会掩盖「你选的站点挂了」，也会让 §6.1.2-4 的契约差异变得无从排查。
4. **真正的风险不是写错库，是写到不同版本的代码上**。`next` 与 `www` 的 API 契约可能不一致：新端点、新字段、新校验会先上 `next`，`www` 落后一段时间。所以：
   - Skill 依赖的新端点（如 §2.3b 的 `DELETE /v2/ai-model-token/{uid}`）在 `www` 上可能还是 404，遇到 404 要提示「当前站点代码版本较旧，请切到 next 或稍后再试」，而不是当成「模型不存在」；
   - `references/api.md` 记录的契约以 **`www`（稳定版）** 为准，`next` 独有的能力标注出来。Skill 默认连 `www`。
5. **写入前仍要回显 api_url**，但理由变了：不是怕写错库（写不错），而是出问题时要知道是哪一版代码写的。

#### 用户如何切换（2026-08-05 定案）

地址来源优先级：

| 优先级 | 来源 | 是否改变默认 |
|---|---|---|
| 1 | `--api https://next.wikipali.org/api` | **否**，仅本次调用 |
| 2 | `WIKIPALI_API_URL` 环境变量 | 否，仅当前 shell |
| 3 | 凭据文件里的 `online.api_url` | 这就是默认，由 `wp.py endpoint` 写入 |
| 4 | 都没有 → `https://www.wikipali.org/api` | 首次运行的兜底（稳定版） |

**`--api` 一次性覆盖，不写回凭据文件**。否则「上周试了一次 next」会一直粘着，之后每次写入都落在最新版代码上而用户毫无察觉。改默认必须是显式动作，即下面的子命令。长期用 `next` 的人应该改默认，而不是每次带参数。

**`wp.py endpoint` 是唯一改默认的入口**，让「切站点」成为可见、可回显的动作，而不是手工编辑 JSON：

```
$ python3 scripts/wp.py endpoint
  1) https://www.wikipali.org/api   稳定版 · .org  ← 当前
  2) https://www.wikipali.cc/api    稳定版 · .cc
  3) https://next.wikipali.org/api  最新版 · .org
  4) https://next.wikipali.cc/api   最新版 · .cc
  5) http://127.0.0.1:8000/api      开发机

$ python3 scripts/wp.py endpoint next
  已切换到 https://next.wikipali.org/api（最新版 · .org）
```

不带参数时列出清单并标出当前选中；带参数时接受序号、简称（`next` / `www` / `local`）或完整 url，写回 `online.api_url`。切到 `local` 则改用 `local` 桶的凭据（§6.2）。

开发机地址不做特殊照顾——`http://` 明文只在 `127.0.0.1` 放行，其余一律要求 `https://`。

上表是**内置的已知站点清单**，与 §6.1.1 第 3 条（地址不硬编码）不冲突：清单只用于 `endpoint` 子命令的展示与 fallback 排序；`--api` / 环境变量给出的任意地址仍然接受，只是不在清单里的地址自成一桶，不与线上凭据互通。

### 6.2 凭据存储

路径：`~/.wikipali/credentials.json`（**不放在用户项目目录内**，避免被误提交），权限 `0600`。

```json
{
  "current": "online",
  "online": {
    "api_url": "https://www.wikipali.org/api",
    "user": { "uid": "...", "username": "...", "token": "..." },
    "model": { "uid": "...", "name": "claude-opus-5", "token": "..." },
    "access_tokens": {
      "<channel_uid>": { "token": "...", "book": 0, "granted_at": "2026-08-03T10:00:00Z" }
    }
  },
  "local": {
    "api_url": "http://127.0.0.1:8000/api",
    "user": {}, "model": {}, "access_tokens": {}
  }
}
```

只有 `online` / `local` 两桶，理由见 §6.1.2：四个线上地址共享库与密钥，凭据通用。`online.api_url` 只记「上次选的是哪个地址」，换地区或在 www / next 之间切换就是改这一个字段，`user` / `model` / `access_tokens` 全部原样沿用。`local` 单独一桶是因为开发机是另一个库、另一把 `jwt_secrets_key`。

原则：
- **Claude 永不接触明文密码**。登录由用户自己在**一个真正的终端**里执行 `python3 scripts/wp_login.py`（`getpass` 读取）。
  ~~或在 Claude Code 中用 `! python .../wp_login.py` 前缀运行~~——2026-08-05 实测推翻：`!` 前缀跑的命令没有交互式终端（`sys.stdin.isatty()` 为假），密码提示无处输入。脚本会明确报错并指路，另提供 `--password-stdin` 供自动化场景从管道读取；密码**不能**经 argv 传（进 `ps` 与 shell history），也不能直接打进对话（进上下文）；
- Skill 读取凭据文件时只取 token，不回显到对话中（日志里 token 一律打码）；
- 任一 token 收到 401 → 提示重新登录，而不是自动重试；
- 缓存的 `model.token` 有效期只有 30 天，且可能被 owner 主动撤销（§2.3b）。两种情况的表现都是 401，处理一致：重跑 §6.3 第 4 步重取，仍 401 才提示重新登录。

### 6.3 幂等 model 记录

`name` 取当前模型标识（如 `claude-opus-5`），流程：

1. `GET /v2/ai-model?view=studio&name={username}&keyword={modelName}`；
2. 在 `rows` 中做 `name` **精确**比对；
3. 命中 → 用其 `uid`；未命中 → `POST` 一次即可带全字段（§5.2d 已落地，不再需要「POST 再 PUT」两步）。`POST` 撞 409 → 回查列表取 uid（模糊匹配漏网或并发）；已存在但字段有出入 → `PUT` 增量补；
4. `GET /v2/ai-model-token/{uid}` 取 modelToken，写入凭据文件缓存。

`--name` 决定署名，故不设默认值：拿不到就报错要求显式指定，避免把句子挂到别的模型名下。

### 6.4 channel 的交互式选择

§9 决策 3：**不要求用户手工提供 channel uid**，由 Skill 列出可编辑 channel 供选择。

接口：`GET /v2/channel?view=user-edit`（Bearer = **用户 token**）

```
ChannelController::index() 的 'user-edit' 分支：
  ShareApi::getResList(user_uid, 2) 中 power >= 20 的 res_id
  ∪ owner_uid == user_uid 的 channel
```

返回列 `uid / name / summary / type / owner_uid / lang / status / is_system / updated_at / created_at`。

流程：
1. 用户未指定 channel → `GET /v2/channel?view=user-edit`，按 `updated_at` 倒序展示 `name` + `lang` + `uid` 前 8 位；
2. 用户从中选择（或用 `name` 模糊匹配后确认）；
3. 选定的 uid 进入 §3 流程的 `access-token` 签发步骤。

要点：
- 该 view **已经是「可编辑」语义**，与 `access-token` 签发用的 `ChannelApi::userCanEdit()` 是同一套 share 权限（power ≥ 20），所以列表里的 channel 正常都能签出 token。但两者代码路径不同，仍须按 §6.6 判 `count: 0`；
- 若用户显式给了 uid，跳过列表直接用，但仍应 `GET /v2/channel/{uid}` 回显 name 供 §6.5 确认——避免写错 channel；
- 列表为空 → 明确提示「当前账号没有任何可编辑的 channel」，而不是继续走签发流程。

### 6.5 写入前的确认

写库属于对外的、不易回滚的操作。Skill 必须在 `POST /v2/sentence` 之前：
- 展示：**当前 api_url**（§6.1.2：四个线上地址写的是同一个库，但代码版本不同，出问题时要知道是哪一版写的）、目标 channel（uid + name）、book、句子条数、前若干条的 `id` 与 content 摘要；
- 明确提示「已存在的相同句子将被覆盖」（`firstOrNew` 语义）；
- 取得用户确认后才发送。批量写入建议分批（如每批 50 条）并报告累计 `count`。

### 6.6 错误处理约定

| 现象 | 含义 | 处置 |
|---|---|---|
| 401 | token 失效/过期，或把 `local` 的 token 发到了线上（§6.1.2；四个线上地址之间不会出这个问题） | 引导重新登录，勿自动重试 |
| 403 | 无 channel 编辑权，或非 studio owner | 明确指出缺哪一项权限 |
| `access-token` 返回 `count: 0` | 用户对该 channel 无编辑权（被静默跳过） | 当作 403 报错，**不可继续写入** |
| `sentence` 返回 `count` 小于提交条数 | 部分句子鉴权失败被 `continue` 跳过 | 逐条对比返回的 rows，报告被跳过的句子 id |
| `no date` (200) | 请求缺 `sentences` 字段 | 客户端 bug |
| 新端点 404（如 §2.3b 的 `DELETE /v2/ai-model-token/{uid}`） | 当前站点跑的是稳定版代码，该端点尚未上线（§6.1.2-4） | 提示切到 `next` 或稍后再试，**不要**当成「资源不存在」 |

注意 `SentenceController::store()` 对**逐句失败是静默跳过**的（`:341`），所以「HTTP 200」不等于「全部写入成功」，必须核对 `count`。

### 6.7 打包与分发（2026-08-05 改为插件，见 §9 决策 7）

**主路径：Claude Code 插件 + 自建 marketplace。**

```
/plugin marketplace add iapt-platform/wikipali-plugins
/plugin install wikipali@wikipali
```

桌面版（Claude Desktop 的 **Code** 标签页）点 `+` → Plugins → Add plugin 装同一个 marketplace。注意插件只对本地/SSH 会话生效，Chat 标签页与云会话不加载插件。

**上游是 `iapt-platform`，`visuddhinanda/*` 是 fork。** 开发在 fork 上做，通过 PR 合回上游（仓库既有的工作流，见 `Merge pull request #2427`）。**面向用户的一切地址都必须指向 `iapt-platform`**——指向 fork 会让用户装到某个人的分支上。

**两个仓库的分工**：

| 仓库 | 内容 | 为什么 |
|---|---|---|
| `iapt-platform/wikipali-plugins` | 只有 `.claude-plugin/marketplace.json` + README，8 KB | `/plugin marketplace add` 会**完整克隆** marketplace 仓库，没有稀疏优化 |
| `iapt-platform/mint` | 插件本体 `plugins/wikipali/` | 与 API 同仓演进（§6.1）。marketplace 用 `git-subdir` 源指过来，Claude Code **稀疏克隆**只取这一个子目录 |

反过来「mint 自己当 marketplace」是不行的：mint 的 packfile 580 MB、HEAD 快照 212 MB，而 Claude Code 的 git 操作超时是 120 秒，且后台自动更新失败时会整仓重新 clone。

**版本与更新**——版本号只有 `plugin.json` 的 `version` 一处。marketplace 条目里可以再加 `sha` 钉到具体提交，那才是真正的「发版」：用户不会静默拿到 mint 上某个未验证的中间提交。改 API 契约时的动作是：改插件 → 提交 → 推 mint → 更新 marketplace.json 的 `sha`/`version` → 用户 `/plugin update`。

**`install.sh` 降级为后路**：不走 marketplace 时，它把插件目录整个复制到 `<target>/.claude/skills/wikipali-write/`，因为带 `.claude-plugin/plugin.json` 的目录会被当作 `<name>@skills-dir` 插件就地加载。代价是不会自动更新。

**先后顺序**：先在本仓库把流程跑通（P1 全部完成），再打包。过早分发会把未定型的 API 契约固化到别人机器上——所以**线上四站部署 + 线上复测通过之前，不要把 marketplace 地址给别人**。

#### 已发布状态（2026-08-06）

`iapt-platform/wikipali-plugins` 已上线，`wikipali-write` 钉在 mint 的 `c46cf6400`。实测：

- `/plugin marketplace add iapt-platform/wikipali-plugins` → `/plugin install wikipali@wikipali` 一次通过；
- 缓存目录 `~/.claude/plugins/cache/wikipali/wikipali-write/0.1.0/` 只有 **84 KB**——`git-subdir` 的稀疏克隆确实只取了那一个子目录，没有拉 mint 的 580 MB；
- 在与 mint 无关的目录下启动，skill 以 `wikipali-write:write` 加载，缓存里的 `wp.py` 直接可跑；
- 常驻上下文成本 ~230 tok（就是 SKILL.md 的 description），调用时 ~2k。

**发版流程**（四步，缺一步用户就拿不到新版）：

1. 改插件 → 提交 → **推 mint**；
2. 有契约变更就 bump `plugins/wikipali/.claude-plugin/plugin.json` 的 `version`；
3. 更新 `wikipali-plugins` 的 `marketplace.json`：`source.sha` 指向新提交，`version` 跟着改；
4. 用户 `/plugin update wikipali@wikipali`。

第 3 步是刻意的手工闸门：不钉 sha 的话用户会静默拿到 `development` 上任何一个中间提交，包括没验证过的。

2026-08-05 的实际情况：`install.sh` 已写好并验证（装出的副本能独立运行），但**分发要等到服务端部署 + 端到端实测通过之后**。打包机制本身不依赖 API 契约，先写好没有代价；真正会把未定型契约固化出去的是「复制给别的项目」这一步。

---

## 7. 安全考量

1. **权限不放大**：AI 模型自身不是任何 channel 的 owner/协作者，其全部写权限来自用户签发的 access token，且受 book 范围限制。用户无权的 channel，签发阶段就会失败。
2. **模型 token 有效期 30 天且可撤销**（§5.1）：泄漏时 owner 调 `DELETE /v2/ai-model-token/{uid}` 即可让该模型全部已签出 token 立刻失效，不必轮换全局 `jwt_secrets_key`（那会踢掉所有用户）。撤销是全量的，不能只废一张。`~/.wikipali/credentials.json` 仍须 `0600`、不进日志/不进对话——撤销是止损手段，不是防线。
3. **access token 有效期 7 天**（§5.2f 已修）。注意签名密钥是 `access_tokens` 表里按 `res_type + res_id` 存的 uuid，`firstOrNew` 只在首次创建，**同一 channel 的密钥不轮换**——所以 7 天只限制单张 token 的窗口，没有「立即吊销」能力。Skill 仍应把它视为高敏感数据，仅存本地、不进日志、不进对话。
4. **密码零留存**：不写入任何文件，不进入对话上下文。
5. **审计**：所有写入都会进 `sent_histories`（`SentenceService::saveHistory`），`editor_uid` 为模型 uid，可追溯。
6. **部署前提**：`token_version` 的校验在 `AuthService::current()` 里，故撤销只被跑了该版本代码的站点认账。本轮改动尚未部署到任何服务器，部署时四个站点一起上即可，不存在版本差窗口。若日后单独灰度某个站点，需记得这条。

---

## 8. 实施计划

| 阶段 | 状态 | 内容 | 依赖 |
|---|---|---|---|
| P0 | ✅ | 服务端：新增 `GET /v2/ai-model-token/{uid}`（`AiModelTokenController::show`，用 `canEdit`）+ 测试 | — |
| P0 | ✅ | 服务端安全修补：§5.2 (a)(b)(f) | — |
| P0 | ✅ | 服务端：§5.2 (d)(e)(g) —— 从 P2 上提，否则 §6.3 的「POST 建档再 PUT 补字段」会被 (e) 的 null 覆盖打断 | — |
| P0 | ✅ | 服务端：模型 token TTL 收到 30 天 + `token_version` 撤销机制 + `DELETE /v2/ai-model-token/{uid}`（推翻 §9 决策 2）| — |
| P1 | ✅ | Skill：`wp_login.py` + 凭据存储 + `auth/current` 校验 | P0 |
| P1 | ✅ | Skill：`ensure-model`（查/建/补字段/取 token）、`revoke`、`whoami` | P0 |
| P1 | ✅ | Skill：`channels`（`view=user-edit` 列表 + 交互选择） | P0 |
| P1 | ✅ | Skill：`grant`（签 access token，缓存，判 `count: 0`） | `channels` |
| P1 | ✅ | Skill：`write`（分批 + 确认 + count 核对 + 401 自动重签一次） | 以上全部 |
| P2 | ⬜ | 服务端质量修补：§5.2 (c) | — |
| P1 | ✅ | Skill：`install.sh` + `VERSION`（打包分发，§6.7） | P1 全部跑通 |
| P1 | ✅ | 端到端实测：开发机（`local`）上跑通登录 → 写入 → 断言署名 | — |
| P2 | ⬜ | 线上复测（部署后重跑一次，确认线上无差异） | 服务端部署 |
| P2 | ⬜ | Skill 扩展：读取能力（`GET /v2/sentence`、`sentences-in-chapter`）与 `sentpr` PR 提交 | P1 |

(d)(e) 上提到 P0 的理由：§6.3 第 3 步在 (d) 落地前必须走「POST 创建 → PUT 补齐字段」两步，而 (e) 未修时那个 PUT 会把未传字段一律置 null，两个缺陷叠加使 ensure-model 无法可靠工作。

测试要求（`api-v13` 使用 Pest）：
- ✅ Feature test 覆盖新端点的 401 / 403 / 200 / 404 四条路径（`tests/Feature/AiModelTokenTest.php`）；
- ✅ `AiModelResourceTest` 断言 key / system_prompt 不外泄、owner 仍可取；
- ✅ `AiModelCrudTest` 断言 store 全字段、重名 409、update 增量不清空；
- ✅ `AccessTokenExpiryTest` 断言签出的 token 带 `exp` 且无权时 `count: 0`；
- ⬜ 端到端 test：登录 → 建模型 → 取 model token → 签 access token → 写句子 → 断言 `editor_uid == 模型 uid`（留待 Skill 落地时补，需要 sentences / pali_texts 等一批表的夹具）。

#### 测试环境

迁移文件含 Postgres 专有语句（`CREATE EXTENSION "uuid-ossp"`、`enum` 列等），**无法在 sqlite 上跑**，所以 `phpunit.xml` 里 `DB_CONNECTION=pgsql`、`DB_DATABASE=mint_test`。

`RefreshDatabase` 会清空目标库，**测试库必须与开发库 `visuddhinanda_20260311` 严格分离**——后者装着完整生产数据集（`sentences` 3.3 GB、`sent_sims` 3.6 GB）。库名写死在 `phpunit.xml` 里正是为了不让它跟着 `.env` 漂移。

数据库需由具备 `CREATEDB` 权限的角色创建（应用角色 `www` 没有该权限）：

```bash
sudo -u postgres createdb -O www mint_test
```

#### Skill 的验证方式（2026-08-05）

Skill 的验证不走 Pest——它是个纯客户端，测的是「对着服务端的响应形状与坑，客户端做对了没有」。做法是写一个模拟 API 的桩服务（复刻 `sign-in` 失败返回 400、`keyword` 模糊匹配、`access-token` 无权返回 `count: 0`、`sentence` 逐句静默跳过、返回字段名是 `book` 而非 `book_id` 这几处），把全流程跑一遍，验证点：

登录（含密码错）、`ensure-model` 幂等复跑、`channels` 列表、`grant` 缓存命中不重签、`write` 的 dry-run / 分批 / 覆盖警告 / 非交互式无 `-y` 时拒绝写入、部分写入时列出漏掉的句子、`count: 0` 时中止、模型 token 被撤销后自动重签一次再重试、fallback 顺序（同版本另一域名 → 另一版本同域名，且绝不落到 `local`）、`endpoint` 切换与 `--api` 不写回、`install.sh` 装出的副本可独立运行。

桩服务不进仓库：它编码的是「我以为服务端是这样」，留着会变成第二份契约来源，与 `references/api.md` 打架。

#### 开发机上的端到端实测（2026-08-05）

对 `local`（`php artisan serve`，开发库 `visuddhinanda_20260311`）跑了一遍完整链路：用户在真实终端里 `wp_login.py` 登录 → `ensure-model` 建档并取模型 token → `channels` 列出 130 个可编辑 channel → 写 3 条句子到「草稿二」的 `book 1 / paragraph 99901`（事先查过该位置在其所有 channel 里都是空的，只新增不覆盖）。

查库断言的结果：

- 3 条句子的 `editor_uid` = `79bb0934-…`（模型 uid），**不是** `ba5463f3-…`（本人 uid）——署名目标达成；
- `language` / `status` 继承自 channel（`zh-Hans` / 10），与 `store()` 的逻辑一致；
- `sent_histories` 每条 1 行，`user_uid` 同为模型 uid，审计链成立；
- 改一句内容重跑，3 个 `uid` 不变、内容更新、每条历史累积到 2 行——`firstOrNew` 的幂等覆盖语义得到确认；
- 对一个无编辑权的 channel 跑 `grant`，服务端返回 `count: 0`，客户端按约定中止并报「没有编辑权」。

测试数据（3 条句子 + 6 行历史）已按 uid 精确删除，「草稿二」回到原有的 13 条；`claude-opus-5` 的 `ai_models` 记录保留，它就是日后真实写入要用的模型身份。

**线上仍未验证**：四个线上地址都还没部署 P0（`POST /api/v2/ai-model-token/x` 返回 404 而非 405 —— 已注册的路由用错方法会返回 405，未注册才是 404）。部署后应重跑一次同样的链路。

---

## 9. 决策记录

四个待确认问题已于 2026-08-04 定案：

| # | 问题 | 决策 | 影响 |
|---|---|---|---|
| 1 | 模型记录挂个人还是 group studio | **个人 studio** | §5.1 用 `canEdit()`，不引入 `StudioApi::userCanManage`；(a) 的遗留项 2 关闭 |
| 2 | 是否提供 token 撤销机制 | ~~不做~~ → **2026-08-05 推翻，改为做** | 加 `ai_models.token_version`，模型 token payload 增 `typ`/`ver`，TTL 从 365 天收到 30 天；旧模型 token 全部失效（见 §5.1、§7-2） |
| 3 | channel uid 如何获取 | **Skill 交互式选择** | 用 `GET /v2/channel?view=user-edit`，见 §6.4 |
| 4 | Skill 分发形态 | **在本仓库开发，以复制方式分发**；不做独立仓库 | 放仓库根 `plugins/wikipali/`，目录自包含、零依赖，可整体复制到其他项目；见 §6.1、§6.7 |
| 5 | 多站点（4 个线上 + 开发机）如何处理 | 四个线上地址**共享库与 `jwt_secrets_key`**，凭据只存一份（`online` / `local` 两桶），可任意切换与自动 fallback（2026-08-05 补） | 见 §6.1.2、§6.2。`.org`/`.cc` 是地区，`www`/`next` 是**代码版本**不是数据环境；随之而来的是 API 契约版本差，见 §6.1.2-4 |
| 6 | 用户怎么切 endpoint | `--api` 一次性覆盖**不写回**；改默认只经 `wp.py endpoint` 子命令；fallback **提示后切换**不静默（2026-08-05 补） | 见 §6.1.2「用户如何切换」。三条都指向同一个原则：当前连的是哪个站点，任何时候都应当是用户明确知道的 |
| 7 | 怎么发布给别人 | **Claude Code 插件 + 自建 marketplace**：目录文件放独立小仓库 `wikipali-plugins`，插件本体留在 mint，用 `git-subdir` 稀疏克隆（2026-08-05 补，修正决策 4 的「整目录复制」） | 见 §6.7。mint 不能直接当 marketplace——marketplace 是整仓 clone，580 MB 撞 120 秒超时。MCP server 形态排在插件跑通之后 |

决策 2 原本是「不做」，理由是省掉 `token_version` 可以不动 `ai_models` 表结构、不改 `getUserToken` 的 payload。2026-08-05 推翻：趁 Skill 尚未分发、代码尚未部署、外面一份真实凭据都没有的时候补，代价最小；再往后每多一份副本，「已签出 token 全部失效」的破坏面就大一分。
