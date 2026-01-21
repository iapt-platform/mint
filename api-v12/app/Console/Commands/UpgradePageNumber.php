<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WbwTemplate;
use App\Models\PageNumber;

class UpgradePageNumber extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:page.number';

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
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
        $table = WbwTemplate::where('type','.ctl.')->orderBy('book')->orderBy('paragraph')->cursor();
        $pageHead = ['M','P','T','V','O'];
        $bar = $this->output->createProgressBar(WbwTemplate::where('type','.ctl.')->count());
        foreach ($table as $key => $value) {
            $type = substr($value->word,0,1);
            if(in_array($type,$pageHead)){
                $arrPage = explode('.',$value->word);
                if(count($arrPage)!==2){
                    continue;
                }
                $page = PageNumber::firstOrNew(
                    [
                        'book'=>$value->book,
                        'paragraph'=>$value->paragraph,
                        'wid'=>$value->wid,
                    ],
                    [
                        'type'=>$type,
                        'volume'=>(int)substr($arrPage[0],1),
                        'page'=>(int)$arrPage[1],
                        'pcd_book_id'=>$value->pcd_book_id,
                    ]
                    );
                    $page->save();
            }
            $bar->advance();
        }
        $bar->finish();
        return 0;
    }
}
