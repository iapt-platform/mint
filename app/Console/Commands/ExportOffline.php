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
    protected $signature = 'export:offline {format?  : zip file format 7z,lzma,gz } {--shortcut}';

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
        $this->info('export term');
        $this->call('export:term');

        //导出channel
        $this->info('export channel');
        $this->call('export:channel');

        if(!$this->option('shortcut')){
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
        }

        $this->info('zip');
        Log::debug('export offline: db写入完毕');

        sleep(5);
        $this->call('export:zip',['format'=>$this->argument('format')]);

        unlink($exportStop);

        return 0;
    }
}
