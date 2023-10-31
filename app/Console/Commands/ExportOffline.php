<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Tools\RedisClusters;
use Illuminate\Support\Facades\Redis;

class ExportOffline extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan export:offline lzma
     * @var string
     */
    protected $signature = 'export:offline {format?  : zip file format 7z,lzma,gz }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'export  offline data for app';

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
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
        $exportDir = storage_path('app/public/export/offline');
        if(!is_dir($exportDir)){
            $res = mkdir($exportDir,0755,true);
            if(!$res){
                Log::error('mkdir fail path='.$exportDir);
                return 1;
            }
        }
        $exportStop = $exportDir.'/.stop';
        $file = fopen($exportStop,'w');
        fclose($file);

        //建表
        $this->info('create db');
        $this->call('export:create.db');

        //term
        $this->info('term');
        $this->call('export:term');

        //导出channel
        $this->info('channel');
        $this->call('export:channel');

        //tag
        $this->info('tag');
        $this->call('export:tag');
        $this->call('export:tag.map');
        //
        $this->info('pali text');
        $this->call('export:pali.text');
        //导出章节索引
        $this->info('chapter');
        $this->call('export:chapter.index');
        //导出译文
        $this->info('sentence');
        $this->call('export:sentence',['--type'=>'translation']);
        $this->call('export:sentence',['--type'=>'nissaya']);
        //导出原文
        $this->call('export:sentence',['--type'=>'original']);

        $this->info('zip');
        Log::debug('export offline: db写入完毕 开始压缩');
        $exportPath = 'app/public/export/offline';
        $exportFile = 'wikipali-offline-'.date("Y-m-d").'.db3';
        Log::debug('export offline: zip file {filename} {format}',
                    [
                        'filename'=>$exportFile,
                        'format'=>$this->argument('format')
                    ]);
        switch ($this->argument('format')) {
            case '7z':
                $zipFile = $exportFile . ".7z";
                break;
            case 'lzma':
                $zipFile = $exportFile . ".lzma";
                break;
            default:
                $zipFile = $exportFile . ".gz";
                break;
        }
        //
        $exportFullFileName = storage_path($exportPath.'/'.$exportFile);
        $zipFullFileName = storage_path($exportPath.'/'.$zipFile);

        shell_exec("cd ".storage_path($exportPath));
        if($this->argument('format')==='7z'){
            $command = "7z a -t7z -m0=lzma -mx=9 -mfb=64 -md=32m -ms=on {$zipFullFileName} {$exportFullFileName}";
        }else if($this->argument('format')==='lzma'){
            $command = "xz -k -9 --format=lzma {$exportFullFileName}";
        }else{
            $command = "gzip -k -q --best -c {$exportFullFileName} > {$zipFullFileName}";
        }
        $this->info($command);
        Log::debug('export offline: zip command:'.$command);
        shell_exec($command);
        Log::debug('zip file {filename} in {format} saved.',
                    [
                        'filename'=>$exportFile,
                        'format'=>$this->argument('format')
                    ]);
        $info = array();
        $url = array();
        $url[] = [
                'link'=>'https://www.wikipali.cc/downloads/'.$zipFile,
                'hostname'=>'阿里云·中国',
            ];
        //s3
        Storage::put($zipFile, file_get_contents($zipFullFileName));
        $s3Link = Storage::url($zipFile);
        Log::info('export offline: link='.$s3Link);
        $url[] = [
            'link'=>$s3Link,
            'hostname'=>'Amazon cloud storage(Hongkong)',
        ];
        $info[] = ['filename'=>$zipFile,
                    'url' => $url,
                   'create_at'=>date("Y-m-d H:i:s"),
                   'chapter'=>RedisClusters::get("/export/chapter/count"),
                   'filesize'=>filesize($zipFullFileName),
                   'min_app_ver'=>'1.3',
                    ];
        RedisClusters::put('/offline/index',$info);
        unlink($exportStop);
        unlink($exportFullFileName);


        return 0;
    }
}
