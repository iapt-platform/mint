# WikiPali API 参考（写入路径）

本文只记 Skill 用到的端点。契约以**稳定版**（`www.*`）为准；标注「最新版」的能力可能在稳定版上还是 404。

基址记为 `{API}`，形如 `https://www.wikipali.org/api`。所有响应统一形如：

```json
{ "ok": true, "data": <任意>, "message": "" }
```

`ok: false` 时 `message` 是原因，HTTP 状态码同时表达语义。**HTTP 200 不等于全部成功**——见文末「静默跳过」。

## 三种 token，职责不能混

| Token | 从哪来 | 代表谁 | 用在哪 | 有效期 |
|---|---|---|---|---|
| userToken | `POST /v2/sign-in` | 人类操作者 | 查/建 ai-model、签 access token、列 channel | 365 天 |
| modelToken | `GET /v2/ai-model-token/{uid}` | AI 模型身份 | **写句子时的 `Authorization`** | 30 天，可撤销 |
| accessToken | `POST /v2/access-token` | 被委托的 channel 编辑权 | **写句子时的 body 字段** | 7 天 |

写句子时两者同时出现：`Authorization: Bearer <modelToken>`，句子对象里带 `access_token: <accessToken>`。用错会让 `editor_uid` 落成人类用户，署名与审计就废了。

---

## 1. 登录

`POST {API}/v2/sign-in`　body `{ "username": "<用户名或邮箱>", "password": "<明文>" }`

- `data` 是 JWT 字符串本身（不是对象）。
- 失败返回 **HTTP 400**，`message` 是 `invalid token`——措辞误导，实际含义是用户名或密码不对。

`GET {API}/v2/auth/current`（Bearer = userToken）

- `data: { id, nickName, realName, avatar, token, roles }`
- **`realName` 就是后面 `studio_name` 参数要用的值**，不是 `nickName`。

## 2. AI Model

`GET {API}/v2/ai-model?view=studio&name={studio_name}&keyword={模型名}`（Bearer = userToken）

- `data: { rows: [...], count }`；`keyword` 是 `like %kw%` **模糊**匹配，客户端必须自己做 `name` 精确比对。
- `view` 只接受 `all` / `studio` / `usable` / `chat`。**传别的值会 500**（服务端 switch 缺 default 分支，已知缺陷）。
- 行里的 `key` / `system_prompt` 只在请求者是 owner 本人时才返回。

`POST {API}/v2/ai-model`（Bearer = userToken）　body `{name, studio_name, model?, url?, key?, privacy?, description?, system_prompt?}`

- 一次 POST 即可带全字段，不需要再 PUT 补。
- 同一 studio 内 `name` 重复 → **409**，客户端据此判定「已存在」。
- `name` / `studio_name` 缺失 → 422。
- 鉴权要求 `studio_name` 就是操作者本人的 studio（个人 studio），group studio 一律 403。

`PUT {API}/v2/ai-model/{uid}`（Bearer = userToken）

- **增量更新**：只改请求里出现的字段，未提交的保持原值；显式传 `null` 才会清空。
- 改名撞上同 studio 内已有名字 → 409。

## 3. 模型身份 token

`GET {API}/v2/ai-model-token/{uid}`（Bearer = userToken）→ `data: { uid, name, token }`

- 只有模型 owner 本人能调；未登录 401，非 owner 403，模型不存在 404。
- 签出的 token 有效期 30 天，payload 带 `typ: "ai-model"` 与 `ver`。

`DELETE {API}/v2/ai-model-token/{uid}`（Bearer = userToken）→ `data: { uid, name, token_version }`

- 语义是**作废该模型已签出的全部 token**，无法只废一张。撤销后旧凭据一律 401。
- 这两个端点较新，稳定版站点上可能还没有 → 404 时先怀疑「站点代码版本旧」，而不是「模型不存在」。

## 4. 可编辑 channel 列表

`GET {API}/v2/channel?view=user-edit`（Bearer = userToken）→ `data: { rows: [...], count }`

