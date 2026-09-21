# CLAUDE.md

wikipali —— 开放的、基于语料库的巴利语学习与翻译平台。本仓库是 monorepo。

## 目录状态（重要）

**活跃维护：**

| 目录 | 是什么 | 技术栈 |
| --- | --- | --- |
| `api-v13/` | 后端，唯一在维护的 API | Laravel 13 / PHP 8.5 / Pest 4 / Inertia 3 |
| `dashboard-v6/` | 前端主站 | React 19 / Vite / antd 6 / Redux Toolkit |
| `openapi/` | API 规格与在线文档（Swagger UI） | 规格由脚本从 `api-v13` 源码生成 |
| `docker/` | 容器工具链与开发环境 | |
| `magnolia/`、`ai-translate/`、`langchain-server/`、`open-ai-server/`、`sutta-pitaka-importer/`、`scripts/` | 周边服务与工具 | |

**冻结但仍在线上运行（代码不要改，但它的依赖决定了后端能动什么）：**

- `dashboard-v4/` —— 线上生产前端，依赖 161 条 `/api/v2` 路径。
  **破坏 v2 就是线上事故**，详见下面「过渡期」一节。真实代码在
  `dashboard-v4/dashboard/src`，不是 `dashboard-v4/src`。

**已冻结，不再维护，不要改动、不要参照其写法、不要当作现状依据：**

- `api-v8/`
- `coconut/`
- `tulip/`

用户说“后端”指 `api-v13`，说“前端”指 `dashboard-v6`。在这两个目录之外动手前先确认。

## 后端 api-v13

- 详细的 Laravel/Pest/Pint 约定见 `api-v13/CLAUDE.md`（Laravel Boost 生成），在该目录工作时必须遵守。
- 路由集中在 `routes/api.php`：`/api/v2/*` 是主力业务接口（约 140 个 `apiResource`），
  `/api/v3/*` 是新版检索与阅读接口，`/api/ops/*` 是运维接口，需
  `Authorization: Bearer <APP_OPS_TOKEN>`。
- 响应统一封装 `{ok, message, data}`（见 `app/Http/Controllers/Controller.php` 的
  `ok()` / `error()`）。**业务失败时 HTTP 状态码仍可能是 200，以 `ok` 字段为准**，
  新代码沿用这个约定。
- 列表接口的通用查询参数：`search`、`order`、`dir`、`limit`、`offset`。
- 响应结构尽量走 `app/Http/Resources/*Resource`。
- 改完 PHP 跑 `vendor/bin/pint --dirty --format agent`；测试 `php artisan test --compact`，
  测试连独立的 `mint_test` 库（不要让 `RefreshDatabase` 打到开发库）。
- `storage/resources` 是 git submodule（clove）。
- 面向用户的文案一律走 `__()`。翻译文件在 **`resources/lang/{locale}/`**（不是
  `lang/`），共 8 个语言。**不要新建语言文件，合并进已有的 11 个**
  （`site`/`labels`/`buttons`/`home`/`library`/`auth`/… 没有 `messages.php`）；
  服务状态类文案放 `site.php`。至少补 `en` 与 `zh-Hans`，其余自动回退 en。

## 前端 dashboard-v6

- `src/request.ts` 是唯一的请求封装（原生 fetch，导出 `get/post/put/patch/delete_/upload/download/graphql`）。
  **没有 baseURL**：每个调用点硬编码完整路径 `/api/v2/...`，开发期由 `vite.config.ts` 的
  proxy 转发到 `http://127.0.0.1:8000`。新增调用请沿用这个写法。
- `src/api/index.ts` 里的 `api_url()` 和 `VITE_API_BASE` 是死代码，没有任何地方使用。
- 目录：`features/`（业务模块）、`pages/`、`components/`、`reducers/` + `store.ts`（RTK）、
  `locales/`（react-intl 多语言）。
- 命令：`npm run dev` / `npm run build` / `npm run lint`。
- **接口类型不要手写**，从 OpenAPI 生成（见下）。RTK 只用了普通 slice，
  没有 RTK Query / react-query / SWR，不要擅自引入数据层。

### 接口类型生成（agent 自动执行，不用问）

`dashboard-v6/src/api/schema.d.ts` 由 OpenAPI 规格生成，**已提交进版本库**，
不要加进 `.gitignore`。生成命令（在仓库根目录执行）：

```bash
npx openapi-typescript openapi/public/assets/protocol/main.yaml -o dashboard-v6/src/api/schema.d.ts
```

**何时必须重跑（满足任一条就直接跑，不必询问用户）：**

- 跑过 `openapi/scripts/generate.php` 之后 —— 规格变了，类型就得跟着变
- 改了 api-v13 的路由、控制器 docblock、FormRequest 规则、或 Resource 的
  `@return array{...}` 声明
