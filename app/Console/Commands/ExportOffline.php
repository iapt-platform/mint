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
    protected $signature = 'export:offline {format?  : zip file format 7z,lzma,gz } {--shortcut}  {--driver=morus}';

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

        //删除全部的旧文件
        foreach (scandir($exportDir) as $key => $file) {
            if(is_file($exportDir.'/'.$file)){
                unlink($exportDir.'/'.$file);
            }
        }
        //添加 .stop
        $exportStop = $exportDir.'/.stop';
        $file = fopen($exportStop,'w');
        fclose($file);

        //建表
        $this->info('create db');
        $this->call('export:create.db');

        //term
        $this->info('export term start');
        $this->call('export:term');

        //导出channel
        $this->info('export channel start');
        $this->call('export:channel',['db'=>'wikipali-offline']);
        $this->call('export:channel',['db'=>'wikipali-offline-index']);

        if(!$this->option('shortcut')){
            //tag
            $this->info('export tag start');
            $this->call('export:tag',['db'=>'wikipali-offline']);
            $this->call('export:tag.map',['db'=>'wikipali-offline']);
            //
            $this->info('export pali text start');
            $this->call('export:pali.text');
            //导出章节索引
            $this->info('export chapter start');
            $this->call('export:chapter.index',['db'=>'wikipali-offline']);
            $this->call('export:chapter.index',['db'=>'wikipali-offline-index']);
            //导出译文
            $this->info('export sentence start');
            $this->call('export:sentence',['--type'=>'translation','--driver'=>$this->option('driver')]);
            $this->call('export:sentence',['--type'=>'nissaya','--driver'=>$this->option('driver')]);
            //导出原文
            $this->call('export:sentence',['--type'=>'original','--driver'=>$this->option('driver')]);
        }

        $this->info('zip');
        Log::info('export offline: db写入完毕 开始压缩');

        sleep(5);
        $this->call('export:zip',[
            'db'=>'wikipali-offline-index',
            'format'=>$this->argument('format'),
        ]);
        $this->call('export:zip',[
            'db'=>'wikipali-offline',
            'format'=>$this->argument('format'),
        ]);

        unlink($exportStop);
        return 0;
    }
}
