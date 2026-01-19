<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Tools\RedisClusters;
use Illuminate\Support\Facades\App;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ExportZip extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:zip {filename : filename} {title : title} {id : 标识符} {format?  : zip file format 7z,lzma,gz }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '压缩导出的文件';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
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
        Log::debug(
            'export offline: zip file {filename} {format}',
            [
                'filename' => $exportFile,
                'format' => $this->argument('format'),
                'exportFullFileName' => $exportFullFileName,
                'exportPath' => $exportPath,
            ]
        );
        switch ($this->argument('format')) {
            case '7z':
                $zipFile = $filename . ".7z";
                break;
            case 'lzma':
                $zipFile = $filename . ".lzma";
                break;
            default:
                $zipFile = $filename . ".gz";
                break;
        }
        //
        if (!file_exists($exportFullFileName)) {
            Log::error('export offline: no  file {filename}', ['filename' => $exportFullFileName]);
            $this->error('export offline: no  file {filename}' . $exportFullFileName);
            return 1;
        }

        $zipFullFileName = $exportPath . '/' . $zipFile;
        if (file_exists($zipFullFileName)) {
            Log::debug('export offline: delete old zip file:' . $zipFullFileName);
            unlink($zipFullFileName);
        }

        shell_exec("cd " . $exportPath);
        switch ($this->argument('format')) {
            case '7z':
                $command = [
                    '7z',
                    'a',
                    '-t7z',
                    '-m0=lzma',
                    '-mx=9',
                    '-mfb=64',
                    '-md=32m',
                    '-ms=on',
                    $zipFullFileName,
                    $exportFullFileName
                ];
                break;
            case 'lzma':
                $command = ['xz', '-k', '-9', '--format=lzma', $exportFullFileName];
                break;
            default:
                $command = ['gzip', $exportFullFileName];
                break;
        }

        $this->info(implode(' ', $command));
        Log::debug('export offline zip start', ['command' => $command, 'format' => $this->argument('format')]);
        $process = new Process($command);
        $process->setTimeout(60 * 60 * 6);
        $process->run();
        $this->info($process->getOutput());
        $this->info('压缩完成');
        Log::debug(
            'zip file {filename} in {format} saved.',
            [
                'filename' => $exportFile,
                'format' => $this->argument('format')
            ]
        );

        $url = array();
        foreach (config('mint.server.cdn_urls') as $key => $cdn) {
            $url[] = [
                'link' => $cdn . '/' . $zipFile,
                'hostname' => 'china cdn-' . $key,
            ];
        }

        $bucket = config('mint.attachments.bucket_name.temporary');
        $tmpFile =  $bucket . '/' . $zipFile;

        $this->info('upload file=' . $tmpFile);
        Log::debug('export offline: upload file {filename}', ['filename' => $tmpFile]);

        Storage::put($tmpFile, file_get_contents($zipFullFileName));

        $this->info('upload done file=' . $tmpFile);
        Log::debug('export offline: upload done {filename}', ['filename' => $tmpFile]);

        if (App::environment('local')) {
            $link = Storage::url($tmpFile);
        } else {
            try {
                $link = Storage::temporaryUrl($tmpFile, now()->addDays(2));
            } catch (\Exception $e) {
                $this->error('generate temporaryUrl fail');
                Log::error(
                    'export offline: generate temporaryUrl fail {Exception}',
                    [
                        'exception' => $e,
                        'file' => $tmpFile
                    ]
                );
                return 1;
            }
        }
        $this->info('link = ' . $link);
        Log::info('export offline: link=' . $link);

        $url[] = [
            'link' => $link,
            'hostname' => 'Amazon cloud storage(Hongkong)',
        ];
        $info = RedisClusters::get('/offline/index');
        if (!is_array($info)) {
            $info = array();
        }
        $info[] = [
            'id' => $this->argument('id'),
            'title' => $this->argument('title'),
            'filename' => $zipFile,
            'url' => $url,
            'create_at' => date("Y-m-d H:i:s"),
            'chapter' => RedisClusters::get("/export/chapter/count"),
            'filesize' => filesize($zipFullFileName),
            'min_app_ver' => '1.3',
        ];
        RedisClusters::put('/offline/index', $info);
        sleep(5);
        try {
            unlink($exportFullFileName);
        } catch (\Throwable $th) {
            Log::error(
                'export offline: delete  file fail {Exception}',
                [
                    'exception' => $th,
                    'file' => $exportFullFileName
                ]
            );
        }

        return 0;
    }
}
