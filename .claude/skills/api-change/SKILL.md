---
name: api-change
description: "在**现有 /api/v2 接口上做增量改动**时用，按固定顺序走完后端、OpenAPI 规格、前端三处，避免漏改。触发：给 v2 列表接口的 view 参数加值、给表或模型加字段、改 v2 的请求参数或返回结构、修 v2 控制器的 bug，以及任何会同时牵动 api-v13 与 dashboard-v6 的 v2 改动。用户说「加个 view」「加个字段」「改接口」「这个接口要返回 xxx」且对象是 v2 时适用。**不要用于**：把资源迁到 /api/v3、在 v3 下新建资源、废掉 view 开关做标准化重构（这些一律用 v3-resource skill）；也不要用于把巴利语料、译文、术语、批注写进 WikiPali 数据库（那是 wikipali:write skill）；纯前端样式、纯文档改动也不需要本 skill。"
---

# v2 接口改动流程

## 先确认没拿错 skill

| 你要做的事 | 用哪个 |
| --- | --- |
| 在现有 **v2** 接口上加 view 值 / 加字段 / 改参数 / 修 bug | `api-change` |
| 把资源**迁到 v3**、v3 新建资源、废掉 view 开关做标准化 | `v3-resource` |
| 把巴利语料、译文、术语、批注写进 **WikiPali 数据库** | `wikipali:write` |

判断依据是**动的是 v2 还是 v3**：在 v2 原地改用 `api-change`，迁到 v3 用 `v3-resource`。
拿错了就停下换对的，不要将就。

mint 的 OpenAPI 规格是**从 api-v13 源码生成的**，前端类型是**手写的**。
所以一次接口改动必须走完三处，顺序固定：后端 → 规格 → 前端。

## 动手前：确认这条路径动不动得

**`dashboard-v4` 在线上跑，已冻结不再升级，依赖 161 条 v2 路径。破坏 v2 = 线上崩，
而且不会有人发现**（v4 无人维护、没有测试）。所以先分区：

```bash
grep -rn "/v2/channel" dashboard-v4/dashboard/src   # 命中 = v4 在用
grep -rn "/api/v2/channel" dashboard-v6/src
```

| 分区 | 谁在用 | 条数 | 本 skill 该怎么做 |
| --- | --- | --- | --- |
| **A** | 只有 v6 | 3 | 可自由改，也可直接迁 v3 |
| **B** | v4 + v6 | 118 | 只准**加**，不准改形状；新能力去 v3 建 |
| **C** | 只有 v4 | 43 | **只修 bug，别的什么都不做**——等 v4 下线整块删 |

v4 真实代码在 `dashboard-v4/dashboard/src`（不是 `dashboard-v4/src`），
另有一批绕过请求封装的裸 URL（Upload 的 `action`、导出 `href`），grep 时别漏。

### v2 铁律

1. **不得改变响应形状、字段名、状态码。** 加字段安全，改/删不安全。
2. **修 bug 修在 Service 层**，v2 控制器只当适配层。同一处修复才能同时惠及 v3——
   这是 v2 改动成为 v3 资产的唯一途径。
3. **碰之前先录快照测试**：把该端点当前的真实响应录下来，改完形状没变才算修对。
4. **migration 只准加不准删**：加列必须 nullable 或带默认值；禁止删列、改列名、
   改类型、加 NOT NULL。
5. **不准删 v2 路由**，除非先枚举过 `BookTreeWithTags.tsx:44` 与
   `SearchVocabulary.tsx:102` 的 `/v2/${api}` 动态调用可能传入的资源名。

v3 端点可以自由改：v4 那两处 v3 引用（`ChatInput.tsx:127`、`agentApi.ts:14`）
均不构成生产依赖，已确认。

## 第 0 步：先 grep 出全集，列给用户确认

动手前必须先找全牵连点，不要边改边找。

```bash
# 某个 view 值涉及哪些地方
grep -rn "'user-in-chapter'" api-v13/app dashboard-v6/src

# 某个字段涉及哪些地方
grep -rn "source_type" api-v13/app api-v13/database dashboard-v6/src openapi/public
```

把命中的文件列成清单给用户过目，再开始改。

## 第 1 步：后端改逻辑（api-v13）

**加 view 值：** 在控制器的 `switch ($request->input('view'))` 里加 case。
注意多数控制器的 switch 没有 default 保护，加 case 时顺手确认新分支一定给
`$table` 赋了值。

**加字段：** migration → Model（`$fillable` / `$casts`）→ 对应的
`app/Http/Resources/XxxResource.php` 的 `toArray()`。

字段名要先核实，不要凭印象：

```bash
cd api-v13 && php artisan tinker --execute 'print_r(\Illuminate\Support\Facades\Schema::getColumnListing("channels"));'
```

## 第 2 步：同一次编辑里改 docblock（最容易漏）

