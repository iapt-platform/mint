<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexTipitakaPageNumberRequest;
use App\Http\Resources\V3\TipitakaPageNumberResource;
use App\Services\V3\TipitakaPageNumberService;

/**
 * 按印本页码找三藏段落（只读、公开）。
 *
 * 取代 `GET /v2/search-page-number/{number}`（`SearchPageNumberController@show`），以及 v2
 * `search?view=page` 的「卷.页」查法。v2 那份在 dashboard-v4 下线前原样保留，不要改动。
 */
class TipitakaPageNumberController extends Controller
{
    public function __construct(private readonly TipitakaPageNumberService $pages) {}

    /**
     * 按页码找段落
     *
     * 一个页码在 PTS、缅甸版、VRI、泰国版、其他里各指一处或多处，另有 CS 版印的段落号（`edition=CS`）。
     * 返回全部命中（不分页，通常几百条），按版本、书、卷、段排序。每条带书名、层级与该版本的书名。
     * 卷号未知的记录 `volume` 为 null；给了 `volume` 就只要这一卷（CS 段落号不分卷，不返回）。
     *
     * @unauthenticated
     *
     * @urlParam page integer required 页码（或 CS 段落号）。Example: 100
     *
     * @queryParam edition string 只要这个版本。Enum: PTS,My,VRI,Thai,Other,CS
     * @queryParam volume integer 只要这一卷。Example: 2
     *
     * @meta total integer 命中数
     */
    public function __invoke(IndexTipitakaPageNumberRequest $request, int $page)
    {
        $rows = $this->pages->find($page, $request->filters());

        return TipitakaPageNumberResource::collection($rows)->additional(['meta' => ['total' => count($rows)]]);
    }
}
