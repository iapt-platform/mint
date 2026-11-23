<?php

namespace App\Services\V3;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 把临时区里的导出文件交给客户端。
 *
 * - 默认磁盘是 S3（生产）：302 跳到 S3 的临时签名链接，流量不经 PHP；文件名由链接参数给，
 *   类型与离线包的 `Content-Encoding: gzip` 已写在对象上，`Range` / `If-None-Match` 由 S3 处理。
 * - 其他磁盘（本地开发）：自己流式读，按 1MB 分块，不把整个文件读进内存；
 *   支持单段 `Range`（mobile 断点续传）与 `If-None-Match`。ETag 按文件的大小与修改时间算，不用导出 id：
 *   同一范围重新生成会覆盖同一个文件，旧 id 的链接拿到的是新文件。
 */
class TipitakaExportDownload
{
    private const CHUNK = 1024 * 1024;

    /** 跳去 S3 的签名链接有效多久（秒）。每次下载现签；给慢速网络下的大文件留足时间。 */
    private const REDIRECT_TTL = 3600;

    public function __construct(private readonly TipitakaExportService $exports) {}

    public function respond(string $id, ?string $range, ?string $ifNoneMatch): Response
    {
        $file = $this->exports->file($id);
        if ($file === null) {
            abort(404, __('messages.export_not_found'));
        }
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $file['filename'],
            $this->asciiFallback($file['filename']),
        );
        if (config('filesystems.disks.'.config('filesystems.default').'.driver') === 's3') {
            return redirect()->away(Storage::temporaryUrl($file['path'], now()->addSeconds(self::REDIRECT_TTL), [
                'ResponseContentDisposition' => $disposition,
            ]));
        }

        $etag = '"'.$file['size'].'-'.Storage::lastModified($file['path']).'"';
        $headers = [
            'Content-Type' => $file['content_type'],
            'Content-Disposition' => $disposition,
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
        $path = $file['path'];

        return new StreamedResponse(function () use ($path, $start, $end) {
            $stream = Storage::readStream($path);
            if ($stream === null) {
                return;
            }
            fseek($stream, $start);
            $remaining = $end - $start + 1;
            while ($remaining > 0 && ! feof($stream)) {
                $chunk = fread($stream, min(self::CHUNK, $remaining));
                if ($chunk === false || $chunk === '') {
                    break;
                }
                echo $chunk;
                flush();
                $remaining -= strlen($chunk);
            }
            fclose($stream);
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