- 前端要用某个接口或字段，但 `schema.d.ts` 里查不到它
- 前端构建报接口类型相关的错误，怀疑类型是旧的

跑完记得把 `schema.d.ts` 的改动一并留在工作区，它是这次改动的一部分。

`src/api/<资源>.ts` 里的类型从它引用，不要再手写字段：

```ts
import type { paths } from "./schema";   // 注意：不写 .d.ts 后缀

type ChannelResponse =
  paths["/v2/channel-name/{name}"]["get"]["responses"][200]["content"]["application/json"];
type ChannelListQuery = paths["/v2/channel"]["get"]["parameters"]["query"];
```

后端改了字段而前端没跟 → `npm run build` 直接报错。
验证类型时用 `npx tsc -b --force`（`tsc -b` 有增量缓存，不加 `--force` 可能不重新检查）。

**v3 的调用走 `src/api/client.ts`**（基于 `openapi-fetch`，已安装）：

```ts
import { api, unwrap } from "./client";
const data = unwrap(await api.GET("/v3/heartbeat"));
```

路径字面量、HTTP 方法、路径参数名、响应字段类型全部编译期检查。client 的
baseUrl 是 `/api`，所以路径写规格里的 path；token 由中间件注入；非 2xx 时
`openapi-fetch` 不抛异常，用 `unwrap()` 转成 `HttpError`。

**v2 的调用继续用 `src/request.ts`，不要动。**

## OpenAPI 规格

`openapi/public/assets/protocol/` 下的规格**全部自动生成，不要手改**。

```bash
cd openapi
php scripts/generate.php   # 从 api-v13 的 route:list + 控制器 AST 重新生成
npm run lint               # redocly 校验，应为 0 error
```

- 生成器解析控制器的文档注释、`$request->input()/query()`、`switch($request->input('view'))`
  的 case、`validate()` 规则、以及返回的 `*Resource::toArray()` 键。
- **接口参数说明写在控制器的文档注释里**，用 `@queryParam` / `@bodyParam` / `@urlParam`
  标签（`@标签 名字 类型 [required] 描述`，描述里可写 `Enum:` / `Default:` / `Example:`），
  生成器会解析成精确的 OpenAPI 参数。示例见 `ChannelController@index`。
- **响应字段的类型来自 Resource 的 `@return array{...}` 声明**；没写声明就只能按
  字段名猜（会猜错，如 `channels.type` 是 varchar 却被猜成 integer）。
  范例见 `api-v13/app/Http/Resources/ChannelResource.php`。
- 不适合写进注释的长篇补充才放 `openapi/public/assets/protocol/overrides/<同名 slug>.yaml`，
  深度合并到自动结果之上（注意数组是整体替换）。
- **后端路由或控制器有改动，就重跑生成器**，否则规格立刻过时。
- 细节见 `openapi/README.md`。

### 为什么是自研脚本，什么时候该换掉

社区有成熟方案（Scribe、swagger-php / L5-Swagger、Dedoc Scramble），当初没用是因为
v2 有三个它们处理不了的形态：`switch ($request->input('view'))` 抽枚举、
`{ok, message, data}` 信封、业务失败也返回 HTTP 200。

v3 的规范正在消除这三点。**等 v3 资源铺开，就评估换成 Scramble**（零注解、自动推断
FormRequest 与 Resource），自研脚本退化为只服务 v2 遗留部分；v4 下线、v2 删完后
一并删掉。在那之前不要提议换工具，也不要在 v3 成熟后还抱着自研脚本不放。

## 过渡期：v2 是线上冻结面（最高优先级，先读这节）

**`dashboard-v4` 正在线上运行，已冻结不再升级，它依赖 161 条 v2 路径。**
`dashboard-v6` 还在测试上线阶段。等 v6 稳定后才会下线 v4。在那之前：

> **破坏 v2 = 线上崩，而且不会有人发现** —— v4 无人维护、没有测试、改了也没人跑。

### 调用面分区（2026-09 盘点）

| 分区 | 谁在用 | 条数 | 策略 |
| --- | --- | --- | --- |
| **A** | 只有 v6 | 3 | 可自由迁 v3。`heartbeat`、`chapter-content`、`paragraph-content` |
| **B** | v4 + v6 | 118 | v2 **原地冻结**，在 v3 另建新端点，v6 灰度切过去 |
| **C** | 只有 v4 | 43 | **什么都不做**。不补注释、不写测试、不迁移，等 v4 下线整块删 |

判断某条路径属于哪区：

```bash
grep -rn "/v2/channel" dashboard-v4/dashboard/src   # 命中 = v4 在用，动不得
grep -rn "/api/v2/channel" dashboard-v6/src
```

