<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\AggregateTipitakaWordParagraphRequest;
use App\Http\Resources\V3\TipitakaWordBookResource;
use App\Services\V3\TipitakaWordParagraphService;

/**
 * 按词形检索的出处分布（只读、公开）。
 *
 * 取代 `GET /v2/search-pali-wbw-books`（`SearchPaliWbwController@book_list`）。
 * v2 那份在 dashboard-v4 下线前原样保留，不要改动。
 */
class TipitakaWordParagraphAggregateController extends Controller
{
    public function __construct(private readonly TipitakaWordParagraphService $paragraphs) {}

    /**
     * 按书统计命中
     *
     * 与 `GET /v3/tipitaka-word-paragraphs` 同样的检索条件，按书汇总：每本书命中多少段、多少词次，
     * 按段落数降序，不分页。v2 只给词次；同一段里出现多次只算一段，所以两者不相等。
     *
     * @unauthenticated
     *
     * @queryParam words string required 词形，逗号分隔，最多 200 个。Example: vitakko,vitakkaṃ
     * @queryParam bold string 只看黑体（only）或排除黑体（exclude）；不给则不限。Enum: only,exclude
     *
     * @meta total integer 书的数目
     * @meta paragraphs integer 命中段落总数
     * @meta occurrences integer 词次总数
     */
    public function __invoke(AggregateTipitakaWordParagraphRequest $request)
    {
        $result = $this->paragraphs->books($request->criteria());

        return TipitakaWordBookResource::collection($result['items'])->additional(['meta' => [
            'total' => count($result['items']),
            'paragraphs' => $result['paragraphs'],
            'occurrences' => $result['occurrences'],
        ]]);
    }
}
