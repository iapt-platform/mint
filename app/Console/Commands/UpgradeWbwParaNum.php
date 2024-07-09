<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WbwTemplate;

class UpgradeWbwParaNum extends Command
{
    /**
     * The name and signature of the console command.
     *  php artisan upgrade:wbw.para.num 130
     * @var string
     */
    protected $signature = 'upgrade:wbw.para.num {book?}';

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
        if(empty($this->argument('book'))){
            $start = 1;
            $end = 217;
        }else{
            $start = $this->argument('book');
            $end = $this->argument('book');
        }

        for ($iBook=$start; $iBook <=$end ; $iBook++) {
            $this->info("book = {$iBook}");

            $startAt = time();
            $count = WbwTemplate::where('book',$iBook)->count();
            $bar = $this->output->createProgressBar($count);
            $rows = WbwTemplate::where('book',$iBook)
                                ->orderBy('paragraph')
                                ->orderBy('wid')->cursor();
            $start = false;
            $bookCode='';
            $count=0;
            $currPara = 0;
            $bookCodeStack = [];
            foreach ($rows as $key => $row) {
                $bar->advance();
                if($row->paragraph !== $currPara){
                    $currPara = $row->paragraph;
                    $start = false;
                    $bookCode='';
                    $bookCodeStack = [];
                }
                if($row->word==='('){
                    $start = true;
                    $bookCode='';
                    $bookCodeStack = [];
                    continue;
                }
                if($start){
                    if(is_numeric(str_replace('-','',$row->word))){
                        if(empty($bookCode) && count($bookCodeStack)>0){
                            //继承之前的
                            $bookCodeList=[];
                            foreach ($bookCodeStack as $key => $value) {
                                $bookCodeList[] = $key;
                            }
                            $bookCode = $bookCodeList[0];
                        }
                        $dot = mb_strrpos($bookCode,'.',0,'UTF-8');
                        if($dot === false){
                            $bookName = $bookCode;
                            $paraNum = $row->word;
                        }else{
                            $bookName = mb_substr($bookCode,0,$dot+1,'UTF-8');
                            $paraNum = mb_substr($bookCode,$dot+1,null,'UTF-8').$row->word;
                        }
                        $bookName = mb_strtolower(mb_substr($bookName,0,64,'UTF-8'),'UTF-8');
                        $bookCodeStack[$bookName] = 1;
                        if(!empty($bookName)){
                            WbwTemplate::where('id',$row->id)->update([
                                'type'=>':cs.para:',
                                'gramma'=>$bookName,
                                'part'=>$paraNum,
                            ]);
                        }
                        $count++;
                    }else if($row->word===';'){
                        $bookCode = '';
                        continue;
                    }else if($row->word===')'){
                        $start = false;
                        continue;
                    }
                    $bookCode .= $row->word;
                }
            }
            $bar->finish();
            $time = time() - $startAt;
            $this->info(" {$time}s {$count}");
        }

        return 0;
    }
}
