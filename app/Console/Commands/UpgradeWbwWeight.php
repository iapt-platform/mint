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
                for ($iWord=0; $iWord < count($words); $iWord++) {
                    WbwTemplate::where('id',$words[$iWord]->id)->update(['weight'=>1]);
                    if($words[$iWord]->style === 'bld'){
                        if($start === -1){
                            $start = $iWord;
                            $bold = 1;
                        }else{
                            $bold++;
                        }
                    }else{
                        if($start>=0){
                            $weight = 1 +  100 / pow($bold,2);
                            for ($i=$start; $i < $iWord ; $i++) {
                                $result = WbwTemplate::where('id',$words[$i]->id)
                                                    ->update(['weight'=>(int)$weight]);
                            }
                            $start = -1;
                            $bold = 0;
                        }
                    }
                }
            }
            $bar->finish();
        }
        return 0;
    }
}
