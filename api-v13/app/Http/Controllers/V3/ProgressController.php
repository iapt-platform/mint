<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexProgressRequest;
use App\Http\Resources\V3\ProgressResource;
use App\Services\V3\ProgressService;

class ProgressController extends Controller
{
    public function __construct(private readonly ProgressService $progress) {}

    /**
     * 列出章节翻译进度
     *
     * 按 channel 查询各章节的翻译完成度。分页与 meta 全由框架算。
     *
     * 原来有个 `view` 参数，但它只有 `channel` 一个合法值、传别的直接 422——
     * 那不是业务路径开关而是噪音，已去掉（硬规范第 1 条）。
     *
     * @unauthenticated
     *
     * @queryParam channels string required channel uid 列表，**下划线分隔**（不是逗号）
     * @queryParam level integer 只返回该层级及以上的章节，需联查 pali_texts
     * @queryParam lang string 按译文语言过滤。Example: zh-Hans
     * @queryParam book integer 按典籍 id 过滤
     * @queryParam order string 排序字段，白名单内。Enum: book,para,lang,progress,title,last_chapter_completed_at,completed_at,updated_at Default: completed_at
     * @queryParam dir string 排序方向。Enum: asc,desc Default: desc
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 200。Default: 10
     */
    public function index(IndexProgressRequest $request)
    {
        return ProgressResource::collection(
            $this->progress->chapters($request->validated())
        );
    }
}
