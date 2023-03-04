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
        //导出channel
        $this->call('export:channel');
        //导出channel
        $this->call('export:tag');
        $this->call('export:tag.map');
        $this->call('export:pali.text');
        //导出章节索引
        $this->call('export:chapter.index');
        //导出译文
        $this->call('export:sentence');
        //导出原文
        $this->call('export:sentence',['--type'=>'original']);
        shell_exec("XZ_OPT=-9 tar jcvf ".storage_path("app/public/export/offline.tar.xz")." ".storage_path("app/public/export/offline"));
        return 0;
    }
}
