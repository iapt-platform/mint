<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexTipitakaRelatedParagraphRequest;
use App\Http\Resources\V3\RelatedParagraphResource;
use App\Services\V3\TipitakaRelatedParagraphService;

/**
 * 段落关联关系（mūla / aṭṭhakathā / ṭīkā 的注释对应）。
 *
 * 取代 `GET /v2/related-paragraph`。按 book_id 分组一本一条，批量联表消除 N+1；
 * 新增 `book_name`/`cs_para` 直接过滤器。
 */
class TipitakaRelatedParagraphController extends Controller
{
    public function __construct(private readonly TipitakaRelatedParagraphService $related) {}

    /**
     * 列出一个段落的关联段落
     *
     * 按 book_id 分组，一本书一条，附带书名、标签、目录路径与该书内的段落列表。
     * 没有关联段落时返回空集（这是正常结果，不是错误）。
     *
     * @unauthenticated
     *
     * @queryParam book integer 典籍文件号（1-217）。与 para 一起用，prohibits 下面那组。Example: 1
     * @queryParam para integer 段落号。与 book 一起用。Example: 2
     * @queryParam book_name string SC 风格书名缩写（如 dn1、an2）。与 cs_para 一起用，prohibits 上面那组。Example: dn1
     * @queryParam cs_para integer 书内段落号。与 book_name 一起用。Example: 4
     * @queryParam book_id integer 真书号（book_titles.sn），可选收窄。
     *
     * @meta count integer 命中的书籍条数
     */
    public function index(IndexTipitakaRelatedParagraphRequest $request)
    {
        $rows = $this->related->related($request->validated());

        return RelatedParagraphResource::collection($rows)
            ->additional(['meta' => ['count' => count($rows)]]);
    }
}
