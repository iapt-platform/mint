<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexGlossaryRequest;
use App\Http\Resources\V3\GlossaryEntryResource;
use App\Services\V3\GlossaryService;

/**
 * 术语词汇表：语法术语表 / 社区译名表（只读）。
 *
 * 取代 `GET /v2/term-vocabulary?view=grammar|community`（`TermVocabularyController@index`）。
 * v2 那份在 dashboard-v4 下线前原样保留，不要改动。
 */
class GlossaryController extends Controller
{
    public function __construct(private readonly GlossaryService $glossaries) {}

    /**
     * 术语词汇表
     *
     * 整表返回、不分页（客户端启动时拉一次，本地查词）。`meta.lang` 是实际用到的语言：
     * 该语言没有这张表时回退到英文。条目的 `id`（术语 guid）只有 grammar 表给，community 表不给（省流量）。
     *
     * @unauthenticated
     *
     * @urlParam kind string required 哪张表：grammar 语法术语，community 社区译名。Enum: grammar,community
     *
     * @queryParam lang string 语言，如 zh-Hans。缺省用界面语言（Accept-Language 协商的结果）
     *
     * @meta kind string 哪张表
     * @meta lang string 实际用到的语言（没有该语言的表时是 en）
     * @meta total integer 条数
     */
    public function __invoke(IndexGlossaryRequest $request, string $kind)
    {
        $result = $this->glossaries->entries($kind, $request->validated('lang') ?? app()->getLocale());

        return GlossaryEntryResource::collection($result['items'])->additional(['meta' => [
            'kind' => $kind,
            'lang' => $result['lang'],
            'total' => $result['items']->count(),
        ]]);
    }
}
