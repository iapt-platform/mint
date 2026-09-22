---
name: v3-resource
description: "把 mint 的一个资源**从 /api/v2 迁移到 /api/v3**，或在 v3 下新建资源时用。按固定链路走完：FormRequest 定入参、Resource 的 @return array{} 定出参、补测试、重生成 OpenAPI、生成前端 TS 类型、把 dashboard-v6 彻底切过去（不留兼容分支）、在文档上标记 v2 旧端点。迁移期内一律不碰 v2 代码。触发：用户说「把 X 迁到 v3」「做 v3 重构」「v3 加个资源」「废掉 view 开关」「接口要符合 REST 标准」，或任务涉及 v2→v3 迁移、API 标准化、从 spec 生成前端类型。**不要用于**：在现有 v2 接口上加 view 值、加字段、修 bug 这类增量改动（那用 api-change skill，改动范围小得多）；也不要用于把巴利语料、译文、术语、批注写进 WikiPali 数据库（那是 wikipali:write skill）。"
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

### 不写兼容代码

**重构是为了提升，不是为了兼容。** 后端 v2 / v3 共存，但前端要分别对待：

> 灰度是**按资源**一部分一部分地做，而每一部分都要**彻底**。
> 一个资源切到 v3，就把它的 v2 调用删干净——不留开关、不留双分支、
> 不写把两种响应抹平的归一化层。

反面教材（曾经写过，已删）：

```ts
// ✗ 不要这样
if (isV3("health")) {
  const d = unwrapQuiet(await api.GET("/v3/heartbeat"));
  return { status: d.data?.status ?? "ok", checkedAt: d.data?.checked_at ?? "" };
}
const res = await get<V2Health>("/api/v2/heartbeat");     // v2 分支
return { status: "ok", checkedAt: (res as { createdAt?: string }).createdAt ?? "" };
```

这段有三样兼容代码：运行时开关、两条分支、一个抹平两种形状的接口。
**这种代码以后删起来很麻烦**——没人敢确认开关还有没有人用。

正确写法是切完就只剩一条路：

```ts
export const apiServerHealth = async (): Promise<Heartbeat> =>
  unwrapQuiet(await api.GET("/v3/heartbeat")).data ?? {};
```

**回退靠 `git revert` 那个 commit**，不靠代码里的开关。一个资源一个 commit，
这也是「一次只迁一个资源」的另一个理由。

生产上本来也不存在「运行时切换」这个选项：前端是 Vite 构建出的静态产物，由
nginx 伺服；无论改代码、改 `.env` 还是改 ops 的配置，都要**重新构建 + 重新部署**，
没有热更新。所以代码里的开关一分钱好处都买不到，只留下删不掉的负担。

