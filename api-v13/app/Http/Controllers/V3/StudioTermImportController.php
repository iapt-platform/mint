<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\ImportTermsRequest;
use App\Http\Resources\V3\TermImportResource;
use App\Services\V3\TermExchangeService;

/**
 * 向 studio 导入术语（xlsx）。
 *
 * 取代 `GET /v2/terms-import?view=studio`（`TermExportController@import`）。v2 原样保留。
 */
class StudioTermImportController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly TermExchangeService $exchange) {}

    /**
     * 向 studio 导入术语
     *
     * 上传 xlsx。`channel_id` 列有值的行落在那个 channel（必须属于这个 studio、你能写），没有的是 studio 通用术语
     * （要填 `language`；新建要团队 owner / manager，修改团队 editor 也行）。其余规则同向 channel 导入：
     * 部分成功、word / tag 不能改、带 etag 列时校验。AI 模型不能导入。最多 5000 行。
     *
     * @urlParam studio string required 用户或团队的 uuid
     *
     * @bodyParam file file required xlsx 表格，不超过 5MB
     *
     * @responseStatus 403 不是本人，也不是团队成员
     */
    public function __invoke(ImportTermsRequest $request, string $studio)
    {
        return TermImportResource::make($this->exchange->importToStudio(
            $studio,
            $this->currentUser($request)['user_uid'],
            $request->file('file'),
        ));
    }
}
