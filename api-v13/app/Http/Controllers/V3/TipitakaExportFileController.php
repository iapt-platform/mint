<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Services\V3\TipitakaExportDownload;
use Illuminate\Http\Request;

/**
 * 三藏下载的文件本身。只认签名链接（`GET /v3/tipitaka-exports/{id}` 的 `download_url`），
 * 不需要 token：浏览器直接跳转、mobile 交给系统下载器都能用。
 */
class TipitakaExportFileController extends Controller
{
    public function __construct(private readonly TipitakaExportDownload $download) {}

    /**
     * 下载文件
     *
     * 文件存在 S3 时 302 跳到 S3 的临时链接（1 小时有效，客户端跟随跳转即可）；本地开发直接返回文件。
     * 两种情况都支持 `Range`（206，断点续传）与 `If-None-Match`（304）。
     * 离线包（offline）是 NDJSON，带 `Content-Encoding: gzip`，HTTP 客户端会自动解压。
     * 签名无效或过期 403，文件已过期 404。
     *
     * @unauthenticated
     *
     * @urlParam export string required 导出 id。
     *
     * @queryParam expires integer required 签名的过期时间（download_url 里自带）。
     * @queryParam signature string required 签名（download_url 里自带）。
     *
     * @responseFile application/zip application/epub+zip application/vnd.openxmlformats-officedocument.wordprocessingml.document application/x-ndjson
     *
     * @responseStatus 302 文件在 S3：跳到临时下载链接
     * @responseStatus 206 Range 请求的部分内容
     * @responseStatus 304 If-None-Match 命中，本地文件就是最新的
     * @responseStatus 403 签名无效或已过期
     * @responseStatus 404 文件已过期
     * @responseStatus 416 Range 超出文件大小
     */
    public function __invoke(Request $request, string $export)
    {
        return $this->download->respond($export, $request->header('Range'), $request->header('If-None-Match'));
    }
}