- 语义：owner 是自己的 channel ∪ 协作权限 power ≥ 20 的 channel。
- 行内含 `uid / name / summary / type / owner_uid / lang / status / updated_at / created_at / role / studio`。
- 支持 `order` / `dir` / `limit`（默认 200）/ `offset` / `search`。

`GET {API}/v2/channel/{uid}` 可用于回显单个 channel 的名字。

## 5. 签发 channel access token

`POST {API}/v2/access-token`（Bearer = userToken）

```json
{ "payload": [ { "res_type": "channel", "res_id": "<channel uid>", "power": "edit", "book": 0 } ] }
```

→ `data: { rows: [ { payload, token } ], count }`

- **无权时该条被静默跳过**，返回 `count: 0` 和空 rows，HTTP 仍是 200。必须判空，等同 403 处理，**不可继续写入**。
- 返回的 `payload` 里含 `nbf` / `exp`，据此判断何时重签。
- ⚠ **`book` 必须是整数**。服务端校验用 `$jwt->book !== $book` 严格比较，而 `$book` 已被转成 int，写成 `"1"` 会让 `"1" !== 1` 恒真而永远鉴权失败。`0` 表示不限 book。

## 6. 写入句子

`POST {API}/v2/sentence`（Bearer = **modelToken**）

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
      "access_token": "<第 5 步签出的 JWT>"
    }
  ]
}
```

→ `data: { rows: [ ... ], count }`

- 语义：按 `(book_id, paragraph, word_start, word_end, channel_uid)` 做 `firstOrNew`——**存在即覆盖，不存在则新建**。天然幂等，但也意味着会静默覆盖别人写的同位置句子，写前必须向用户确认。
- 副作用：写 `sent_histories`（可追溯）、清缓存、发进度消息。
- 返回的 rows 用的是另一套字段名：`book`（不是 `book_id`）、`paragraph`、`word_start`、`word_end`、`channel.uid`、`editor`。核对写入结果要按这套字段匹配。
- 缺 `sentences` 字段时返回 **HTTP 200** 且 `message: "no date"`——这是客户端 bug，不是成功。

### 静默跳过

`store()` 对**逐句**鉴权失败是 `continue` 掉的，不报错。所以：

> 提交 N 条、HTTP 200、`count` 却小于 N，意味着有句子没写进去。

必须把返回的 rows 与提交的句子逐条比对，把差集报给用户。

---

## 错误约定

| 现象 | 含义 | 处置 |
|---|---|---|
| 401 | token 失效/过期/被撤销 | 提示重新登录或重取模型 token，**不要自动重试** |
| 403 | 无 channel 编辑权，或不是模型 owner | 指出缺哪一项权限 |
| 404（较新端点） | 站点跑的是旧版代码 | 提示切到最新版站点，别当成「资源不存在」 |
| 409 | 同 studio 内模型重名 | 当作「已存在」，回查列表取 uid |
| 422 | 参数校验失败 | 看 message |
| `access-token` 返回 `count: 0` | 对该 channel 无编辑权（静默跳过） | 当作 403，**中止写入** |
| `sentence` 的 `count` < 提交条数 | 部分句子鉴权失败被跳过 | 逐条比对并报告 |
| `message: "no date"` + 200 | 请求缺 `sentences` | 客户端 bug |

## 站点

四个线上地址**共享同一个数据库和同一把 JWT 密钥**，凭据完全通用，可随时切换：

| 地址 | 地区 | 代码版本 |
|---|---|---|
| `https://www.wikipali.org/api` | .org | 稳定版 |
| `https://www.wikipali.cc/api` | .cc | 稳定版 |
| `https://next.wikipali.org/api` | .org | 最新版 |
| `https://next.wikipali.cc/api` | .cc | 最新版 |
| `http://127.0.0.1:8000/api` | 开发机 | 另一个库、另一把密钥 |

- `.org` / `.cc` 是地区可达性；`www` / `next` 是**代码版本，不是数据环境**。
- 线上四个之间可以自动 fallback（同一套数据），**但绝不能自动退到 127.0.0.1**。
- 真正的风险不是写错库（写不错），而是写到不同版本的代码上，所以写入前要回显当前 api_url。