同理，**v3 不要 import v2 的东西**：v3 用 `src/api/error.ts` 的 `ApiError`，
v2 用 `src/request.ts` 的 `HttpError`，两边不共用类型。等 v2 调用点全部消失，
`request.ts` 整个删掉，不牵连 v3。

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
2. **响应信封 = Laravel Resource 的原生形状，不要自己拼**

   | | 形状 | media type |
   | --- | --- | --- |
   | 成功·单个 | `{data: {...}}` | `application/json` |
   | 成功·列表 | `{data: [...], meta: {...}}` | `application/json` |
   | 失败 | `{type, title, status, detail, instance, errors?}` | `application/problem+json` |

   **判断成败只看 HTTP 状态码，不看响应体字段。** 这是与 v2 最根本的区别——
   v2 靠 `ok` 字段、业务失败也回 200；v3 靠状态码。

   **成功：直接 return Resource，控制器里没有任何 helper。**

   ```php
   return ChannelV3Resource::make($channel);                              // 单个
   return ChannelV3Resource::collection($query->paginate($per_page));     // 列表
   ```

   所有 `*V3Resource` 继承 `App\Http\Resources\V3Resource`。它只做一件事：
   让 `::collection()` 产出 `V3ResourceCollection`，裁掉分页里的绝对 URL
   （顶层 `links`、`meta.path`、`meta.links` 都是 `APP_URL` 拼的，反代下会拼错，
   前端也用不到）。其余全交给框架：`meta` 里的 `current_page` / `per_page` /
   `total` / `last_page` / `from` / `to` 都是 paginator 自己算的。

   载荷不是 Eloquent 模型时直接用基类，`additional()` 补 meta：

   ```php
   return V3Resource::collection($items)->additional(['meta' => [
       'current_page' => $page, 'total' => $total, 'has_more' => $more,
   ]]);
   ```

   meta 的键**跟框架走**（snake_case）；领域特有的字段另起名字，不要占用
   `per_page` / `from` / `to` 这些有既定含义的键（例如按字节切页的阅读接口
   用 `page_size` / `first_para` / `last_para`）。

   **失败：抛异常，不要在控制器里拼错误响应。** `bootstrap/app.php` 的处理器
   会统一渲染成 Problem Details，**只接管 `api/v3/*`**，v2 不受影响：

   ```php
   abort(404, __('site.not_found'));                             // → 404
   throw ValidationException::withMessages([                     // → 422，带 errors
       'channel' => __('site.invalid_parameter'),
   ]);
   $this->authorize('update', $channel);                         // → 403
   // FormRequest 校验失败自动 → 422，什么都不用写
   ```

   不必为 `ModelNotFoundException`（`findOrFail`）和 `AuthorizationException`
   单独注册处理器：`prepareException()` 在 render 回调之前已把它们转成
   404 / 403。`AuthenticationException` 则不在那张表里，已显式处理成 401。

   **需要自定义 type / 扩展字段的业务错误**才写 `BusinessException`：

   ```php
   throw new BusinessException(__('sentence.locked'), 409, 'sentence-locked',
       ['locked_by' => $editor->nickname]);
   ```

   它自带 `render()`，优先级高于所有处理器。**5xx 的 detail 在生产环境会被兜底
   处理器抹掉**（防泄露），所以「预期的 5xx、文案必须送达」的场景必须用它，
   不能用 `abort(503)`——见 `HeartbeatV3Controller` 的停机分支。

   **v2 的 `ok()` / `error()` 不准出现在 v3 控制器里。**

3. **资源名用复数名词**：`/v3/channels/{uid}`。不要 `/v2/channel-my-number` 这种
   动词化端点。（注意：现有 v3 端点 search / progress / tipitaka-read-para 是早期
   遗留的单数命名，新资源不要照抄。）

   **类名用 `<v2 资源名>V3Controller`**，不要另起炉灶起新名字：

   | v2 | v3 |
   | --- | --- |
   | `HeartbeatController` | `HeartbeatV3Controller` |
   | `ChannelController` | `ChannelV3Controller` |
   | `DhammaTermController` | `DhammaTermV3Controller` |

   Resource、FormRequest、测试同理：`HeartbeatV3Resource`、
   `IndexChannelV3Request`、`HeartbeatV3Test`。

   理由：新旧类会长期共存（v4 下线前 v2 一直在），名字带上 v2 的资源名才能
   一眼看出父子关系。**不要按新端点的语义另起名**——`/v3/heartbeat` 别叫
   `HealthController`，那会和既有的 `HealthCheckController` 混淆，也切断了与
   `HeartbeatController` 的血缘。

   **URL 也沿用 v2 的资源名，只换版本前缀**，不要借迁移之机改名：
   `/v2/heartbeat` → `/v3/heartbeat`，不要改叫 `/v3/health`。
   改名会切断 v2↔v3 的对应关系，迁移期内两套并存，对不上号就很难排查。

   唯一允许的调整是**按 REST 规范做复数化**（集合用复数，单例保持单数）：

   | v2 | v3 | 说明 |
   | --- | --- | --- |
   | `/v2/heartbeat` | `/v3/heartbeat` | 单例资源，保持单数 |
   | `/v2/channel` | `/v3/channels` | 集合，复数化 |
   | `/v2/terms` | `/v3/terms` | 已经是复数，不动 |

   动词化端点（`/v2/channel-my-number`）不在此列，它们本来就要按
   「路由怎么设计」那节重新落位。

   v3 控制器的文档注释里写明取代了哪个 v2 端点：

   ```php
   /**
    * 存活检查
    *
    * 取代 `GET /v2/heartbeat`（`HeartbeatController@index`）。v2 那份在
    * dashboard-v4 下线前原样保留，不要改动。
    */
   ```