规格是从注释生成的，注释没跟上，文档立刻就是错的。

- 加 view 值 → 在 `@queryParam view` 的 `Enum:` 列表里加上这个值，并在描述里
  写清它的口径与权限要求
- 加入参 → 补 `@bodyParam` 或 `@queryParam`
- 加出参 → 确保它进了 Resource 的 `toArray()`（生成器只认 Resource）

标签语法（位置固定）：`@标签 <参数名> <类型> [required] <中文描述>`，
描述里可写 `Enum: a,b,c` / `Default: x` / `Example: y`。
范例见 `api-v13/app/Http/Controllers/ChannelController.php` 的 `index`。

## 第 3 步：补测试（加 view 值时**必须**）

**新增 view 值 → 必须补 Pest 测试**，至少两条：一条验证返回预期数据，一条验证
权限不足时被拒。每个 view 是一条独立的业务路径，新写的查询与权限逻辑没人跑过，
最容易藏 bug。

模板抄 `api-v13/tests/Feature/ChannelUserEditListTest.php`，
`makeStudio` / `makeChannel` / `authHeader` 是现成 helper：

```php
it('lists the channels the user can edit', function () {
    $me = makeStudio('me');
    $mine = makeChannel($me, 'my channel');

    $rows = $this->getJson('/api/v2/channel?view=user-edit', authHeader($me))
        ->assertOk()->json('data.rows');

    expect(array_column($rows, 'uid'))->toContain($mine);
});

it('requires authentication', function () {
    $this->getJson('/api/v2/channel?view=user-edit')->assertJsonPath('ok', false);
});
```

**新增字段 → 不用改测试。** 项目里的断言都是 `toHaveKeys(...)` 这种「包含即可」
的写法，全仓库没有 `assertExactJson` / `assertJsonStructure`，多返回一个字段
不会打破任何测试。

需要改已有测试的只有：改了现有字段的名字或类型、改了权限规则、改了现有 view 的返回口径。

## 第 4 步：重新生成规格

```bash
cd openapi && php scripts/generate.php && npm run lint
```

lint 必须是 0 error。生成器会重建 `resources/auto/` 下的全部文件，不要手改那里面
的任何东西。

## 第 5 步：前端跟上（dashboard-v6）

先重新生成类型：

```bash
npx openapi-typescript openapi/public/assets/protocol/main.yaml -o dashboard-v6/src/api/schema.d.ts
```

**跑过 `generate.php` 就必须跑这条**，不用问用户。`schema.d.ts` 已提交进版本库，
它的改动是本次改动的一部分，要一并留在工作区。

- `src/api/<资源>.ts`：类型从 `schema.d.ts` 引用（`import type { paths } from "./schema"`，
  不写 `.d.ts` 后缀），**不要手写字段**；加调用函数。
  这里是前端类型与请求的汇集处。
- 再改用到它的 `src/features/`、`src/pages/`。
- 调用走 `src/request.ts`，路径硬编码完整的 `/api/v2/...`，**没有 baseURL**。
- 不要擅自安装依赖，不要引入 RTK Query / react-query 这类数据层。

## 第 6 步：验收

```bash
cd api-v13 && vendor/bin/pint --dirty --format agent && php artisan test --compact
cd dashboard-v6 && npm run build    # tsc 会把漏改的类型报出来
```

三条都要过：pint 无报错、测试全绿（`ExampleTest` 是历史遗留失败，可忽略）、
前端构建通过。

## 第 7 步：顺手还债（有边界）

这个库有六年欠债。改到哪还到哪，但**只还你这次真正改到的那个方法的债**——
同文件的其他方法一律不动，哪怕一眼看到问题；看到了就加 `// FIXME:` 留给下次。

对你改到的方法，做得到就做，做不到就 FIXME：

- 补中文 docblock 与参数标签（第 2 步已经在做）
- 返回值走 Resource，而不是 `return $this->ok($model)` 吐原始模型
- 给碰到的 Resource 补 `@return array{...}` 类型声明，类型要核实数据库列定义，
  不要照字段名想当然（`type`、`source_id` 这类常是 varchar 而非数字）
- `$this->error()` 参数位置写对：`error(string $message, mixed $data, int $status)`
- `switch` 补 `default`，避免 `$table` 未定义直接 500
- `$this->error(...)` 后漏掉的 `return` 补上

**功能改动和还债拆成两个 commit**（`feat`/`fix` 在前，`refactor` 在后），
review 时能分开看。

**不要**为了顺手重写整个控制器，**不要**改你没读懂的逻辑，**不要**在功能改动里
夹带大范围重构。拿不准就写 FIXME。

## 收尾

改完留在工作区，**不要自动 git commit**，等用户 review。

报告里必须用一句话列出这次顺带还了哪些债，例如
「顺带：`index` 补了 docblock、switch 加了 default 保护」。没列出来的清理动作
等于没经过 review。
