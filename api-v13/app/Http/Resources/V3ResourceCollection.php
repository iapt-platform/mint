<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;

/**
 * v3 列表响应的集合类。
 *
 * 只做一件事：裁掉 Laravel 默认分页信息里的绝对 URL。
 * `links` 与 `meta.path` 都由 APP_URL 拼出，反向代理下容易拼错，前端也用不到。
 *
 * 其余一概交给框架：`meta` 里的 current_page / per_page / total / last_page /
 * from / to 都是 paginator 自己算的。
 */
class V3ResourceCollection extends AnonymousResourceCollection
{
    /**
     * @param  array{links: array, meta: array}  $default
     * @return array{meta: array}
     */
    public function paginationInformation($request, array $paginated, array $default): array
    {
        unset($default['links']);
        $default['meta'] = Arr::except($default['meta'], ['path', 'links']);

        return $default;
    }
}
