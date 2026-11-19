<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexTipitakaChapterRequest;
use App\Http\Resources\V3\TipitakaChapterResource;
use App\Services\V3\TipitakaChapterService;

/**
 * 三藏章节标题：按名字找章节，或列出一本书的全部标题。
 *
 * 取代 `GET /v2/search-title-index`（`SearchTitleController@index`）。v2 那份只回标题文字、
 * 不带坐标，在 dashboard-v4 下线前原样保留，不要改动。
 */
class TipitakaChapterController extends Controller
{
    public function __construct(private readonly TipitakaChapterService $chapters) {}

    /**
     * 找章节
     *
     * 两种用法，可以叠加：
     *
     * - **按名字找**（给 `q`）：巴利名不分大小写、变音符号可有可无（`mahaparinibbana`），可以只写
     *   名字的一部分，拼错一两个字母也能找到；也能用公开译本里的章节译名（如「大般涅槃经」）。
     *   按匹配度排序，带 `score`（相等 1、开头相同 0.95、包含 0.9，拼错的按相似度最高 0.85）；同一档里根本典在前，再按与整个标题的接近度、大章在前。
     * - **列出一本书的标题**（给 `book`，不给 `q`）：按经文顺序。再给 `para` 只列那一章下面的；
     *   `max_level` 限制层级深浅。
     *
     * `layer` 是这本书属于根本典（mula）、义注（atthakatha）、复注（tika）还是其他（anna）；
     * `paragraphs` / `chars` 是这一章的段数与字符数；`parent` / `prev` / `next` 是同一本书里的段号，
     * 没有为 null；`path` 是从丛书到上一级的路径（不含本章）。
     *
     * @unauthenticated
     *
     * @queryParam q string 章节名：巴利名或译名，不给 book 时必填。Example: mahaparinibbana
     * @queryParam book integer 书号，不给 q 时必填。Example: 94
     * @queryParam para integer 只列 book 里这一章下面的标题（含它自己），要和 book 一起给。Example: 287
     * @queryParam layer string 只要某几层，逗号分隔。Enum: mula, atthakatha, tika, anna
     * @queryParam max_level integer 只要层级不深于它的标题（1 是书名）。Example: 3
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 1000。Default: 50
     */
    public function __invoke(IndexTipitakaChapterRequest $request)
    {
        return TipitakaChapterResource::collection($this->chapters->search(
            $request->safe()->only(['q', 'book', 'para', 'layer', 'max_level']),
            $request->integer('page', 1),
            $request->integer('per_page', 50),
        ));
    }
}
