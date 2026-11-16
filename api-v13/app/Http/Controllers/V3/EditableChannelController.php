<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexEditableChannelRequest;
use App\Http\Resources\V3\ChannelResource;
use App\Services\V3\ChannelService;

/**
 * 我能写内容的 channel，跨 studio：本人的、本人是团队 editor 及以上的团队名下的、共享权限 ≥20 的。
 *
 * 取代 `GET /v2/channel?view=user-edit`（`ChannelController@index`）。v2 的搜索一直是坏的（前端传 `key`、
 * 后端读 `search`），这里用 `search`。写入前挑 channel 用（编辑器、wikipali-plugins）。
 * v2 那份在 dashboard-v4 下线前原样保留，不要改动。
 */
class EditableChannelController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly ChannelService $channels) {}

    /**
     * 列出我能写的 channel
     *
     * @queryParam search string 按名字模糊匹配
     * @queryParam type string channel 类型。Enum: translation,nissaya,original,wbw,commentary,similar
     * @queryParam lang string 语言
     * @queryParam book integer 章节所在的书（与 para 同时给）
     * @queryParam para integer 章节起始段（与 book 同时给）
     * @queryParam order string 排序字段。Enum: updated_at,created_at,name,progress Default: updated_at
     * @queryParam dir string 排序方向。Enum: asc,desc Default: desc
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 200。Default: 20
     */
    public function __invoke(IndexEditableChannelRequest $request)
    {
        return ChannelResource::collection($this->channels->listWritable(
            $this->currentUser($request)['user_uid'],
            $request->safe()->except(['page', 'per_page']),
            $request->integer('per_page', 20),
        ));
    }
}