4. **分页交给框架。**

   ```php
   return ChannelV3Resource::collection($query->paginate($request->integer('per_page', 20)));
   ```

   `paginate()` 自己会读 `page` 查询参数，count、页数、边界全由框架算，
   `meta` 由 `V3ResourceCollection` 输出（已裁掉绝对 URL）。控制器里只写业务查询。

   只有**不是 Eloquent 查询**的端点才手写 meta（例如 `TipitakaReadChapterController`
   按字节累加切页），用 `V3Resource::collection($items)->additional(['meta' => [...]])`，
   **对外形状必须与框架一致**：`{data: [...], meta: {...}}`，键名跟 snake_case。

   **列表端点必须能把结果集收敛到可读范围。** 不加条件就能拉全表的端点是设计
   错误——`sentences` 有 414 万行，没人会翻 20 万页。设计时先问「用户实际带着
   什么条件来」：若那条件必填且互斥，按第 1 条它就不是 filter 而是契约的一部分，
   应该体现在路由上（`/v3/channels/{channel}/books/{book}/sentences`）。

   收敛之后性能不是问题：实测 sentences 按 channel+book 过滤后 3,019 行，
   `count(*)` 8ms，offset 5000 仍是 4ms。不需要 `cursorPaginate`。

5. **时间一律 ISO 8601 UTC；ID 一律字符串。** 不要让同一字段在不同端点类型摇摆。
6. **认证统一 Sanctum bearer。** 不准从 cookie 里摸 user_id 这类旁路。
7. **面向用户的文案一律走 `__()`，不准硬编码。**

   翻译文件在 **`api-v13/resources/lang/{locale}/`**（注意不是 Laravel 11+ 默认的
   `lang/`，本项目把 langPath 指到了 resources 下）。支持 8 个语言：
   `en`、`zh-Hans`、`zh-Hant`、`my`、`th`、`si`、`vi`、`lo`。

   **不要新建语言文件，合并进已有的那 11 个**：`site`、`labels`、`buttons`、
   `home`、`library`、`auth`、`validation`、`passwords`、`pagination`、
   `grammar`、`language`。（注意没有 `messages.php`。）
   API 的服务状态类文案放 **`site.php`**，界面标签放 `labels.php`，
   按钮文字放 `buttons.php`，各就各位。

   新增文案至少补 `en` 与 `zh-Hans` 两份，其余语言缺失会自动回退到 `en`，
   不必一次补齐。

   RFC 9457 的字段分工：**`title` 保持稳定英文**（按 RFC 它应在所有场合一致，
   便于机器识别），**`detail` 是面向人的文案，走 `__()`**。

   ```php
   'title'  => 'Service Unavailable',
   'detail' => __('site.maintenance'),
   ```

   配一条测试守住退化——翻译文件缺失时 `__()` 会原样吐出键名：

   ```php
   expect($detail)->not->toBe('site.maintenance');
   ```

   > ⚠️ 已知问题：`SetLocale` 中间件只挂在 `web` 组，**api 组没有语言协商**，
   > 所以目前 API 响应永远是 `en`，`Accept-Language` 与 `?lang=` 均无效。
   > 文案仍然必须外置到翻译文件（否则将来想修也修不了），但别指望它现在会变中文。

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

### 1. FormRequest 定入参（有入参才需要）

**有入参就必须用 FormRequest，禁止 inline `validate()`；零入参的端点不要建
空 FormRequest**——那只是仪式，没有任何声明价值。

`rules()` 既是运行时强制，又是 requestBody 的真声明——它不可能和实际行为不符，
因为它就是实际行为。

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

### 3. 测试（契约测试尚未落地，这是已知缺口）

**现状：`hotmeteor/spectator` 未安装，项目里也没有可用的 JSON Schema 校验器，
所以目前写不了真正的契约测试。**这意味着**规格与实现漂移不会被自动拦截**——
这是整套方案里唯一没落地的一环，写测试时要清楚自己没有这层保护。

在装上之前，写普通的 Pest 形状断言：状态码、关键字段存在性与类型、权限分支。
每个端点至少三条：正常返回、无权限被拒、参数非法被拒（无入参的端点可省最后一条）。
测试文件开头用注释写明「这里断言的是形状而非契约」，装上 spectator 后好定位替换。

