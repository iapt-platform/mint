<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexTipitakaWordParagraphRequest;
use App\Http\Resources\V3\TipitakaWordParagraphResource;
use App\Services\V3\TipitakaWordParagraphService;

/**
 * 按词形找三藏段落（只读、公开）。
 *
 * 取代 `GET|POST /v2/search-pali-wbw`（`SearchPaliWbwController@index` / `@store`）。
 * v2 那份在 dashboard-v4 下线前原样保留，不要改动。
 */
class TipitakaWordParagraphController extends Controller
{
    public function __construct(private readonly TipitakaWordParagraphService $paragraphs) {}

    /**
     * 按词形找段落
     *
     * 在逐词表里精确匹配词形（连音已拆开），返回命中的段落，按命中权重降序、同分按经文顺序。
     * 词形先用 `GET /v3/pali-word-forms/{word}` 展开——拿词根直接查通常是 0 条。
     * 每条带整段正文（只含 `<b>` 黑体与 `<mark>` 命中词的安全 HTML）、章节路径与各印本页码。
     *
     * @unauthenticated
     *
     * @queryParam words string required 词形，逗号分隔，最多 200 个。Example: vitakko,vitakkaṃ
     * @queryParam bold string 只看黑体（only，注释书里被解释的词）或排除黑体（exclude）；不给则不限。Enum: only,exclude
     * @queryParam book_id string 只看这些书，逗号分隔的 book_id（见 `/aggregate`）。Example: 75,79
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页条数，上限 100。Default: 20
     */
    public function index(IndexTipitakaWordParagraphRequest $request)
    {
        return TipitakaWordParagraphResource::collection(
            $this->paragraphs->paragraphs($request->criteria(), (int) $request->validated('per_page', 20)),
        );
    }
}
