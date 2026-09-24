<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

/**
 * 阅读接口逐段返回的正文条目，即 `/v3/tipitaka-reading/{channel}` 的 `data[]` 元素。
 *
 * 结果可以跨书，所以每条都带 `book`——只给 `para` 在跨书时不唯一。
 *
 * 载荷是普通数组而非 Eloquent 模型，`toArray` 直接透传；`@return` 声明的是
 * `view=display` 口径下的形状（para + display）。
 */
class ReadParagraphResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     para: int,
     *     display: string
     * }
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
