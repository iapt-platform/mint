<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * v3 资源的基类。所有 `*V3Resource` 都继承它。
 *
 * 作用是让 `XxxV3Resource::collection()` 产出 {@see V3ResourceCollection}，
 * 从而裁掉分页里的绝对 URL。除此之外与普通 JsonResource 完全一致——
 * 响应形状由框架决定：
 *
 *     单个：{data: {...}}
 *     列表：{data: [...], meta: {...}}
 *
 * 控制器直接 `return XxxV3Resource::make($model)` 或
 * `return XxxV3Resource::collection($query->paginate($n))`，不需要任何 helper。
 *
 * 载荷是普通数组（不是 Eloquent 模型）时可以直接用本类：
 *
 *     return V3Resource::collection($items)->additional(['meta' => [...]]);
 */
class V3Resource extends JsonResource
{
    /**
     * @param  mixed  $resource
     */
    protected static function newCollection($resource): V3ResourceCollection
    {
        return new V3ResourceCollection($resource, static::class);
    }
}
