<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BookTitle;
use App\Models\FtsText;
use App\Models\WbwTemplate;
use App\Tools\PaliSearch;

class UpgradeFts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:fts {--content : upgrade col content only}
        {para?}
        {--test : output log only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'upgrade full text search table';

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

        if(!empty($this->argument('para'))){
            $para = explode('-',$this->argument('para'));
        }
        for ($iBook=1; $iBook <= 217; $iBook++) {
            if(isset($para[0]) && $para[0] != $iBook){
                continue;
            }
            # code...
            $this->info('book:'.$iBook);
            $maxParagraph = WbwTemplate::where('book',$iBook)->max('paragraph');
            $bar = $this->output->createProgressBar($maxParagraph-1);
            for($iPara=1; $iPara <= $maxParagraph; $iPara++){
                if(isset($para[1]) && $para[1] != $iPara){
                    $bar->advance();
                    continue;
                }
                $content = $this->getContent($iBook,$iPara);
                //查找黑体字
                $words = WbwTemplate::where('book',$iBook)
                                    ->where('paragraph',$iPara)
                                    ->orderBy('wid')->get();
                $bold1 = array();
                $bold2 = array();
                $bold3 = array();
                $currBold = array();
                foreach ($words as $word) {
                    if($word->style==='bld'){
                        $currBold[] = $word->real;
                    }else{
                        $countBold = count($currBold);
                        if($countBold === 1){
                            $bold1[] = $currBold[0];
                        }else if($countBold === 2){
                            $bold2 = array_merge($bold2,$currBold);
                        }else if($countBold > 0){
                            $bold3 = array_merge($bold3,$currBold);
                        }
                        $currBold = [];
                    }
                }
                $pcd_book = BookTitle::where('book',$iBook)
                        ->where('paragraph','<=',$iPara)
                        ->orderBy('paragraph','desc')
                        ->first();
                if($pcd_book){
                    $pcd_book_id = $pcd_book->sn;
                }else{
                    $pcd_book_id = BookTitle::where('book',$iBook)
                                            ->orderBy('paragraph')
                                            ->value('sn');
                }
                if($this->option('test')){
                    $this->info($content.
                                ' pcd_book='.$pcd_book_id.
                                ' bold1='.implode(' ',$bold1).
                                ' bold2='.implode(' ',$bold2).
                                ' bold3='.implode(' ',$bold3).PHP_EOL
                                );
                }else{
                    $update = PaliSearch::update($iBook,
                                                 $iPara,
                                                implode(' ',$bold1),
                                                implode(' ',$bold2),
                                                implode(' ',$bold3),
                                                $content,
                                                $pcd_book_id);
                }
                $bar->advance();
            }
            $bar->finish();
            $this->info('done');
        }

        return 0;
    }

    private function getContent($book,$para){
        $words = WbwTemplate::where('book',$book)
                            ->where('paragraph',$para)
                            ->where('type',"<>",".ctl.")
                            ->orderBy('wid')->get();
        $content = '';
        foreach ($words as  $word) {
            if($word->style === 'bld'){
                if(strpos($word->word,"{")===FALSE){
                    $content .= "**{$word->word}** ";
                }else{
                    $content .= str_replace(['{','}'],['**','** '],$word->word);
                }
            }else if($word->style === 'note'){
                $content .= " _{$word->word}_ ";
            }else{
                $content .= $word->word . " ";
            }
        }
        return $content;
    }
}


