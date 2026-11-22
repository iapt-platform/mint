<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\StoreTipitakaExportRequest;
use App\Http\Resources\V3\TipitakaExportResource;
use App\Services\V3\TipitakaExportService;
use Illuminate\Http\Request;

/**
 * 三藏下载：把一个 channel 的一部作品、一个分类或整个 channel 导出成文件。
 *
 * 新能力，没有对应的 v2 端点（v2 的 `export` / `MqExportPaliChapter` 是按章节导出的另一套，原样保留）。
 * library 的作品页 / 分类页下载按钮与 wikipali-mobile 都走这里。
 */
class TipitakaExportController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly TipitakaExportService $exports) {}

    /**
     * 发起下载
     *
     * 同一份文件（同一 channel、范围、格式，且译文没改过）已经生成好就直接返回 `status = ready`
     * 与 `download_url`（200）；否则排队生成，返回 `queued`（202，带 `Retry-After`），
     * 之后轮询 `GET /v3/tipitaka-exports/{id}`。同一份正在生成时不会重复排队。
     *
     * 格式：`html`、`markdown`、`epub`、`docx`、`offline`（mobile 离线包，NDJSON，下载时 gzip 传输编码，
     * 每行一段，`html` 与 `tipitaka-reading` 的 display 相同；只能按作品，首行头里有作品的段落区间
     * `para` / `end`，客户端按它判断是否覆盖了自己要的范围）。单部作品的 epub / docx
     * 直接给文件，其余是 zip。私有 channel 只有能读它的人能下载。范围太大（超过全三藏的量）422。
     *
     * @unauthenticated
     *
     * @bodyParam channel string required channel uid。Example: a19eaf75-c63f-4b84-8125-1bce18311e23
     * @bodyParam work integer 作品（library 作品页 URL 里的 sn）。作品、category 只能给一个，都不给 = 整个 channel。Example: 275
     * @bodyParam book integer 用坐标指定作品：book + para 所在的那部作品（mobile 用，它不知道 sn）。与 para 同时给。
     * @bodyParam para integer 与 book 同时给，作品里的任一段落号（通常是作品起始段）。
     * @bodyParam category integer 分类 id（library 分类页 URL 里的 id），含后代分类。
     * @bodyParam format string required 文件格式。Enum: html, markdown, epub, docx, offline
     *
     * @responseStatus 202 已排队生成，响应体同 200
     */
    public function store(StoreTipitakaExportRequest $request)
    {
        $export = $this->exports->request($request->validated(), $this->currentUserUid($request));
        $response = TipitakaExportResource::make($export)->response();

        return $export['status'] === 'ready' ? $response : $response->setStatusCode(202)->header('Retry-After', '2');
    }

    /**
     * 查看下载状态
     *
     * `status`：`queued` 排队中 / `running` 生成中（`progress` 0–1，`message` 说明当前在哪部作品）/
     * `ready` 可下载（带 `download_url`）/ `failed` 失败（重新 POST 即可重试）。
     * 不存在或已过期 404。
     *
     * @unauthenticated
     *
     * @urlParam export string required 导出 id（POST 返回的 id）。
     */
    public function show(Request $request, string $export)
    {
        return TipitakaExportResource::make($this->exports->show($export, $this->currentUserUid($request)));
    }
}
