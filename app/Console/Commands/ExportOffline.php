<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

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
        $exportPath = 'app/public/export/offline';
        $exportFile = 'sentence-'.date("Y-m-d").'.db3';
        $zipFile = "sentence-".date("Y-m-d").".db3.gz";

        $exportFullFileName = storage_path($exportPath.'/'.$exportFile);
        $zipFullFileName = storage_path($exportPath.'/'.$zipFile);

        shell_exec("cd ".storage_path($exportPath));
        shell_exec("gzip -k -q --best -c {$exportFullFileName} > {$zipFullFileName}");
        shell_exec("chmod 600 {$zipFullFileName}");

        $info = array();
        $info[] = ['filename'=>$exportFile,
                   'create_at'=>date("Y-m-d H:i:s"),
                   'chapter'=>Cache::get("/export/chapter/count"),
                   'filesize'=>filesize($zipFullFileName),
                    ];
        Storage::disk('local')->put("public/export/offline/index.json", json_encode($info));
        return 0;
    }
}
