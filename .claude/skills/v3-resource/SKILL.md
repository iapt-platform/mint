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

> **动 v3 端点时这张表不适用。** v3 有第三个消费者：独立仓库
> `/home/deploy/workspace/wikipali-mobile`（移动端，开发阶段未上线，用
> openapi-fetch 调 /api/v3/*）。改 v3 之前三个前端都要 grep：
>
> ```bash
> grep -rn "v3/xxx" dashboard-v4/dashboard/src dashboard-v6/src \
>     /home/deploy/workspace/wikipali-mobile/src
> ```
>
> 2026-09 漏过一次，把 mobile 正在用的 tipitaka-read-para / -chapter 删掉了。

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

### Service 怎么用：不为共享去改 v2

**不要把 v2 控制器里的逻辑抽到 Service 再让两边共用。** 抽取动作本身就要改 v2，
而一个 Service 同时服务两套契约，最后一定会长出开关和分支——正是本 skill 禁止的兼容代码。
**两个都照顾，两个都照顾不好。**

按代码种类分：

| 种类 | 怎么办 |
| --- | --- |
| **契约相关**：查询构造、权限判定、响应组装、view 分支 | **不共享，v3 重写** |
| **纯能力**：与请求/响应形状无关，进去模型出来数据 | **共享现成的** |

现成的纯能力 Service（已经在被两边共享，继续用）：

```
PaliContentService   ← ChapterContentController(v2) / V3\TipitakaReadingController / …
OpenSearchService    ← TipitakaContentController(v2) / V3\SearchPlusController / …
UserService          ← 全仓库（v2 经 UserApi 转发壳，v3 直接注入）
```

v3 需要而现成 Service 没有的逻辑，**v3 自己写一份**；v2 里那份同名逻辑原地烂着，
等 v4 下线一起删。

**那 v2 的 bug 怎么办？** 常规做法是「bug 报告 = 迁移触发器」：迁 v3、在 v3 修、
v6 同 commit 切过去，v2 那份不碰。只有两种情况直接改 v2——必须 1 小时内修好的，
以及会污染 / 丢失数据库数据的（v4 和 v6 共用一个库）。详见根目录 CLAUDE.md 铁律 2。

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
   return ChannelResource::make($channel);                              // 单个
   return ChannelResource::collection($query->paginate($per_page));     // 列表
   ```

   所有 v3 Resource 继承 `App\Http\Resources\V3\BaseResource`。它只做一件事：
   让 `::collection()` 产出 `BaseResourceCollection`，裁掉分页里的绝对 URL
   （顶层 `links`、`meta.path`、`meta.links` 都是 `APP_URL` 拼的，反代下会拼错，
   前端也用不到）。其余全交给框架：`meta` 里的 `current_page` / `per_page` /
   `total` / `last_page` / `from` / `to` 都是 paginator 自己算的。

   载荷不是 Eloquent 模型时直接用基类，`additional()` 补 meta：

   ```php
   return BaseResource::collection($items)->additional(['meta' => [
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
   不能用 `abort(503)`——见 `V3\HeartbeatController` 的停机分支。

   **v2 的 `ok()` / `error()` 不准出现在 v3 控制器里。**

   ### 写操作各自返回什么

   | 动作 | 状态码 | 响应体 |
   | --- | --- | --- |
   | `store` | 201 | **新建的资源本身**（调用方需要 id 才能后续引用） |
   | `update` | 200 | 更新后的资源 |
   | `destroy` | **204** | **空**。硬删就是删了，没有资源可回 |

   三条依据都指向 204：RFC 9110 §9.3.5 说动作已执行且「no further information is
   to be supplied」就用 204；GitHub 的 star/unstar 与 reactions 删除都是 204 空体；
   Laravel 的 `assertNoContent()` 默认就是 204 **且断言响应体为空**——框架把这个
   组合当成了默认预期。

   ```php
   public function destroy(Request $request, Reaction $reaction)
   {
       $this->reactions->remove($reaction, $this->currentUser($request)['user_uid']);

       return response()->noContent();
   }
   ```

   **mutation 不返回共享聚合值**——计数、总数、排名、排行位次。理由不是洁癖：

   - 它当场就可能是错的（你删的同时别人在点，返回的数字发出去就过期了）
   - 它给了假的权威感，前端会拿它当真值缓存
   - 它把「改一次状态」和「读一次聚合」耦在一起，将来想给聚合加缓存都不好加

   X(Twitter) 的 like / unlike 只回 `{"data":{"liked":false}}`，连计数都不给；
   GitHub 干脆 204。前端用乐观更新 ±1，下次读聚合端点时自然校正——这是
   TanStack Query 文档里的标准套路（`onMutate` 快照 + 失败回滚 +
   `onSettled` invalidate）。聚合值有自己的读端点，mutation 不该再抄一份。

   **判据：这个值客户端自己算得出来吗？**

   - 算得出来（计数 ±1、列表里去掉一项）→ 不回，前端乐观更新
   - 算不出来且当场就要用（服务端分配的 id、剩余配额、余额）→ 回

   > **软删是例外**：行还在、只是标了 `deleted_at`，资源并没有消失。按 AIP-164
   > 这种情况应该返回**更新后的资源**（200），不是 204。测试也要跟着换成
   > `assertSoftDeleted()`——软删的模型用 `assertModelMissing()` 会失败，因为行还在表里。

   **destroy 的测试两条都要写**，它们抓的失败不一样：

   ```php
   $this->deleteJson("/api/v3/me/reactions/{$mine->id}", [], authHeader($uid))
       ->assertNoContent();           // 只有它：可能返回 204 但压根没删
   $this->assertModelMissing($mine);  // 只有它：删对了但多吐了不该有的 body
   ```

   **前端注意**：`openapi-fetch` 对 204 返回 `{ data: undefined }`，而
   `client.ts` 的 `unwrap()` 见到 `data === undefined` 会当失败、弹提示再抛异常。
   所以 204 端点要用 `unwrapVoid()`（只判状态码，不取 body）。

3. **资源名用复数名词**：`/v3/channels/{uid}`。不要 `/v2/channel-my-number` 这种
   动词化端点。（注意：现有 v3 端点 search / progress / tipitaka-read-para 是早期
   遗留的单数命名，新资源不要照抄。）

   **v3 的类放在 `V3\` 子命名空间，名字里不带 `V3`**，资源名沿用 v2 的：

   | v2 | v3 |
   | --- | --- |
   | `App\Http\Controllers\HeartbeatController` | `App\Http\Controllers\V3\HeartbeatController` |
   | `App\Http\Controllers\ChannelController` | `App\Http\Controllers\V3\ChannelController` |
   | `App\Http\Resources\ChannelResource` | `App\Http\Resources\V3\ChannelResource` |
   | — | `App\Http\Requests\V3\IndexChannelRequest` |

   命名空间已经说明了版本，类名再带一次是冗余；同名也不冲突。基类是
   `App\Http\Resources\V3\BaseResource`。测试保持 `tests/Feature/` 扁平，
   文件名用 `<资源>V3Test` 区分。

   **判据：`/v3/*` 路由指向的控制器，全都应该在 `Controllers/V3/` 下。**

   理由：新旧类会长期共存（v4 下线前 v2 一直在），名字带上 v2 的资源名才能
   一眼看出父子关系。**不要按新端点的语义另起名**——`/v3/heartbeat` 别叫
   `HealthController`，那会和既有的 `HealthCheckController` 混淆，也切断了与
   `HeartbeatController` 的血缘。

   **URL 也沿用 v2 的资源名，只换版本前缀**，不要借迁移之机改名：
   `/v2/heartbeat` → `/v3/heartbeat`，不要改叫 `/v3/health`。
   改名会切断 v2↔v3 的对应关系，迁移期内两套并存，对不上号就很难排查。

   **但改名是允许的，不需要审批**——v3 是破坏性重构，v2 有不少名字本来就名不副实。
   改了就往根目录 CLAUDE.md 的「v2 → v3 对照表」加一行。那张表不是门禁是地图：
   看到 v3 的名字与 v2 对不上时先查表，别当成错误去"修正"。

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
   return ChannelResource::collection($query->paginate($request->integer('per_page', 20)));
   ```

   `paginate()` 自己会读 `page` 查询参数，count、页数、边界全由框架算，
   `meta` 由 `V3ResourceCollection` 输出（已裁掉绝对 URL）。控制器里只写业务查询。

   只有**不是 Eloquent 查询**的端点才手写 meta（例如 `V3\TipitakaReadingController`
   按字节累加切页），用 `BaseResource::collection($items)->additional(['meta' => [...]])`，
   **对外形状必须与框架一致**：`{data: [...], meta: {...}}`，键名跟 snake_case。

   **列表端点必须能把结果集收敛到可读范围。** 不加条件就能拉全表的端点是设计
   错误——`sentences` 有 414 万行，没人会翻 20 万页。设计时先问「用户实际带着
   什么条件来」：若那条件必填且互斥，按第 1 条它就不是 filter 而是契约的一部分，
   应该体现在路由上（`/v3/channels/{channel}/books/{book}/sentences`）。

   收敛之后性能不是问题：实测 sentences 按 channel+book 过滤后 3,019 行，
   `count(*)` 8ms，offset 5000 仍是 4ms。不需要 `cursorPaginate`。

   **但"下载全量"这类端点必须用 keyset 游标，不能把结果集捞进 PHP。**
   实测最大的 channel 有 **522,718 段、跨 217 本书**：

   | 做法 | 代价 |
   | --- | --- |
   | `->pluck()` 全量再在 PHP 里切片 | 821 ms，**峰值内存 281 MB** |
   | keyset 取一页 | **3 ms** |

   keyset 的三条约定：

   - **游标用行值比较**：`whereRaw('(book_id, paragraph) > (?, ?)')`。比
     `book > ? or (book = ? and para > ?)` 短，而且能走复合索引。
   - **游标对客户端不透明**：服务端在 `meta.next_cursor` 给出，客户端原样回传，
     `null` 表示取完。内部形式（`{book}-{para}`）是实现细节。
     这跟"路径里不要放复合 id"不矛盾：复合 id 要人手写，游标是机器回传的 token。
   - **越界不报错**：keyset 语义下"游标在范围之后"就是空结果。游标既然由服务端给，
     再去校验它落在哪个区间就没有意义了。

   **进度用的 `total` / `remaining` 只在带了收敛 filter 时给**，无 filter 时省略
   （schema 里声明成可选），让调用方用 `next_cursor` 判断结束。无 filter 时
   `count(distinct …)` 在 52 万行上要几百毫秒，每翻一页算一次不划算。

5. **控制器只是胶水层：粘 request、service、response，不放业务逻辑。**

   ```php
   public function __invoke(TipitakaReadingRequest $request, string $channel)
   {
       $page = $this->reading->page($channel, $request->validated());

       return ReadParagraphResource::collection($page['items'])
           ->additional(['meta' => $page['meta']]);
   }
   ```

   控制器里**只允许**出现这四件事：取 `validated()`、调 Service、包 Resource、
   `abort()` / `throw`。查询构造、权限判定、分页切块、聚合统计、领域规则一律进 Service。

   **「少量业务逻辑写在控制器里也还行」是个陷阱。** Laravel 官方说控制器保持在
   10 行以内即可，那是以项目静态为前提的。本仓库六年下来的经验正相反：项目刚开始
   业务逻辑很少，后来逐渐增加，等到不可控时已经散落在一百多个控制器里，既没法复用
   也没法单测。所以这里的规则比官方更硬——**无论多少，业务逻辑一律进 Service，
   没有"少量可以接受"这条缝**。有缝就会被撑开。

   **自检：控制器文件里除了入口方法，还有别的方法吗？** 有就是漏了。
   （`Concerns\` 下的共用胶水 trait 不算——它提供的是取当前用户这类跨控制器的
   粘合动作，不是业务逻辑。）
   反面教材（本仓库真实发生过）：`TipitakaReadingController` 一度 314 行，带着
   `scopedQuery` / `chapterRange` / `applyCursor` / `sliceByBytes` / `paragraphLengths`
   / `progressMeta` / `filterInclude` 七个业务方法——那是个穿着控制器外衣的 Service。

   **v3 自己的 Service 放 `App\Services\V3\`**，与控制器的 `V3\` 对齐：

   | 放哪 | 什么 |
   | --- | --- |
   | `App\Services\`（根） | 纯能力，v2/v3 共享：`PaliContentService`、`OpenSearchService`、`UserService` |
   | `App\Services\V3\` | v3 契约相关：查询构造、权限判定、分页游标、领域规则 |

   这跟「不为共享逻辑去改 v2」不冲突：那条说的是**不要和 v2 共用**，不是说写进控制器。
   v3 重写出来的逻辑，落点是 `App\Services\V3\`。

   Service 返回**普通数组 + `@return array{...}` 声明**（仓库现有风格，见
   `PaliContentService::readParagraph`），不引入 DTO 层。

   好处不只是好看：业务逻辑在 Service 里可以直接单元测，不必走 HTTP。本仓库的
   `TipitakaReadingSliceTest` 为了测一个纯函数，得 `new class(app(PaliContentService::class))
   extends Controller` 造匿名子类——这就是逻辑放错地方的味道。

6. **时间一律 ISO 8601 UTC；ID 一律字符串。** 不要让同一字段在不同端点类型摇摆。
7. **认证走 `auth.v3` 中间件，只认 bearer。**

   ```php
   // 整组挂
   Route::prefix('me')->middleware('auth.v3')->group(...);

   // 或按方法挂——读公开、写要登录，不必为此改 URL
   Route::apiResource('channels', ChannelController::class)
       ->middlewareFor(['store', 'update', 'destroy'], 'auth.v3');
   ```

   `App\Http\Middleware\V3\Authenticate`（别名 `auth.v3`）做三件事：挡掉没带
   bearer 的请求、校验 token、把解出的用户放进 request attributes。控制器用
   `Concerns\ResolvesCurrentUser` 的 `currentUser($request)` 取，不重复解 JWT；
   可选登录的端点（如 tally）不挂中间件，用 `currentUserUid($request)`，没登录返回 null。

   **别在控制器第一行写 `if (! $user) throw`。** 中间件跑在 FormRequest 之前，
   所以「未登录 + 参数非法」得到的是 401 而不是 422——这是语义上正确的顺序，
   写在控制器里就拿不到。

   **只认 bearer 这件事在中间件里自己挡**：`AuthService::current()` 取不到 bearer
   时会回落读 `$_COOKIE['user_uid']`，那是 v2 的旁路。不要去改 `AuthService`——
   它是 v2 也在用的共享代码（铁律 2）。

   **鉴权和 URL 形状是两件事**：要让某些动作需要登录，挂中间件就行，不需要把它们
   塞进 `/v3/me/` 前缀。而且中间件只管得了 401，「钥匙对不对」的 403 得靠 Policy
   或 Service 里抛 `AuthorizationException`。
8. **用户建立的实质内容：软删除 + 乐观锁。**

   **哪些算**：用户亲手产出、删错了会心疼的东西——`article`、译文（`sentence`）、
   术语（`dhamma_terms` / `user_dicts`）、`collection`、`channel`。
   **哪些不算**：操作记录、日志、聚合、reactions、view、recent——可重建，硬删就好。

   判据一句话：**删错了用户会心疼吗？**

   > **缺列时停下来问用户，不要自己加 migration。** 加列是不可逆的库结构变更，
   > 由用户决定。这跟「不要擅自安装依赖」是同一条规矩。

   ### 软删除

   现状（2026-09 实测）：**16 张表已经有 `deleted_at` 列**（articles / channels /
   collections / dhamma_terms / user_dicts / sentences / attachments / chats …），
   但**只有 `Sentence` 挂了 `SoftDeletes` trait**。所以多数情况下不需要 migration，
   缺的是代码。

   **加 trait 之前必须查三件事**——模型是 v2/v3 共享的，加 trait 会同时改变 v2 的行为：

   1. **业务唯一索引**。软删的行仍占着唯一键，用户删了再建会撞。实测目标表的唯一
      索引只有主键，安全；但 `likes` 的 `UNIQUE(type, target_id, user_id)` 会中招——
      这类表不要软删（reactions 正因如此是硬删）。
   2. **裸 `DB::table()` 查询绕过全局作用域**，会把已删的行算进来。实测
      `dhamma_terms` 3 处、`sentences` 2 处，其余 0。逐个看过再动。
   3. **v4 没有回收站 UI**。软删对它等同于删除，可接受；但别指望 v4 能恢复。

   API 形态按 AIP-164：

   - `destroy` → **200 + 更新后的资源**，不是 204。资源没消失，只是标了 `deleted_at`，
     204「没有内容可给」的前提不成立。
   - 恢复 → `POST /v3/xxx/{id}/restore`（补充路由 + 单动作控制器）。
     AIP 写成 `:undelete`，但冒号动作不符合本项目的路由判据。
   - 测试用 `assertSoftDeleted()`，**不能用 `assertModelMissing()`**——行还在表里，会失败。

   ### 乐观锁

   为什么值得做：两个人同时编辑同一条译文，后保存的会**静默覆盖**前一个人的工作，
   而且没有人会发现。这是这个项目最值得防的一类数据丢失。

   **机制用 ETag + `If-Match` → 412，不要在 body 里塞 `version` + 409。**
   RFC 9110 把 412 Precondition Failed 定义成「条件请求的前置条件不成立」，这正是
   丢失更新的语义；409 Conflict 说的是「请求与资源当前状态冲突」（用户名已占用那种）。
   混用会让客户端和缓存分不清。412 的教科书用例就是乐观并发控制。

   ```
   GET /v3/articles/{id}                    → 200, ETag: "7"
   PUT /v3/articles/{id}  If-Match: "7"
       version 仍是 7 → 写入，version + 1，返回新 ETag
       version 已变   → 412 Precondition Failed
   PUT 不带 If-Match                        → 428 Precondition Required
   ```

   **ETag 不能用 `updated_at` 生成。** 实测所有目标表的 `updated_at` 是
   `timestamp(0)`，只有秒精度——同一秒内的两次更新分辨不出来，而那恰恰是乐观锁
   要防的场景。需要一个自增的 version 列，**缺就问用户**。
   （`sentences.version` 与 `sentences.ver` 已存在且全仓库无引用，是死列，可以直接用。）

   **前端：412 不能静默重试**——那等于把别人的改动覆盖掉，比不做乐观锁还糟。
   必须提示「这条在你编辑期间被改过」，给出重新加载或查看差异的入口。

   **生成器目前不支持声明请求/响应头**（只有 `@queryParam` / `@bodyParam` /
   `@urlParam`）。做第一个乐观锁资源时要先给它加 header 支持，否则 ETag 与
   If-Match 不会进规格，前端类型也拿不到。

9. **批量操作：路由和控制器都要带 `batch` 字样。**

   ```php
   // 字面量段必须排在 apiResource 之前，否则 /terms/{term} 会把 batch 当成 id
   Route::post('terms/batch',        BatchTermController::class);        // 批量创建
   Route::post('terms/batch-delete', BatchDeleteTermController::class);  // 批量删除
   Route::apiResource('terms', TermController::class);
   ```

   **不要把批量伪装成单条端点。** 反面教材就在本仓库：`DELETE /v2/userdict` 的
   `id` 参数收的是 JSON 数组字符串，从 URL 和方法名完全看不出它一次能删一批。
   调用方读文档才知道，审计日志里也分不清。

   **批量删除用 POST，不用 DELETE。** RFC 9110 §9.3.5：「A client SHOULD NOT
   generate content in a DELETE request. Content received in a DELETE request has
   no generally defined semantics, cannot alter the meaning or target of the
   request, and might lead some implementations to reject it.」——要删哪些只能靠
   请求体传，而 DELETE 带 body 既无定义语义，也可能被中间件拒掉。Google 的
   AIP-235 同理用 `POST …:batchDelete`（冒号动作不合本项目的路由判据，用
   `/batch-delete` 这个段代替）。

   **默认整体事务：全成功或全失败。** 别默默部分成功——调用方拿到 200 却不知道
   有三条没进去，是最难排查的一类 bug。AIP-233 / 235 也是原子为默认，部分成功
   必须由 `return_partial_success` 这类显式开关打开，并在响应里逐条给出失败原因。
   真要支持部分成功，就得设计一个逐条结果的响应体，那是另一个契约，别顺手做。

   **必须有条数上限，写进 FormRequest。** 不设上限的话一次请求就能打垮库：

   ```php
   'ids'   => ['required', 'array', 'min:1', 'max:200'],
   'ids.*' => ['uuid'],
   ```

   响应：

   | 动作 | 状态码 | 响应体 |
   | --- | --- | --- |
   | 批量创建 | 201 | 创建出来的资源列表（调用方要 id） |
   | 批量删除（硬删） | **204** | 空 |
   | 批量删除（软删） | 200 | 更新后的资源列表（同第 8 条） |

   别返回「成功了几条」这种计数——原子事务下它恒等于请求的条数，是废话；
   非原子才需要，而非原子本来就不该是默认。

10. **写操作的入参：`store` 按业务必填，`update` 只做 PATCH。**

    ### 只注册 PATCH，不注册 PUT

    Laravel 的 `apiResource` 把两个动词路由到**同一个** `update()`
    （`ResourceRegistrar:402`：`$this->router->match(['PUT', 'PATCH'], $uri, $action)`）——
    框架只给一个方法承载两种语义。注册了 PUT 却做局部更新，是在契约上撒谎；
    真做全量替换，客户端漏传一个字段就误删。所以只暴露实现了的那一个：

    ```php
    Route::apiResource('channels', ChannelController::class)->except(['update']);
    Route::patch('channels/{channel}', [ChannelController::class, 'update'])
        ->whereUuid('channel');
    ```

    PUT 随之返回 405，这是对的——它本来就没实现。

    ### FormRequest：store 分必填，update 一律 sometimes

    ```php
    // StoreChannelRequest —— 业务上真的必填才写 required
    'name' => ['required', 'string', 'max:255'],
    'lang' => ['required', 'string', 'size:7'],
    'summary' => ['sometimes', 'nullable', 'string'],

    // UpdateChannelRequest —— 一个 required 都不要有
    'name' => ['sometimes', 'string', 'max:255'],
    'lang' => ['sometimes', 'string', 'size:7'],
    'summary' => ['sometimes', 'nullable', 'string'],
    ```

    ### 控制器只用 `validated()`，绝不用 `$request->input('x')`

    ```php
    $channel->fill($request->validated())->save();   // 只有传了的字段会被改
    ```

    **反面教材在 v2 的 `ChannelController@update`：**

    ```php
    $channel->summary = $request->input('summary');   // 没传 → null → 清空
    ```

    用户只想改个名字，`summary` 没传就被写成 null，**没有任何报错**。
    `input()` 分不清「没传」和「传了 null」，`validated()` 分得清——这是两者的
    根本差别，不是风格问题。

    ### 这条修的是一个现在就存在的矛盾

    实测 v2 生成的 `v2-channel-channel.yaml` 里，**PATCH 也把 5 个字段声明成必填**
    （因为 PUT / PATCH 共用同一份 docblock）。前端从这份规格生成类型，结果是
    「局部更新」必须传全部字段。只保留 PATCH 之后，required 列表为空，规格与实现
    才对得上。

    ### PATCH 涵盖不了什么

    「传全部字段的 PATCH」**不等于** PUT：PUT 的语义是「没传的字段重置为默认/空」，
    PATCH 永远只认你传了什么。真需要「把这条记录整个重置」，用
    `POST /v3/xxx/{id}/reset` 这种补充路由明确表达——靠 PUT 顺带表达重置，
    客户端漏传一个字段就变成误删，正是上面那个毛病。

    社区口径一致：GitHub 用 PATCH，Stripe 用 POST 做局部更新，Google AIP-134
    只用 PATCH + `update_mask`（`*` 才是全量替换）。**本项目不需要 `update_mask`**——
    protobuf 要它是因为分不清「没传」和「传了零值」，PHP 的 `validated()` 分得清。

11. **面向用户的文案一律走 `__()`，不准硬编码。**

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

## 路由怎么设计

### 先过四道判据

**这四条是本项目的约定，不是行业标准。** REST 从没规定过 URL 长什么样——Fielding
本人写过 "A REST API **must not** define fixed resource names or hierarchies"，
RFC 3986 也把 path 与 query 都定义成「标识资源」，只区分层级 / 非层级。所以别去找
"正确答案"，照下面四条走：**一致比正确值钱**。

**① 路径开头是「领域-功能」，不是通用坐标。**

`channel + book + para` 在 wikipali 里是**通用坐标**——sentences、wbw、批注、进度
全都用它。把坐标放路径开头，每个新能力都要来抢同一个前缀，而且看不出这条 API 干什么：

```
✗  /v3/channels/{channel}/books/{book}/paragraphs/{para}
       坐标开头。看不出是"读三藏"还是别的；还会跟将来真正的 /v3/channels 资源混
       （生成器按路径第一段打 tag，这两条会被归到 channels 标签下）

✓  /v3/tipitaka-reading/{channel}?book=&para=
       领域-功能开头，坐标降成 filter
```

「领域-功能」写成**一个段**（`tipitaka-reading`），不要写成 `tipitaka/reading` 两段：
裸命名空间段不能解引用（`GET /v3/tipitaka` 是 404），而一个段后面跟 `{id}` 是正常的
集合形态。

**② 必填进路径，可选留查询串。**

| 方向 | 规则 | 依据 |
| --- | --- | --- |
| 必填 → 路径 | 它在指认"是哪一个"，属层级数据 | RFC 3986 §3.3 |
| 路径 → 必填 | 路径参数**没有"可选"这回事** | OpenAPI 强制 `required: true` |

**检验方法：把问号后面整段砍掉，请求还成立吗？** 成立就对了。

副作用是好的：这条逼你给每个可选参数定默认值。

**例外——本质非层级的参数，即使必填也留查询串**：自由文本（`/v3/search?key=`，
检索词里一个斜杠就把路径劈开）、多值集合（`?ids=a,b,c`）。判断方法：这个值能不能
安全地当一个路径段？值域是 id / slug / 数字就能，是用户输入的任意文本就不能。

**③ 路径变量最多两个，最好只有一个。**

```
✓  /v3/channels/{channel}
✓  /v3/studios/{studio}/articles/{article}
✗  /v3/channels/{channel}/books/{book}/paragraphs/{para}      三个，太多
```

超过两个就回头想：**是不是有参数其实是 filter？是不是业务逻辑该重新定义？**
`tipitaka-reading` 就是这么从三个降到一个的——重新想清楚「这个 API 是为阅读和下载
设计的，只有 channel 必填，book / para 是过滤条件，不给就是取整个 channel」。

**④ 需要校验归属关系 → 路径。**

`->scoped()` 只对**路径参数**生效，框架自动验证 channel 确实属于那个 studio；
写成 `?studio=x` 就得自己写一遍权限判断。

### `/v3/me/` 什么时候用

问一句：**`{owner}` 这个位置，将来会不会出现 `me` 以外的值？**

- **会** → **magic ID**：`GET /v3/studios/me/channels`。一个路由模板、一份权限逻辑，
  `me` 只是 `{studio}` 的取值之一（Gmail 的 `users/me`，Google AIP-122 明确认可）。
  配套要守 AIP-122 那条 must：**响应里回真实 id，不能回 `"me"`**。
- **不会** → **独立端点**：`/v3/me/reactions`。它与 `/v3/reactions` 必填参数相反
  （那边 `target_id` 必填，这边不能有）、权限相反，是两个契约，不是别名。

**别把鉴权和 URL 形状绑在一起。** 想用中间件挡 401 不需要路径前缀：
`Route::apiResource(...)->middlewareFor(['store','update'], 'auth.v3')`，或者控制器
实现 `HasMiddleware`，都能按方法挂。而且中间件只管得了 401，「钥匙对不对」的 403
它做不了——前缀买到的东西比看上去少。

反面：`me` 定义成"我有钥匙的资源"会让同一个资源有两个 URL（我协作的 channel 同时
出现在 `/v3/me/channels` 和 `/v3/studios/{g}/channels`），这正是 AIP-122 的
canonical name 要防的，也是 GitHub `/user/repos` 被诟病的形态。

### Laravel 官方的四种形态

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

**路由约束必须挂在每条路由上，不能挂在 prefix 组上链式调用。**
`RouteRegistrar` 的 `where` 是**整体替换而不是合并**，后一个会把前一个顶掉，
而且不报错——约束静默失效，非法参数一路打到数据库才炸：

```php
// ✗ whereUuid('channel') 被 whereNumber('book') 顶掉了，channel 没有约束
Route::prefix('channels/{channel}/books/{book}')
    ->whereUuid('channel')->whereNumber('book')->group(...);

// ✓ 挂在 Route 对象上，Route::where() 是合并
Route::get('...', Xxx::class)->whereUuid('channel')->whereNumber(['book', 'para']);
```

顺带一条契约后果：**路径参数格式不对是 404 不是 422**（匹配不上任何路由），
查询参数格式不对才是 422（走 FormRequest）。写测试时别搞反。

### 路由文件按领域拆

`routes/api.php` 里 v2 有 156 条声明、134 行 import，v3 铺开后还要翻倍。
**按领域拆成 `routes/v3/<领域>.php`**，与 `dashboard-v6/src/components/` 的领域对齐，
找东西只有一个心智模型。

```php
// routes/api.php —— 前缀和名字前缀只在这里加一次
Route::prefix('v3')->as('v3.')->group(function () {
    require __DIR__.'/v3/system.php';
    require __DIR__.'/v3/tipitaka.php';
    require __DIR__.'/v3/channel.php';
    require __DIR__.'/v3/interaction.php';
});
```

三条注意：

- **子文件里不要再包一层 group**，漏写会让路由落到 `/api/` 根下**而且不报错**
- **不要用 `glob()`**：跨文件的注册顺序由 require 顺序决定，glob 会变成按字母排且看不见
- **按需建文件，不要一次建十几个空的**——空文件会让人以为那个域已经在迁了

`withRouting(api: [...])` 也接受文件数组，但那样每个文件都得重复 `prefix('v3')`
的包装，漏一个就静默出错，所以用单入口 require。

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
scope 和 filter 的组装写在 Service 里（硬规范第 5 条），控制器只负责调它。

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

**Resource 不准查库。** v2 到处是 `UserApi::getByUuid($this->editor_id)` 这种逐行
查询（仓库里约 30 处），一页 15 行就是 15~30 次查询。v3 的做法：

```php
// 模型：uuid 列对 user_infos.userid，三参数 belongsTo（同款见 Channel::owner()）
public function user(): BelongsTo     { return $this->belongsTo(UserInfo::class, 'user_id', 'userid'); }
public function aiModel(): BelongsTo  { return $this->belongsTo(AiModel::class, 'user_id', 'uid'); }

// 控制器：列白名单收在 UserService 里，别手抄
$query->with(UserService::eagerLoadActor());

// Resource：use ResolvesActor，一行
'user' => $this->actor($this->user, $this->aiModel),
```

三个要知道的点：

- **受限列必须含匹配列**（`userid` / `uid`），漏了关系会**静默对不上、整列变 null**，
  不报错。所以列白名单收在 `UserService::eagerLoadActor()`，不要在控制器里手抄。
- 顺带的安全收益：`->first()` 是 `SELECT *`，会把 `user_infos.password`、
  `ai_models.key`（API 密钥）逐行捞进内存；受限列挡住了。
- **关系的 ownerKey 是非主键列时，目标模型要声明 `$keyType`**。`AiModel` 漏了这行，
  Eloquent 按默认 int 主键走 `whereIntegerInRaw`，把 uuid 全转成 `0`。

**配一条 N+1 守卫测试**，断言的是**不变量**而不是具体数字：

```php
// 12 行与 2 行的查询数必须相同；硬编码 4 会被 Laravel 内部变动误伤
expect($countQueriesFor(12))->toBe($countQueriesFor(2));
```

（`DB::enableQueryLog()` **不清空历史**，两次测量之间要 `DB::flushQueryLog()`。）

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

- 成功响应是 `{data: ...}`（Laravel Resource 的默认包装）；
  **返回 `::collection()` 的就是列表**（带 `meta`），返回 `::make()` 的是单个——
  按返回值判断，不看方法名叫不叫 `index`，所以单动作控制器也对
- 错误响应引用 `ProblemDetails`（RFC 9457），不是 v2 的 `{ok, message, data}`
- 默认挂 401；**有入参时**才挂 422
- **单动作控制器（`__invoke`）支持**
- **子命名空间支持**：短类名按控制器的 `use` 语句解析，所以
  `App\Http\Resources\V3\XxxResource` 能正确找到
- `Default:` / `Example:` 的字面量会按参数声明的类型转换（整数参数不会配字符串示例）

> 这几条都是 2026-09 补的。**生成器静默失败是这套方案最危险的地方**：找不到 Resource
> 就返回空 schema、认不出路由就整条漏掉，lint 全过、没有任何报错。改完规格养成
> 抽查一眼的习惯——`grep -c "type: string" resources/auto/<你的端点>.yaml`。

两个标签控制这部分，写在控制器方法的文档注释里：

```php
/**
 * 存活检查
 *
 * @unauthenticated                       // 该端点无需登录，不要挂 401
 *
 * @responseStatus 503 服务已进入停机维护状态   // 额外状态码。2xx 视为另一种成功口径
 *                                            //（响应体与 200 同形），非 2xx 是 Problem Details
 *
 * @meta next_cursor string 下一块的游标      // 手写 meta 的端点用它声明 meta 字段，
 *                                            // 不写就按框架的 PaginationMeta 出
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
- **不要为了共享逻辑去改 v2**：现成的纯能力 Service 直接用，其余 v3 自己写一份
- 不要凭字段名猜类型，一律查库核实
- 改完留在工作区，**不要自动 git commit**

## 收尾报告

用一句话说清：迁了哪个资源、v2 的哪几个端点/view 映射成了 v3 的哪几个端点、
补了几条契约测试、三道闸的结果。**改了名的话，提醒用户往 CLAUDE.md 的
「v2 → v3 对照表」加一行。**
