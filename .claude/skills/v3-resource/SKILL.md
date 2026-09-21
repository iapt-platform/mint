---
name: v3-resource
description: "把 mint 的一个资源**从 /api/v2 迁移到 /api/v3**，或在 v3 下新建资源时用。按固定链路走完：FormRequest 定入参、Resource 的 @return array{} 定出参、契约测试卡住漂移、重生成 OpenAPI、生成前端 TS 类型、切换 dashboard-v6、删旧路由。触发：用户说「把 X 迁到 v3」「做 v3 重构」「v3 加个资源」「废掉 view 开关」「接口要符合 REST 标准」，或任务涉及 v2→v3 迁移、API 标准化、契约测试、从 spec 生成前端类型。**不要用于**：在现有 v2 接口上加 view 值、加字段、修 bug 这类增量改动（那用 api-change skill，改动范围小得多）；也不要用于把巴利语料、译文、术语、批注写进 WikiPali 数据库（那是 wikipali:write skill）。"
---

# v3 资源迁移

## 先确认没拿错 skill

| 你要做的事                                               | 用哪个           |
| -------------------------------------------------------- | ---------------- |
| 在现有**v2** 接口上加 view 值 / 加字段 / 改参数 / 修 bug | `api-change`     |
| 把资源**迁到 v3**、v3 新建资源、废掉 view 开关做标准化   | `v3-resource`    |
| 把巴利语料、译文、术语、批注写进**WikiPali 数据库**      | `wikipali:write` |

判断依据是**动的是 v2 还是 v3**：在 v2 原地改用 `api-change`，迁到 v3 用 `v3-resource`。
拿错了就停下换对的，不要将就。

## 为什么这么做

v2 的规格是**从代码里推断**出来的，注释一旦漂移，文档就错，agent 只能猜。
v3 要反过来：**声明是强制的，spec 和前端类型都是生成的，CI 卡住漂移**。

三件事，缺一不可：`声明 → 生成 → 契约测试`。

关键不在于找到完美的声明语法，而在于**声明与实际不符时会红灯**。

## 过渡期铁律：迁移 ≠ 删 v2

**`dashboard-v4` 在线上跑，已冻结不再升级，依赖 161 条 v2 路径。**
v6 还在测试上线阶段，等它稳定才会下线 v4。所以过渡期内：

> **v2 原地保留，v3 是并存的新端点。迁完一个资源也不要删它的 v2 版本。**

### 先分区，决定这个资源怎么迁

```bash
grep -rn "/v2/channel" dashboard-v4/dashboard/src   # 命中 = v4 在用
grep -rn "/api/v2/channel" dashboard-v6/src
```

| 分区  | 谁在用  | 条数 | 迁移策略                                                    |
| ----- | ------- | ---- | ----------------------------------------------------------- |
| **A** | 只有 v6 | 3    | 自由迁。`heartbeat`、`chapter-content`、`paragraph-content` |
| **B** | v4 + v6 | 118  | v3 建新端点，v6 灰度切；v2 原地冻结不动                     |
| **C** | 只有 v4 | 43   | **不迁**。等 v4 下线整块删，投入任何功夫都是沉没成本        |

**试点顺序：先做完 A 类 3 条**（零线上风险，用来打磨样板与 CI 三道闸），
再从 `channel` 开始啃 B 类。不要一上来就动 B 类。

### 灰度开关（第 6 步前端切换时必须加）

```ts
// src/api/channel.ts
const USE_V3 = import.meta.env.VITE_V3_RESOURCES?.split(",") ?? [];
const base = USE_V3.includes("channel")
  ? "/api/v3/channels"
  : "/api/v2/channel";
```

每个资源独立切换、独立回滚——出问题把资源名从环境变量里摘掉即可。
v4 永远走 v2，不受任何影响。

### 逻辑下沉到 Service

不要把逻辑在 v2 和 v3 各写一遍，那样 v2 的紧急修复成不了 v3 的资产，反而双倍维护。

```
                 ┌─ v2 Controller（冻结的适配层，只做形状转换）
Service（唯一逻辑）┤
                 └─ v3 Controller（FormRequest + Resource 新契约）
```

