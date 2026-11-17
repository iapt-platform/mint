<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\ImportTermsRequest;
use App\Http\Resources\V3\TermImportResource;
use App\Models\Channel;
use App\Services\V3\TermExchangeService;

/**
 * 向 channel 导入术语（xlsx）。
 *
 * 取代 `GET /v2/terms-import?view=channel`（`TermExportController@import`）。v2 原样保留。
 */
class ChannelTermImportController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly TermExchangeService $exchange) {}

    /**
     * 向 channel 导入术语
     *
     * 上传 xlsx（第一行是列名，至少有 word、meaning；可以直接用导出的表格）。所有行都落在这个 channel，
     * 语言跟随 channel。有 id 的行改那一条（word / tag 不能改；带 etag 列时与当前不一致就跳过），
     * 没有 id 的按 word + tag 找，有就改、没有就建。**部分成功**：出错的行跳过，`errors` 逐行给出原因。
     * 能写这个 channel 的人（含团队 editor）才能导入；AI 模型不能导入。最多 5000 行。
     *
     * @urlParam channel string required channel uid
     *
     * @bodyParam file file required xlsx 表格，不超过 5MB
     *
     * @responseStatus 403 不能写这个 channel
     */
    public function __invoke(ImportTermsRequest $request, Channel $channel)
    {
        return TermImportResource::make($this->exchange->importToChannel(
            $channel,
            $this->currentUser($request)['user_uid'],
            $request->file('file'),
        ));
    }
}
