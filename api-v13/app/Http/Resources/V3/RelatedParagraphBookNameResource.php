<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

/**
 * 聚合端点 `GET /v3/tipitaka-related-paragraphs/aggregate` 的一行：一个 SC 缩写。
 */
class RelatedParagraphBookNameResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{book_name: string}
     */
    public function toArray($request): array
    {
        return [
            'book_name' => $this->book_name,
        ];
    }
}
