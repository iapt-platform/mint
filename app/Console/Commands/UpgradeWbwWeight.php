<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WbwTemplate;

class UpgradeWbwWeight extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan upgrade:wbw.weight 66
     * @var string
     */
    protected $signature = 'upgrade:wbw.weight {book?} {para?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'upgrade wbw template weight by word bold ';

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
        $book = $this->argument('book');
        $para = $this->argument('para');

        for ($iBook=1; $iBook <= 217 ; $iBook++) {
            if($book && $book != $iBook ){
                continue;
            }
            $this->info('running book='.$iBook);
            $maxPara = WbwTemplate::where("book",$iBook)->max('paragraph');
		    $bar = $this->output->createProgressBar($maxPara);
            for ($iPara=1; $iPara <= $maxPara ; $iPara++) {
                if($para && $para != $iPara ){
                    continue;
                }
                $bar->advance();
                $words = WbwTemplate::where("book",$iBook)
                                    ->where("paragraph",$iPara)
                                    ->orderBy('wid','asc')
                                    ->get();
                $start = -1;
                $bold = 0;
                $katama = false;
                $katamaDis = 0;
                $katamaWeight = 1;
                $arrKatama = array();//katama 权重结果
                //先计算katama权重
                for ($iWord=0; $iWord < count($words); $iWord++) {
                    //计算katama加分
                    if(empty($words[$iWord]->real)){
                        $katama = false;
                        $katamaWeight = 0;
                    }
                    if($katama){
                        $katamaDis++;
                        $katamaWeight = 463 * (pow(0.2, ($katamaDis-1))+1);
                    }
                    $arrKatama[] = $katamaWeight;
                    if(mb_substr($words[$iWord]->real,0,5,"UTF-8") === 'katam'){
                        $katama = true;
                        $katamaDis = 0;
                    }
                }
                for ($iWord=0; $iWord < count($words); $iWord++) {
                    $wid = $words[$iWord]->wid;
                    $weight = 1.01;
                    WbwTemplate::where('id',$words[$iWord]->id)->update(['weight'=>$weight*1000]);

                    if($words[$iWord]->style === 'bld' && !empty($words[$iWord]->real)){
                        if($start === -1){
                            $start = $iWord;
                            $bold = 1;
                        }else{
                            $bold++;
                        }
                    }else{
                        if($start>=0){
                            //前面的词是黑体字
                            $result = WbwTemplate::where('id',$words[$iWord]->id)
                                                    ->update(['weight'=>floor(($weight+$arrKatama[$iWord])*1000)]);
                            $weight = 1.01+23*pow(10,(2-$bold));
                            for ($i=$start; $i < $iWord ; $i++) {
                                $result = WbwTemplate::where('id',$words[$i]->id)
                                                    ->update(['weight'=>floor(($weight+$arrKatama[$i])*1000)]);
                            }

                            $start = -1;
                            $bold = 0;
                        }else{
                            $result = WbwTemplate::where('id',$words[$iWord]->id)
                                        ->update(['weight'=>floor(($weight+$arrKatama[$iWord])*1000)]);
                        }
                    }
                }
            }
            $bar->finish();
        }
        return 0;
    }
}
