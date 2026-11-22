<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

/**
 * 一次三藏下载的状态（`/v3/tipitaka-exports`）。载荷是 TipitakaExportService::present() 的数组。
 *
 * `id` 是内容指纹：同一 channel、范围、格式，译文没改过就是同一个 id——客户端（mobile 离线包）
 * 可以拿它判断已下载的内容是否过时。`download_url` 是签名的相对地址，`status = ready` 时才有，
 * 到 `expires_at` 失效。
 */
class TipitakaExportResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     id: string,
     *     status: string,
     *     format: string,
     *     progress: float,
     *     message: string|null,
     *     title: string,
     *     works: int,
     *     filename: string,
     *     content_type: string,
     *     size: int|null,
     *     ready_at: string|null,
     *     expires_at: string|null,
     *     download_url: string|null
     * }
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
