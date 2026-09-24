<?php

namespace App\Http\Resources;

use App\Http\Controllers\BookTitleController;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookTitleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * book / paragraph / sn / title 是 book_titles 表列，toc / tags / related_name
     * 由 {@see BookTitleController::index} 在取数时附加，
     * language 来自目录数据（接口可能不返回）。
     *
     * @param  Request  $request
     * @return array{
     *     book: int,
     *     paragraph: int,
     *     sn: int,
     *     title: string,
     *     toc: string|null,
     *     tags: string[],
     *     related_name: string|null,
     *     language?: string|null
     * }
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