现成范例：`TipitakaReadParaController` 注入 `PaliContentService`。
迁移时把 v2 控制器里的业务逻辑抽到 Service，v2 控制器改成薄适配层——
**但它的响应形状、字段名、状态码一个都不准变**，v4 会碎。

### migration 只准加不准删

加列必须 nullable 或带默认值；禁止删列、改列名、改类型、加 NOT NULL。
v4 的代码在读这些列，而你不会去改 v4。要改名就新列并存 + 双写，等 v4 下线再清理。

v3 端点可以自由改：v4 那两处 v3 引用（`ChatInput.tsx:127`、`agentApi.ts:14`）
均不构成生产依赖，已确认。

## 一次只迁一个资源

不要大爆炸，不要一次迁多个资源。v2 与 v3 并存，逐个资源走完整条链，
每次的 diff 要小到人能 review 完。**没走完全部 7 步就不算迁完。**

## v3 硬规范

新端点必须满足，不满足就不要写：

1. **一个 index 只服务一个契约。**

   契约 = 三件事：**同一套权限模型、同一套必填参数、同一种响应结构**。
   三条里破任意一条，就说明那不是同一个端点，必须拆。三条都满足则留在同一个 index。

   **过滤是允许的，而且是正常的**——同一个 index 挂十几个 `filter[]` 是常态
   （用 `spatie/laravel-query-builder` 把 Model 的 local scope 暴露成 filter）。
   要禁止的是 v2 那种必填、互斥、还各自带不同必填伙伴参数的 `view=` 开关。

   **判断某个参数是过滤还是业务路径开关**，问一句：*去掉它，查询还成立吗？*
   - 成立 → 可选叠加的过滤，留在 index，写成 `filter[xxx]`
   - 不成立（必填 + 各值互斥）→ 业务路径开关，拆出去

   **最可靠的机械检验是必填参数那条**：一个 FormRequest 的 `rules()` 能不能干净地
   描述它？如果得写成下面这样，这串 `required_if` 就是在说「这里其实是三个端点」：

   ```php
   'name'    => 'required_if:view,studio,studio-all',
   'book'    => 'required_if:view,user-in-chapter',
   'book_id' => 'required_if:view,paragraphs',
   ```

   纯过滤永远是一串互不依赖的 `sometimes`，写起来很平。

   响应结构那条有灰区：传了 `filter[book]` 才多返回一个 `progress` 字段**不算**结构
   不同，只要 schema 里声明成可选（`progress?: float`）。结构不同指**顶层形状变了**，
   比如有的返回 `{rows, count}`、有的直接返回数组。

   拆出去的部分按 Laravel 官方形态落地（见下面「路由怎么设计」）。

   > 别把这条读成「一个 index 只能走一个分支」。Laravel 官方没有这句话，
   > index 内部有多少 `where` 条件分支都无所谓，那叫过滤。
2. **真实 HTTP 状态码。** 禁用 v2 的 `ok:false` + HTTP 200。401 未登录、403 无权限、
   404 不存在、422 校验失败。
3. **错误体用 RFC 9457 Problem Details**：`{type, title, status, detail, errors}`。
4. **资源名用复数名词**：`/v3/channels/{uid}`。不要 `/v2/channel-my-number` 这种
   动词化端点。（注意：现有 v3 端点 search / progress / tipitaka-read-para 是早期
   遗留的单数命名，新资源不要照抄。）
5. **分页统一**：用 Laravel 自带 paginator，返回 `data` + `meta{page, per_page, total}`。
6. **时间一律 ISO 8601 UTC；ID 一律字符串。** 不要让同一字段在不同端点类型摇摆。
7. **认证统一 Sanctum bearer。** 不准从 cookie 里摸 user_id 这类旁路。

## 路由怎么设计（Laravel 官方形态）

官方对这件事只有一句话，在 Supplementing Resource Controllers 一节：

> Remember to keep your controllers focused. If you find yourself routinely needing
> methods outside of the typical set of resource actions, consider splitting your
> controller into two, smaller controllers.

拆出去的部分按下面四种形态落地，不要自创：

