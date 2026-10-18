<?php

namespace App\Http\Resources\V3;

use App\Services\V3\DiscussionService;
use Illuminate\Http\Request;

/**
 * 一个对象上的讨论角标：未关闭的评论线程数，以及其中当前用户发起的数目。
 *
 * 载荷由 {@see DiscussionService::batchCount()} 组好，本类只做形状声明。
 */
class DiscussionCountResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     target_type: string,
     *     target_id: string,
     *     total: int,
     *     mine: int
     * }
     */
    public function toArray($request): array
    {
        return $this->resource;
    }
}
