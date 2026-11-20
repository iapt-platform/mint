<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\StoreMarkupPreviewRequest;
use App\Http\Resources\V3\MarkupPreviewResource;
use App\Services\V3\MarkupPreviewService;

/**
 * 源码预览（模板生成器、编辑器的「预览」）。
 *
 * 新能力，没有一一对应的 v2 端点：v2 只有绑定在某篇文章上、走旧渲染器的 `PUT /v2/article-preview/{id}`
 * （`ArticleController@preview`），那份在 dashboard-v4 下线前原样保留，不要改动。
 */
class MarkupPreviewController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly MarkupPreviewService $previews) {}

    /**
     * 预览一段源码
     *
     * 把还没保存的 markdown 渲染成节点树（与术语、批注的 `body` 同形），不写库，所以是 200 而不是 201。
     * 口径同阅读页的读模式：术语按第一个 channel 查，句子模板缺省读这些 channel 的译文。
     * 给了 `channels` 的，每个都要有读权限：不存在 404，看不见 403。
     *
     * @bodyParam content string required 源码，最多 20000 字
     * @bodyParam content_type string 方言。Enum: markdown, markdown-e2。Default: markdown
     * @bodyParam channels string[] 内容所属的 channel uid，最多 10 个
     * @bodyParam lang string 没给 channel 时用的语言，如 zh-Hans。缺省用界面语言（Accept-Language 协商的结果）
     *
     * @responseStatus 403 给的 channel 没有读权限
     * @responseStatus 404 给的 channel 不存在
     */
    public function __invoke(StoreMarkupPreviewRequest $request)
    {
        return MarkupPreviewResource::make($this->previews->preview(
            $request->validated(),
            $this->currentUser($request)['user_uid'],
            app()->getLocale(),
        ));
    }
}