| 形态 | 路由 | 什么时候用 |
| --- | --- | --- |
| **留在 index 用过滤** | `GET /v3/channels?filter[lang]=zh` | 可选叠加的筛选条件 |
| **补充路由 + 单动作控制器** | `GET /v3/channels/editable` | 权限模型独特的集合（`--invokable` 生成） |
| **嵌套资源** | `GET /v3/studios/{studio}/channels` | 从属关系；`->scoped()` 自动校验归属，权限代码少一半 |
| **单例资源** | `GET /v3/courses/{c}/members/me` | 只有一个实例（当前用户的成员记录、profile、summary） |

**路由顺序陷阱**（官方明确警告）：补充路由必须写在 `apiResource` **之前**，
否则 `/channels/{channel}` 会把 `/channels/editable` 吃掉，`editable` 被当成 id。

```php
Route::get('channels/editable', EditableChannelsController::class);   // 必须在前
Route::apiResource('channels', ChannelController::class);
Route::apiResource('studios.channels', StudioChannelController::class)->scoped()->shallow();
Route::apiSingleton('courses.members.channel', MemberChannelController::class);
```

动词化端点的统一处理：

| v2 模式 | v3 |
| --- | --- |
| `*-my-number`、`*-count`（纯计数） | 读 index 的 `meta.total`，或 `GET /v3/studios/{s}/summary` 单例 |
| `*-export` | `GET /v3/terms` + `Accept: text/csv`（内容协商） |
| `*-import` | `POST /v3/terms/imports`（导入作业资源化） |
| `*-tree` | `GET /v3/projects/{p}/tree` 或 `?include=descendants` |
| `sign-in` / `sign-up` / `auth/current` | `POST /v3/sessions` / `POST /v3/users` / `GET /v3/me` |

控制器内部：每个原 view 分支变成 Model 的 local scope（`scopePublic`、`scopeEditableBy`），
index 用 `spatie/laravel-query-builder` 把 scope 暴露成 `filter[]`，
控制器方法回到 10 行以内（官方 Keep Controllers Thin）。

## 迁移七步

### 0. 先盘点，列给用户确认

```bash
grep -rn "channel" api-v13/routes/api.php | grep v2
grep -rn "/api/v2/channel" dashboard-v6/src
cd api-v13 && php artisan tinker --execute 'print_r(\Illuminate\Support\Facades\Schema::getColumnListing("channels"));'
```

产出三份清单：这个资源在 v2 有哪些端点（含 view 值）、前端哪些文件在调、
表有哪些列及其**真实类型**。列给用户确认后再动手。

### 1. FormRequest 定入参

v3 **禁止 inline `validate()`**，一律 FormRequest。`rules()` 既是运行时强制，
又是 requestBody 的真声明——它不可能和实际行为不符，因为它就是实际行为。

```php
class IndexChannelRequest extends FormRequest
{
    /** @return array<string, string> */
    public function rules(): array
    {
        return [
            'filter.owner' => 'string|in:me,shared,system',
            'filter.lang' => 'string|size:7',
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:200',
        ];
    }
}
```

### 2. Resource 定出参

必须写 `@return array{...}` 声明。**类型去查 `information_schema` 核实**，
不要照字段名想当然——`type`、`source_id` 这类常是 varchar 而非数字。

```php
/**
 * @return array{
 *     uid: string,
 *     name: string,
 *     summary: string|null,
 *     type: string,
 *     status: int,
 *     created_at: string,
 *     progress?: float
 * }
 */
public function toArray($request): array
```

`key?:` 表示只在部分口径下返回；`|null` 会生成 `nullable: true`。
范例见 `api-v13/app/Http/Resources/ChannelResource.php`。

### 3. 契约测试（整套方案的地基）

前置：装 `hotmeteor/spectator`（目前**尚未安装**，第一个资源迁移时一并装上）。

契约测试让注释不可能悄悄漂移——写错了 CI 就红。每个端点至少三条：

```php
it('lists channels', function () {
    $me = makeStudio('me');
    makeChannel($me, 'my channel');

    $this->getJson('/api/v3/channels?filter[owner]=me', authHeader($me))
        ->assertValidRequest()      // 请求符合 spec
        ->assertValidResponse(200); // 响应符合 spec，字段类型对不上就失败
});

it('rejects anonymous access', function () {
    $this->getJson('/api/v3/channels')->assertStatus(401);
});

it('rejects invalid filter', function () {
    $this->getJson('/api/v3/channels?filter[owner]=bogus', authHeader(makeStudio('me')))
        ->assertStatus(422);
});
```

