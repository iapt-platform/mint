<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExportOffline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:offline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        //建表
        $this->info('create db');
        $this->call('export:create.db');
        //导出channel
        $this->info('channel');
        $this->call('export:channel');
        //tag
        $this->info('tag');
        $this->call('export:tag');
        $this->call('export:tag.map');
        $this->info('pali text');
        $this->call('export:pali.text');
        //导出章节索引
        $this->info('chapter');
        $this->call('export:chapter.index');
        //导出译文
        $this->info('sentence');
        $this->call('export:sentence');
        //导出原文
        $this->call('export:sentence',['--type'=>'original']);

        $this->info('zip');
        $exportFile = storage_path('app/public/export/offline/sentence-'.date("Y-m-d").'.db3');
        shell_exec("tar jcf ".
                    storage_path("app/public/export/offline-".date("Y-m-d").".tar.xz")." ".
                    $exportFile);
        return 0;
    }
}
