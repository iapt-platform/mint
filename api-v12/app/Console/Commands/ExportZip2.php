<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;

use Symfony\Component\Process\Process;

class ExportZip2 extends Command
{
    protected $signature = 'export:zip2
        {filename : filename}
        {title : title}
        {id : 标识符}
        {format? : zip file format 7z,lzma,gz }';

    protected $description = '压缩导出的文件';

    public function handle()
    {
        Log::debug('export offline: 开始压缩');

        $defaultExportPath = storage_path('app/public/export/offline');
        $exportFile = $this->argument('filename');

        $filename = basename($exportFile);

        if ($filename === $exportFile) {
            $exportFullFileName = $defaultExportPath . '/' . $filename;
            $exportPath = $defaultExportPath;
        } else {
            $exportFullFileName = $exportFile;
            $exportPath = dirname($exportFile);
        }

        $format = $this->argument('format') ?? 'gz';

        if (!file_exists($exportFullFileName)) {

            Log::error('export offline: file not exists', [
                'file' => $exportFullFileName
            ]);

            $this->error('file not exists: ' . $exportFullFileName);

            return 1;
        }

        $zipFile = $this->getZipFileName($filename, $format);
        $zipFullFileName = $exportPath . '/' . $zipFile;

        if (file_exists($zipFullFileName)) {
            unlink($zipFullFileName);
        }

        $this->info("start compress: {$exportFullFileName}");
        Log::debug('export offline zip start', [
            'file' => $exportFullFileName,
            'format' => $format
        ]);

        $this->compress($exportFullFileName, $zipFullFileName, $format);

        $this->info('压缩完成');

        Log::debug('zip done', [
            'zip' => $zipFullFileName
        ]);

        /*
        |--------------------------------------------------------------------------
        | 上传 S3
        |--------------------------------------------------------------------------
        */

        $bucket = config('mint.attachments.bucket_name.temporary');
        $tmpFile = $bucket . '/' . $zipFile;

        $this->info('upload file=' . $tmpFile);

        Log::debug('export offline upload', [
            'file' => $tmpFile
        ]);

        Storage::put($tmpFile, fopen($zipFullFileName, 'r'));

        $this->info('upload done');

        Log::debug('upload done');

        /*
        |--------------------------------------------------------------------------
        | 生成下载链接
        |--------------------------------------------------------------------------
        */

        if (App::environment('local')) {
            $link = Storage::url($tmpFile);
        } else {
            try {
                $link = Storage::temporaryUrl(
                    $tmpFile,
                    now()->addDays(2)
                );
            } catch (\Exception $e) {
                Log::error('temporaryUrl fail', [
                    'exception' => $e
                ]);
                $this->error('generate temporaryUrl fail');
                return 1;
            }
        }

        $this->info('link=' . $link);

        /*
        |--------------------------------------------------------------------------
        | CDN 列表
        |--------------------------------------------------------------------------
        */

        $url = [];
        foreach (config('mint.server.cdn_urls') as $key => $cdn) {
            $url[] = [
                'link' => $cdn . '/' . $zipFile,
                'hostname' => 'china cdn-' . $key
            ];
        }

        $url[] = [
            'link' => $link,
            'hostname' => 'Amazon cloud storage(Hongkong)'
        ];

        /*
        |--------------------------------------------------------------------------
        | Cache 写入
        |--------------------------------------------------------------------------
        */

        $info = Cache::get('/offline/index', []);

        $info[] = [
            'id' => $this->argument('id'),
            'title' => $this->argument('title'),
            'filename' => $zipFile,
            'url' => $url,
            'create_at' => now()->toDateTimeString(),
            'chapter' => Cache::get("/export/chapter/count"),
            'filesize' => filesize($zipFullFileName),
            'min_app_ver' => '1.3',
        ];

        Cache::put('/offline/index', $info);

        /*
        |--------------------------------------------------------------------------
        | 删除原始文件
        |--------------------------------------------------------------------------
        */

        sleep(5);
        try {
            if (is_file($exportFullFileName)) {
                unlink($exportFullFileName);
            }
            if (file_exists($zipFullFileName)) {
                unlink($zipFullFileName);
            }
        } catch (\Throwable $e) {
            Log::error('delete source fail', [
                'exception' => $e
            ]);
        }

        return 0;
    }

    /*
    |--------------------------------------------------------------------------
    | 生成压缩文件名
    |--------------------------------------------------------------------------
    */

    protected function getZipFileName(string $filename, string $format): string
    {
        return match ($format) {
            '7z' => $filename . '.7z',
            'lzma' => $filename . '.lzma',
            default => $filename . '.tar.gz'
        };
    }

    /*
    |--------------------------------------------------------------------------
    | 压缩函数
    |--------------------------------------------------------------------------
    */

    protected function compress($source, $target, $format)
    {
        $isDir = is_dir($source);
        switch ($format) {
            case '7z':
                $command = [
                    '7z',
                    'a',
                    '-t7z',
                    '-mx=9',
                    $target,
                    $source
                ];
                break;

            case 'lzma':
                if ($isDir) {
                    $tmpTar = $source . '.tar';
                    $tar = new Process([
                        'tar',
                        '-cf',
                        $tmpTar,
                        '-C',
                        dirname($source),
                        basename($source)
                    ]);
                    $tar->run();
                    $source = $tmpTar;
                }
                $command = [
                    'xz',
                    '-k',
                    '-9',
                    '--format=lzma',
                    $source
                ];
                break;

            default:
                $command = [
                    'tar',
                    '-czf',
                    $target,
                    '-C',
                    dirname($source),
                    basename($source)
                ];
        }

        $this->info(implode(' ', $command));
        $process = new Process($command);
        $process->setTimeout(60 * 60 * 6);
        $process->run();

        $this->info($process->getOutput());

        if (!$process->isSuccessful()) {

            Log::error('compress fail', [
                'error' => $process->getErrorOutput()
            ]);

            throw new \RuntimeException($process->getErrorOutput());
        }
    }
}