`makeStudio` / `makeChannel` / `authHeader` 是现成 helper，见
`tests/Feature/ChannelUserEditListTest.php`。

**每个 view 值拆出来的新端点都要单独测**——这是业务路径分叉，新写的查询与权限
逻辑没人跑过，最容易藏 bug。

### 4. 重新生成规格

```bash
cd openapi && php scripts/generate.php && npm run lint
```

lint 必须 0 error。不要手改 `resources/auto/` 下的任何文件。

### 5. 生成前端类型

```bash
npx openapi-typescript openapi/public/assets/protocol/main.yaml -o dashboard-v6/src/api/schema.d.ts
```

**跑过 `generate.php` 就必须跑这条**，不用问用户。`schema.d.ts` 已提交进版本库，
它的改动是本次改动的一部分，要一并留在工作区。

**类型一律从生成产物取，不要手写字段**：

```ts
import type { paths } from "./schema";   // 不写 .d.ts 后缀

type ChannelListResponse =
  paths["/v3/channels"]["get"]["responses"][200]["content"]["application/json"];
type ChannelListQuery =
  paths["/v3/channels"]["get"]["parameters"]["query"];
```

后端改了字段而前端没跟 → `npm run build` 直接报错。这是整条链路的最后一道闸。

### 6. 前端切过去

- `src/api/<资源>.ts`：调用路径改 `/api/v3/...`，类型引用 `schema.d.ts`
- 再改 `src/features/`、`src/pages/` 里的调用点
- 调用仍走 `src/request.ts`，路径硬编码完整 URL，**没有 baseURL**
- 按上面「灰度开关」那节加 `VITE_V3_RESOURCES` 开关

**`openapi-fetch` 尚未引入。** 它能把路径字面量与 HTTP 方法也纳入类型检查
（现在手写 URL 拼错了编译器抓不到），但**由用户自己安装**，agent 不要装。
用户确认装好之前，按上面的写法走。

### 7. 处理 v2 旧路由

**过渡期内不要删。** v4 在线上依赖它们，删了就是线上事故。

- **A 类**（只有 v6 用）：v6 切完、测试全绿后可以删。删前再 grep 一遍确认无遗留：
  ```bash
  grep -rn "/api/v2/chapter-content" dashboard-v6/src dashboard-v4/dashboard/src
  ```
- **B 类**（v4 也在用）：**保留**。给 v2 那个控制器方法加 `@deprecated` 标注
  （生成器认这个标签，Swagger UI 会显示废弃），并在注释里写明"已迁至 /v3/xxx，
  待 dashboard-v4 下线后删除"。
- 任何删除动作的前置条件：先枚举 `BookTreeWithTags.tsx:44` 与
  `SearchVocabulary.tsx:102` 的 `/v2/${api}` 动态调用可能传入的资源名。

## 验收：CI 三道闸

```bash
cd api-v13 && vendor/bin/pint --dirty --format agent && php artisan test --compact
cd openapi && php scripts/generate.php && git diff --exit-code   # spec 没重生成就失败
cd dashboard-v6 && npm run build                                  # 前端类型对不上就失败
```

三条都要过。**没有这三道闸，上面所有规范半年内都会腐化**——这比任何文档都重要。
（`tests/Feature/ExampleTest.php` 是历史遗留失败，可忽略。）

## 禁止事项

- 不要一次迁多个资源
- 不要在迁移里夹带无关重构（看到问题写 `// FIXME:`）
- 不要手写前端类型，不要手改 `resources/auto/`
- **不要擅自安装依赖**（含 `openapi-fetch`）。需要就说明理由，等用户装完通知
- 不要保留 `view=` 开关，不要保留 `ok:false` + HTTP 200（**仅指新建的 v3 端点**；
  v2 那份原样不动）
- **不要删 B 类和 C 类的 v2 路由**，v4 在线上用着
- 不要把逻辑在 v2 和 v3 各写一遍，抽到 Service 共用
- 不要凭字段名猜类型，一律查库核实
- 改完留在工作区，**不要自动 git commit**

## 收尾报告

用一句话说清：迁了哪个资源、v2 的哪几个端点/view 映射成了 v3 的哪几个端点、
补了几条契约测试、三道闸的结果。
