<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

/**
 * 聚合端点 `GET /v3/tipitaka-related-paragraphs/aggregate/{book_name}` 的一行：一个 cs_para。
 */
class RelatedParagraphCsParaResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{cs_para: string}
     */
    public function toArray($request): array
    {
        return [
            'cs_para' => (string) $this->cs_para,
        ];
    }
}