装上之后，把形状断言换成 `assertValidRequest()` / `assertValidResponse()`：



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

生成器对 v3 路由（`api/v3/*`）用的是另一套信封，不用你操心：

- 成功响应是 `{data: ...}`（Laravel Resource 的默认包装），
  `index` 额外带 `meta{page, per_page, total}`
- 错误响应引用 `ProblemDetails`（RFC 9457），不是 v2 的 `{ok, message, data}`
- 默认挂 401；**有入参时**才挂 422

两个标签控制这部分，写在控制器方法的文档注释里：

```php
/**
 * 存活检查
 *
 * @unauthenticated                       // 该端点无需登录，不要挂 401
 *
 * @responseStatus 503 服务已进入停机维护状态   // 声明额外状态码，响应体是 Problem Details
 */
```

范例见 `api-v13/app/Http/Controllers/HealthController.php`。

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

v3 的调用**一律走类型化客户端** `src/api/client.ts`（基于 `openapi-fetch`，已安装）。
v2 的老调用继续用 `src/request.ts`，不要动。

```ts
import { api, unwrap } from "./client";

// 路径写 OpenAPI 规格里的 path，client 的 baseUrl 已经是 "/api"
const data = unwrap(await api.GET("/v3/channels", {
  params: { query: { "filter[lang]": "zh-Hans" } },
}));
```

这样写的收益：**路径字面量、HTTP 方法、路径参数名、响应字段类型全部编译期检查**。
路径拼错、对只有 GET 的端点用 POST，`npm run build` 直接报错——手写 URL 拿不到这层保护。

两个必须知道的点：

- **`openapi-fetch` 非 2xx 不抛异常**，返回 `{ data, error }`。一律用
  `client.ts` 的 `unwrap()`，不要绕过它直接读 `data`。
- **错误不用在调用点处理**：`unwrap()` 失败时弹提示（消息来自后端已本地化的
  `detail`）并抛出，全局兜底收尾。**不要写 try/catch 只为弹窗**，也不要再写
  `if (!json.ok)`。
- 自己渲染失败状态的调用（轮询、状态条）用 `unwrapQuiet()`，只抛不弹——
  否则网络一断就每隔几秒弹一次。见 `api-health.ts`。
- 表单要字段级错误时才自己 catch，用 `fieldErrorsOf(e)` 取 422 的 `errors`。
- **token 注入已在 `client.ts` 的中间件里做掉**，不要在调用处再拼 Authorization。

还要做的：

- 把资源名加进 `src/api/v3-migration.ts` 的 `V3_RESOURCES`，两条分支都保留
- 改 `src/features/`、`src/pages/` 里的调用点
- 不要引入 RTK Query / react-query 这类数据层

### 7. 标记 v2 旧路由（不碰 v2 代码）

**过渡期内一律不动 v2**——不删路由、不改控制器，连加 `@deprecated` 注释都不行。
修改 v2 的唯一理由是紧急 bug 修复。v4 在线上依赖它们，任何改动都是风险。
删除留到 v4 下线后统一处理。

要在文档上标记某个 v2 端点已被取代，走 **overrides**，这样不碰一行 PHP：

```yaml
# openapi/public/assets/protocol/overrides/v2-heartbeat.yaml
get:
  deprecated: true
  description: |
    已被 `GET /v3/heartbeat` 取代，保留仅为 dashboard-v4 兼容，待 v4 下线后删除。
```

同时在 v3 控制器的文档注释里写明它取代了哪个 v2 端点，让两边都能追溯。

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
- **不要擅自安装依赖**。需要就说明理由，等用户装完通知
- 不要保留 `view=` 开关，不要保留 `ok:false` + HTTP 200（**仅指新建的 v3 端点**；
  v2 那份原样不动）
- **不要删 B 类和 C 类的 v2 路由**，v4 在线上用着
- 不要把逻辑在 v2 和 v3 各写一遍，抽到 Service 共用
- 不要凭字段名猜类型，一律查库核实
- 改完留在工作区，**不要自动 git commit**

## 收尾报告

用一句话说清：迁了哪个资源、v2 的哪几个端点/view 映射成了 v3 的哪几个端点、
补了几条契约测试、三道闸的结果。
