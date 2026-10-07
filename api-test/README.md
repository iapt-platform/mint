# api-test —— api-v13 的 `/api/v3` 契约测试

用 openapi-generator 从 `openapi/public/assets/protocol/main.yaml` 生成 PHP（Guzzle）客户端，
测试脚本是**纯 PHP**，用它来对 `/api/v3/*` 做契约冒烟 + 负向测试。**目前只测 v3 路由。**

## 运行

```bash
cd api-test

php run.php                          # 默认 local（http://127.0.0.1:8000/api）
php run.php --server=staging         # 切服务器（alias）
php run.php --server=https://foo/api # 任意完整 base URL
php run.php --filter=search          # 只跑名字含 search 的用例
php run.php --list                   # 列出可用服务器

# 跑 /v3/me/*（需登录）用例 —— 两种给 token 的方式：
php run.php --token=<bearer>                                   # 直接给 token
php run.php --username=alice --password=secret                 # 调 /v2/sign-in 现换 token
```

### 可用 `--server` 一览

| alias | URL | 说明 |
| --- | --- | --- |
| `local` | `http://127.0.0.1:8000/api` | 本地开发（默认） |
| `staging` | `https://staging.wikipali.org/api` | 测试 |
| `next` | `https://next.wikipali.org/api` | 生产预览 |
| `prod` | `https://www.wikipali.org/api` | 生产 |
| `next-cn` | `https://next.wikipali.cc/api` | 生产预览（中国） |
| `prod-cn` | `https://www.wikipali.cc/api` | 生产（中国） |

> 也可以 `--server=<完整 base URL>` 直连任意服务器（含 /api 前缀）。

- 服务器切换优先级：`--server=` / `API_TEST_BASE` > `config.php` 的 `default_server`。
- **鉴权优先级**：`--token=` > 环境变量 `API_TEST_TOKEN` > `--username/--password`
  （或 `API_TEST_USERNAME`/`API_TEST_PASSWORD`）调 `POST /v2/sign-in` 登录换 token。
  v3 只认 bearer token，**不用 username/password**——那两样只用于登录换 token。
- **测试逻辑**：需要登录的 `/v3/me/reactions`（GET/POST/DELETE）各测两种情况——
  无 token → 期望 401；带 token → 正常。带 token 的用例**缺 token 就 FAIL，不 SKIP**，
  所以跑全量前要带 `--token=` 或 `--username/--password`。
- **写操作在所有服务器上都真实执行**（POST 建 / DELETE 删，用垃圾 target_id，测完即删），
  包括 prod。会产生少量垃圾数据，由测试账号定期清理。
- 登录接口 `POST /v2/sign-in`：`username` 可以是用户名或邮箱；响应是 v2 信封
  `{ok,message,data}`，**成败看 `ok` 字段**（失败时 HTTP 可能是 200 也可能是 400），
  `data` 即 JWT。
- 退出码：`0` 全绿；`1` 有失败/错误；`2` 参数/加载错误。

## 目录结构

```
api-test/
├── run.php               # 入口 + 运行器
├── config.php            # 服务器 alias 与 token
├── fixtures.php          # 种子数据（channel/target_id/book 等，按环境可能要改）
├── src/bootstrap.php     # 断言/注册 helper（test/request/assert_*）
├── cases/*.php           # 用例，按领域分文件
├── spec/
│   ├── openapi.json      # 全量 spec 的 bundle（构建输入）
│   └── openapi-v3.json   # 切出的 v3 + 补丁（生成客户端的输入）
├── tools/
│   ├── filter-v3.php     # 切 /v3 + 修数组 items + 修 3 个漂移端点的响应 schema
│   └── regen.sh          # 一键重新生成客户端
└── client/               # openapi-generator 生成的 Guzzle 客户端（vendor/ 已 gitignore）
```

## OpenAPI 修改后如何升级测试

后端改了路由 / 控制器 docblock / FormRequest / Resource 之后，spec 会过时，测试要跟着升
两级：**先重生 spec，再重生客户端，最后人工核对用例**。客户端是自动生成的，但用例断言
不会自动跟着变。

### 1. 重新生成 OpenAPI spec（openapi 侧）

```bash
cd openapi
php scripts/generate.php   # 从 api-v13 的 route:list + 控制器 AST 重新生成 main.yaml
npm run lint               # redocly 校验，应为 0 error
cd ..
```

> 前端类型也跟着变了的话，还要按根目录 CLAUDE.md 重跑
> `npx openapi-typescript … -o dashboard-v6/src/api/schema.d.ts`（两份，mobile 那份别漏）。

### 2. 重新生成测试客户端（api-test 侧）

