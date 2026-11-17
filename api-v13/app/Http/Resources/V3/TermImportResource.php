<?php

namespace App\Http\Resources\V3;

use App\Services\V3\TermExchangeService;
use Illuminate\Http\Request;

/**
 * 一次术语导入的结果（{@see TermExchangeService}）。部分成功：出错的行跳过，`errors` 逐行给出原因（`row` 是表格行号）。
 * `reindexed` 为 false 表示改动较多，搜索索引等每周的全量重建补上。
 */
class TermImportResource extends BaseResource
{
    /**
     * @return array{
     *     created: int,
     *     updated: int,
     *     unchanged: int,
     *     reindexed: bool,
     *     errors: list<array{row: int, message: string}>
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'created' => $this->resource['created'],
            'updated' => $this->resource['updated'],
            'unchanged' => $this->resource['unchanged'],
            'reindexed' => $this->resource['reindexed'],
            'errors' => $this->resource['errors'],
        ];
    }
}
