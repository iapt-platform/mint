<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\AggregateTipitakaRelatedParagraphBookNameRequest;
use App\Http\Requests\V3\AggregateTipitakaRelatedParagraphRequest;
use App\Http\Resources\V3\RelatedParagraphBookNameResource;
use App\Http\Resources\V3\RelatedParagraphCsParaResource;
use App\Services\V3\TipitakaRelatedParagraphService;

/**
 * 段落关联关系的聚合端点。
 *
 *   - `GET /v3/tipitaka-related-paragraphs/aggregate`          → 所有 SC 缩写
 *   - `GET /v3/tipitaka-related-paragraphs/aggregate/{book_name}` → 该缩写下的 cs_para
 *
 * 聚合逻辑在 {@see TipitakaRelatedParagraphService}，这里用 Resource 包成
 * 标准的 {data, meta} 信封（分页 meta 由 BaseResourceCollection 裁掉绝对 URL）。
 */
class TipitakaRelatedParagraphAggregateController extends Controller
{
    public function __construct(private readonly TipitakaRelatedParagraphService $related) {}

    /**
     * 列出所有 SC 缩写
     *
     * data 是去空、去重的 SC 缩写，每条形如 {book_name}，分页由 Laravel paginator 提供。
     *
     * @unauthenticated
     *
     * @queryParam book integer 典籍文件号过滤。
     * @queryParam book_id integer 真书号过滤。
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 200。Default: 15
     */
    public function index(AggregateTipitakaRelatedParagraphRequest $request)
    {
        return RelatedParagraphBookNameResource::collection(
            $this->related->bookNames($request->validated())
        );
    }

    /**
     * 列出某个 SC 缩写下的 cs_para
     *
     * data 是去重、>0 的 cs_para（升序），每条形如 {cs_para}，分页由 Laravel paginator 提供。
     *
     * @unauthenticated
     *
     * @urlParam book_name string required SC 风格书名缩写（如 dn1、an2）。Example: dn1
     *
     * @queryParam book integer 典籍文件号过滤。
     * @queryParam book_id integer 真书号过滤。
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 200。Default: 15
     */
    public function show(AggregateTipitakaRelatedParagraphBookNameRequest $request, string $bookName)
    {
        return RelatedParagraphCsParaResource::collection(
            $this->related->csParas($bookName, $request->validated())
        );
    }
}
