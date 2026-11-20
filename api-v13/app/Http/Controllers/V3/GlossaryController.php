<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexGlossaryRequest;
use App\Http\Resources\V3\GlossaryEntryResource;
use App\Services\V3\GlossaryService;
use Illuminate\Http\Response;

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
     * community 表带 `ETag`（`Cache-Control: public, no-cache`）：浏览器下次自动带 `If-None-Match`，
     * 表没变就回 304、不带正文。ETag 在社区术语任务每次跑完时更换。grammar 表暂不带。
     * @meta total integer 条数
     */
    public function __invoke(IndexGlossaryRequest $request, string $kind)
    {
        $lang = $request->validated('lang') ?? app()->getLocale();
        $etag = $this->glossaries->etag($kind, $lang);
        $withEtag = fn ($response) => $etag === null ? $response : $response
            ->setEtag($etag)
            ->header('Cache-Control', 'public, no-cache');

        // 表没变：在查整表之前就回 304（isNotModified 按 RFC 9110 弱比较，nginx 压缩后的 W/"…" 也认）
        if ($etag !== null) {
            $probe = $withEtag(new Response);
            if ($probe->isNotModified($request)) {
                return $probe;
            }
        }

        $result = $this->glossaries->entries($kind, $lang);

        return $withEtag(GlossaryEntryResource::collection($result['items'])->additional(['meta' => [
            'kind' => $kind,
            'lang' => $result['lang'],
            'total' => $result['items']->count(),
        ]])->response());
    }
}
