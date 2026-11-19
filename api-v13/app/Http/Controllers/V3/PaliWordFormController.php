<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Resources\V3\PaliWordFormResource;
use App\Services\V3\PaliWordFormService;

/**
 * 巴利词形展开（只读、公开）。
 *
 * 取代 `GET /v2/case/{word}`（`CaseController@show`）。v2 那份在 dashboard-v4 下线前原样保留，不要改动。
 */
class PaliWordFormController extends Controller
{
    public function __construct(private readonly PaliWordFormService $forms) {}

    /**
     * 词形展开
     *
     * 把一个巴利词还原成可能的词根，列出每个词根在三藏里实际出现过的词形（含 iti / api / ca / hi 连音），
     * 带出现次数与黑体次数。第一组是输入的词本身，其后是推导出的词根；没有任何词形出现过的词根不列。
     * 每组内按出现次数降序。词不区分大小写。
     *
     * @unauthenticated
     *
     * @urlParam word string required 巴利词，如 vitakka
     *
     * @meta total integer 组数
     */
    public function __invoke(string $word)
    {
        $groups = $this->forms->groups($word);

        return PaliWordFormResource::collection($groups)->additional(['meta' => ['total' => count($groups)]]);
    }
}
