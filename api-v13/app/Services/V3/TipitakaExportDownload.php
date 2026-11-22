<?php

namespace App\Services\V3;

use App\Facades\RedisCache;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 把 Redis 里的导出文件流给客户端：按 1MB 分块 GETRANGE 读，不把整个文件读进 PHP 内存；
 * 支持单段 `Range`（mobile 断点续传）与 `If-None-Match`（id 即内容指纹，可当强 ETag）。
 */
class TipitakaExportDownload
{
    private const CHUNK = 1024 * 1024;

    public function __construct(private readonly TipitakaExportService $exports) {}

    public function respond(string $id, ?string $range, ?string $ifNoneMatch): Response
    {
        $file = $this->exports->file($id);
        if ($file === null) {
            abort(404, __('messages.export_not_found'));
        }
        $etag = '"'.$id.'"';
        $headers = [
            'Content-Type' => $file['content_type'],
            'Content-Disposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                $file['filename'],
                $this->asciiFallback($file['filename']),
            ),
            'Accept-Ranges' => 'bytes',
            'ETag' => $etag,
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ];
        if ($file['content_encoding'] !== null) {
            // 离线包：表示是 NDJSON，传输编码 gzip。Range / Content-Length 都按 gzip 后的字节算
            $headers['Content-Encoding'] = $file['content_encoding'];
            $headers['Vary'] = 'Accept-Encoding';
        }
        if ($ifNoneMatch !== null && in_array($etag, array_map('trim', explode(',', $ifNoneMatch)), true)) {
            return new Response('', 304, $headers);
        }

        $size = $file['size'];
        [$start, $end] = [0, $size - 1];
        $status = 200;
        if ($range !== null && preg_match('/^bytes=(\d*)-(\d*)$/', trim($range), $m) && ($m[1] !== '' || $m[2] !== '')) {
            if ($m[1] === '') {
                // bytes=-500：最后 500 字节
                $start = max(0, $size - (int) $m[2]);
            } else {
                $start = (int) $m[1];
                $end = $m[2] === '' ? $size - 1 : min($size - 1, (int) $m[2]);
            }
            if ($start > $end || $start >= $size) {
                return new Response('', 416, $headers + ['Content-Range' => "bytes */{$size}"]);
            }
            $status = 206;
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
        }
        $headers['Content-Length'] = (string) ($end - $start + 1);
        $key = $this->exports->fileKey($id);

        return new StreamedResponse(function () use ($key, $start, $end) {
            for ($offset = $start; $offset <= $end; $offset += self::CHUNK) {
                echo RedisCache::getBinaryRange($key, $offset, min($end, $offset + self::CHUNK - 1));
                flush();
            }
        }, $status, $headers);
    }

    /**
     * Content-Disposition 的 filename= 只能是 ASCII，中文名放在 filename*= 里。
     */
    private function asciiFallback(string $filename): string
    {
        $ascii = (string) preg_replace('/[^\x20-\x7e]+/', '_', $filename);
        $ascii = str_replace(['"', '\\', '/', '%'], '_', $ascii);

        return trim($ascii, '_ ') === '' || str_starts_with($ascii, '_.') ? 'tipitaka'.strrchr($filename, '.') : $ascii;
    }
}