**v4 的真实代码在 `dashboard-v4/dashboard/src`**（不是 `dashboard-v4/src`），
且有一批绕过请求封装的裸 URL（antd Upload 的 `action`、导出 `href`），grep 时别漏。

### 铁律

1. **不得改变 v2 的响应形状、字段名、状态码。** 加字段是安全的，改/删不是。
2. **修 bug 修在 Service 层**，v2 控制器只当适配层，不再新增业务逻辑。
   这样同一处修复能同时惠及 v2 与 v3——这是 v2 改动成为 v3 资产的唯一途径。
3. **碰之前先录快照测试**（characterization test）。不必一次给 161 条都补，
   但要动哪条就先把它当前的真实响应录下来，改完形状没变才算修对。
4. **migration 只准加，不准改删**：加列必须 nullable 或带默认值；
   禁止删列、改列名、改类型、加 NOT NULL。v4 的代码在读这些列，而你不会去改 v4。
5. **删除任何 v2 路由前**，必须先枚举 `dashboard-v4/dashboard/src` 里两处动态调用
   （`components/corpus/BookTreeWithTags.tsx:44`、`components/dict/SearchVocabulary.tsx:102`
   的 `/v2/${api}`）可能传入的资源名。在枚举完成前，v2 路由一律不准删。

### v4 与 v3 的关系（已澄清）

v4 有两处引用 v3，**均不构成生产依赖**：

- `components/chat/ChatInput.tsx:127` 调 `/v3/search-suggest`——所在的三个页面
  线上不可达，用户会彻底封闭该组件。
- `services/agentApi.ts:14` 硬编码 `http://localhost:8000/api/v3`，是未完成的开发残留，
  生产环境必然请求失败。

所以 **v3 端点可以自由改**。仍建议改 v3 时顺手 grep 一下 v4，成本近乎为零。

### 退出条件

v6 全量稳定 → 下线 v4 → 删掉 C 类与 B 类的 v2 残留 → v2 前缀整体下线。

## v3 重构

正在把资源从 `/api/v2` 逐个迁到 `/api/v3`。原则是 `声明 → 生成 → 契约测试`：
FormRequest 定入参、Resource 的 `@return array{...}` 定出参、契约测试卡住漂移、
spec 与前端类型全部生成。v3 端点不准有 `view=` 开关、不准 `ok:false` + HTTP 200。

**后端 v2 原地保留，前端切干净。** v2 与 v3 在后端共存到 v4 下线；但前端每迁
一个资源就要**彻底**切过去——删掉该资源的 v2 调用，不留开关、不留双分支、
不写抹平两种响应的归一化层。**重构是为了提升，不是为了兼容**；兼容代码以后
删起来很麻烦，没人敢确认还有没有人在用。

回退靠 `git revert` 那个 commit，不靠代码里的开关——这也是「一次只迁一个资源、
一个资源一个 commit」的理由。生产上本来也没有「运行时切换」这回事：前端是静态
构建产物，改代码、改 `.env`、改 ops 配置都得重新构建部署，没有热更新。

v3 前端代码不要 import v2 的东西：v3 用 `src/api/error.ts` 的 `ApiError`，
v2 用 `src/request.ts` 的 `HttpError`。等 v2 调用点全部消失，`request.ts`
整个删掉，不牵连 v3。

**v3 响应信封（已定死）**：就是 Laravel Resource 的原生形状——单个 `{data: {...}}`，
列表 `{data: [...], meta: {...}}`，失败 `{type, title, status, detail, instance, errors?}`
+ `application/problem+json`。**判断成败只看 HTTP 状态码。**

控制器里**没有任何响应 helper**：成功直接 `return XxxV3Resource::make()/::collection()`，
失败 `abort()` / `throw ValidationException` / `throw BusinessException`，
由 `bootstrap/app.php` 统一渲染（只接管 `api/v3/*`）。
`*V3Resource` 继承 `App\Http\Resources\V3Resource`，它负责裁掉分页里的绝对 URL。
**v2 的 `ok()` / `error()` 不准出现在 v3 控制器里。**

前端对应（**只管 v3**，v2 的 `request.ts` 错误语义不同，迁移期内不碰）：

- `unwrap()` —— 失败时弹提示并抛出。业务代码只写成功路径，**不用 try/catch、
  不用判断 `ok` 字段**（v2 那套在全仓库重复了 221 处）
- `unwrapQuiet()` —— 只抛不弹，给自己渲染失败状态的调用用（如心跳轮询）
- `ApiError` —— v3 专用错误类型，与 v2 的 `HttpError` 互不依赖
- `src/api/error.ts` —— 提示的唯一出口，将来换 Notification 只改这里；
  `installApiErrorHandler()` 在 `main.tsx` 装全局兜底，消掉未捕获 promise 的噪音
