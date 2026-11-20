<?php

namespace App\Http\Resources\V3;

use App\Services\V3\MarkupPreviewService;
use Illuminate\Http\Request;

/**
 * 源码预览结果（{@see MarkupPreviewService}）：`body` 是新渲染器的节点树，与术语、批注的 `body` 同形。
 */
class MarkupPreviewResource extends BaseResource
{
    /**
     * @return array{body: list<MarkupNode>}
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
