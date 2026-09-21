# MINT API 规格

`public/assets/protocol/` 下的 OpenAPI 3.0.3 规格 **全部由脚本从 api-v13 的源码生成**，
不要手改生成结果。

## 重新生成

后端路由或控制器改动之后：

```bash
php scripts/generate.php      # 或 npm run generate
npm run lint                  # redocly 校验，应为 0 error
```

脚本做的事：

1. 读 `php artisan route:list --json`（在 `../api-v13` 下执行）拿到全部路由。
2. 用 nikic/php-parser 解析每个控制器方法，提取
   - 文档注释 → summary / description（Laravel 脚手架英文样板会被丢弃）
   - 文档注释里的 `@queryParam` / `@bodyParam` / `@urlParam` / `@deprecated` 标签（见下）
   - `$request->input()/query()/has()/boolean()/integer()` → 查询参数与默认值
   - `switch ($request->input('view'))` 的 case → 参数枚举
   - `validate([...])` / `Validator::make()` → 请求体字段、必填与类型
   - 返回的 `App\Http\Resources\*Resource::toArray()` 的键 → 响应 schema
3. 写出 `main.yaml` + 每个 path 一个 `resources/auto/<slug>.yaml`。

## 在控制器里写参数说明（首选做法）

与其在 `overrides/` 里手写 YAML，不如把说明写在控制器的文档注释里——离代码最近，改接口时不容易忘。
语法是固定位置的：`@标签 <参数名> <类型> [required] <描述>`。

```php
/**
 * 列出 channel（译文集）
 *
 * 按 view 指定的口径返回 channel 列表，支持关键字搜索、分页、排序。
 *
 * @queryParam view string required 查询口径。Enum: public,studio,system
 * @queryParam view2 string 二级口径：my=我的，其余值=协作的。Default: my
 * @queryParam updated_at string 只返回该时间之后更新的记录，用于离线包增量同步。
 *             Example: 2023-09-18T05:39:51.000000Z
 * @bodyParam name string required channel 名称，在同一 studio 内唯一
 * @urlParam channel string required channel uid
 */
```

- `@queryParam` 出现在查询串，`@bodyParam` 出现在请求体，`@urlParam` 是路径参数。
- 类型写 `string` / `integer` / `number` / `boolean` / `array` / `object`。
- 描述里的 `Enum: a,b,c`、`Default: x`、`Example: y` 会被解析成对应的 schema 字段。
- 描述换行时续行要缩进，会自动并入上一个参数。
- 标注过的参数覆盖源码推断的结果；没标注的参数仍然按源码推断输出，不会丢。
- `@deprecated` 会把该 operation 标成废弃。

## 目录

| 路径                                               | 说明                                             |
| -------------------------------------------------- | ------------------------------------------------ |
| `public/assets/protocol/main.yaml`                 | 入口，servers / components / paths（全部`$ref`） |
| `public/assets/protocol/resources/auto/`           | 自动生成，每次重跑会被清空重建，**勿手改**       |
| `public/assets/protocol/overrides/`                | 手写补充，按同名文件深度合并到自动结果之上       |
| `public/assets/protocol/resources/list_query.yaml` | 列表接口通用分页/排序参数                        |
| `scripts/generate.php`                             | 生成器                                           |

`overrides/` 留给那些不适合写进 PHP 注释的东西（长篇示例响应、跨接口的说明）。
注意合并规则是：同名键以 overrides 为准，**数组整体替换**——所以在 overrides 里写
`parameters` 会覆盖掉自动生成的全部参数，一般不要这么做，优先用上面的文档注释标签。

## 约定

- 所有接口挂在 `/api` 下，业务路由带 `/v2`、`/v3` 前缀；`/ops` 为运维接口，需
  `Authorization: Bearer <APP_OPS_TOKEN>`。
- 响应统一封装 `{ok, message, data}`；业务失败时 HTTP 仍可能是 200，以 `ok` 为准。

## 本地预览

```bash
npm install
npm run dev
```

## 文档

- [OpenAPI Guide](https://swagger.io/docs/specification/basic-structure/)