```bash
# 首次先下载 openapi-generator-cli jar（7.25.0）
mkdir -p /tmp/og
curl -L https://repo1.maven.org/maven2/org/openapitools/openapi-generator-cli/7.25.0/openapi-generator-cli-7.25.0.jar \
  -o /tmp/og/openapi-generator-cli.jar

# 一键：bundle main.yaml → 切 /v3 + 打补丁 → 生成 Guzzle 客户端 → composer install
bash api-test/tools/regen.sh
```

`regen.sh` 内部四步，都对应 spec 的某一部分：

| 步骤 | 做什么 | 产物 |
| --- | --- | --- |
| bundle | redocly 把 `main.yaml` 及所有 `$ref` 合并成单文件 | `spec/openapi.json` |
| filter | `filter-v3.php` 切出 `/v3/*` + 修数组 items + 修 3 个漂移端点 schema | `spec/openapi-v3.json` |
| generate | openapi-generator 生成 PHP(Guzzle) 客户端 | `client/`（会先 `rm -rf` 重来） |
| install | composer 装 Guzzle | `client/vendor/` |

> ⚠️ `client/` 是纯生成物，**不要手改**——`regen.sh` 会整个删掉重生成，手改会丢。

### 3. 人工核对 / 更新用例（不会自动）

客户端自动更新了，但 `cases/*.php`、`fixtures.php` 要人看：

- **新增端点** → 在 `cases/` 加对应 `test()`（参照现有文件按领域放）。
- **参数变了**（新增 required、改名、枚举增删）→ 改对应用例的入参。
- **响应字段变了**（Resource 的 `@return` 改了形状）→ 改对应断言。
- **种子数据失效** → `fixtures.php` 里的 `channel` / `target_id` / `search_doc_id` 换新值。

### 4. 跑全量验证

```bash
php run.php --username=test161 --password=12345          # 默认 local
php run.php --username=test161 --password=12345 --server=staging
```

- 如果新 spec 又冒出「spec 与实现不一致」导致客户端反序列化报错（如之前的
  `Invalid array 'object[]'`），在 `filter-v3.php` 的 `applyDriftPatches()` 补，并同步更新
  下面「已知漂移」表。
- 退出码 `0` 才算绿；`1` 看每条 `❌ FAIL` 的详情。

## 已知的 spec ↔ 实现漂移（记录在案，不是测试脚本的 bug）

| 端点 | spec 说 | 实际 | 处理 |
| --- | --- | --- | --- |
| `GET/POST /v3/search` | 200 `{data:数组, meta}` + 非法 `search_mode` 422 | 200 `{success, data:{took,hits}}`；`search_mode` 不校验 | `filter-v3.php` 把 200 schema 修为 `{success,data:对象}`；用例只断言状态码 + `data` 键 |
| `GET /v3/search-suggest` | 200 `{data:数组, meta}`；空 q 422 | 200 `{success, data:{query,suggestions}}`；空 q 400 `{success:false,error}` | 同上修 schema；空 q 用例按实际 400 断言 |
| `GET /v3/upgrade` | 200 `{data:数组, meta}` | 200 `{data:{status:"ok"}}` | 修 schema 为 `{data:对象}` |

根因是这三个控制器还没迁到 v3 的 Resource 信封（`{data}` 原生形状、错误体 RFC 9457
Problem Details、成败只看状态码）。正确的长期修法在 api-v13 侧，这里只让测试客户端能用、
把漂移显式记录。`filter-v3.php` 里的 `applyDriftPatches()` 就是这三处补丁，改对齐后可删。

## 环境注意

- **pspell / xdebug 启动警告**：本机 PHP CLI 的 pspell 扩展与 PHP 版本不匹配，每次 `php`
  都会在 stderr 打警告，与测试无关，可忽略。
- **staging 的 OpenSearch**：`/v3/search`、`/v3/search-suggest` 在 staging 会 500
  （索引 mapping 与代码不一致：`resource_type` 缺 fielddata、`content.suggest.pali` 没映射）。
  这是 staging 环境问题，本地跑这几条是绿的。
- **种子数据按环境不同**：`fixtures.php` 里的 channel/target_id/doc_id 取自本地开发库，
  换环境后可能需要更新。跨环境跑时，数据不存在通常只是返回空集（仍 200），不会假红，
  但 `search/{id}` 的 200 用例依赖真实 doc id。
- **写操作用垃圾数据 + 测完即删**：`/v3/me/reactions` 的增删查用例用固定垃圾
  `target_id`（uuid 格式），DELETE 会清理；若中途失败留下残留，下次跑时幂等逻辑会
  兜住（POST 返回 200 命中已有），最后由测试账号定期清理。