- 表单要字段级错误时自己 catch，用 `fieldErrorsOf(e)` 取 422 的 `errors`

**类名约定：`<v2 资源名>V3Controller`**（`HeartbeatController` → `HeartbeatV3Controller`），
Resource / FormRequest / 测试同理。新旧长期共存，名字带上 v2 资源名才能一眼看出父子
关系；不要按新端点语义另起名。**URL 同样沿用 v2 资源名**，只换版本前缀
（`/v2/heartbeat` → `/v3/heartbeat`），唯一允许的调整是集合复数化
（`/v2/channel` → `/v3/channels`）。

**做迁移时使用 `v3-resource` skill**，里面有完整的七步链路与模板。
一次只迁一个资源。试点顺序：先 A 类 3 条（零线上风险，用来打磨样板与 CI 三道闸），
再从 `channel` 开始啃 B 类。

## 渐进式还债

这个代码库有六年历史，欠债很多。规则是**碰到什么还什么**，但必须让每次的还债量
小到人能 review 完。三条硬约束：

**1. 只还你这次真正改到的那个方法的债，不扩散。**
改 `ChannelController@index` 就只清理 `index`，同文件的 `store`、`destroy` 一律不动，
哪怕一眼就看到问题。看到了就加 `// FIXME:`，留给下次碰到它的人。

**2. 功能改动和还债拆成两个 commit。**
先 `feat`/`fix` 提交功能本身，再 `refactor` 提交清理。这样 review 时能分开看，
清理动作出了问题也能单独回滚。

**3. 每次改动结尾，用一句话列出还了哪些债。**
比如「顺带：`index` 补了 docblock、switch 加了 default 保护」。没列出来的清理动作
等于没做过 review。

碰到旧方法时按这个顺序还（做得到就做，做不到就 FIXME）：

- 补中文 docblock 与 `@queryParam` / `@bodyParam` 标签（规格直接受益）
- 返回值走 `app/Http/Resources/` 的 Resource，而不是 `return $this->ok($model)` 吐原始模型
- 给碰到的 Resource 的 `toArray()` 补 `@return array{...}` 声明（响应类型直接受益）
- `$this->error()` 的参数位置写对：`error(string $message, mixed $data, int $status)`
- `switch` 补 `default` 分支，避免 `$table` 未定义直接 500
- `$this->error(...)` 后面漏掉的 `return` 补上
- 给你这次碰到的 view 值补测试

**不要做的事：** 不要为了"顺手"重写整个控制器、不要改动你没读懂的逻辑、
不要在功能改动里夹带大范围重构。拿不准就写 FIXME，成本最低。

现有的 FIXME 清单：`grep -rn FIXME api-v13/app/Http/Controllers/`

## 测试约定

**每个 view 值都要有测试覆盖。** 列表接口的 `view` 参数是业务路径分叉，每个值背后
是一段独立的查询与权限逻辑，新增的那段代码没人跑过，最可能藏 bug。

- **新增 view 值 → 必须补 Pest 测试**，至少两条：一条验证它返回预期数据，
  一条验证权限不足时被拒。模板见 `tests/Feature/ChannelUserEditListTest.php`，
  `makeStudio` / `makeChannel` / `authHeader` 是现成 helper。
- **新增字段 → 不用改测试**。项目里的断言都是 `toHaveKeys(...)` 这种「包含即可」
  的写法，没有 `assertExactJson` / `assertJsonStructure`，多返回一个字段不会打破谁。
- 需要改已有测试的只有：改了现有字段的名字或类型、改了权限规则、改了现有 view 的返回口径。
- 测试连独立的 `mint_test` 库，`RefreshDatabase` 不会清开发库。

## 分支与发布（先读这条）

**线上生产跑的就是本仓库的 `development` 分支。** 没有单独的发布分支，
也没有 CI 部署流程（`api-v13/.github/workflows/` 只有 lint 和 tests）。

所以：**提交到 `development` 就等于把代码推向生产路径**。这让下面两条约定
不只是习惯问题，而是安全边界：

- 改完留在工作区，**永远不要自动 `git commit`**，等用户 review
- **永远不要 `git push`**，除非用户明确要求

`dashboard-v4/deploy/` 里那套 ansible 是 v4 时代的遗留：它指向
`iapt-platform/mint` 的 `laravel` 分支，而本仓库的 remote 是
`visuddhinanda/mint`。**不要把它当作现在的发布流程依据。**

## 工作约定

- 改完代码留在工作区，等用户 review，不要自动 `git commit`。
- 文档文件只在用户明确要求时创建。
- **不要擅自安装或升级任何依赖**（npm、composer 都一样）。需要新依赖就说明理由，
  等用户自己装完通知你。
