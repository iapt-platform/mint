<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BookTitle;
use App\Models\FtsText;
use App\Models\WbwTemplate;

class UpgradeFts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:fts {--content : upgrade col content}
        {para?}
        {--test : output log}';

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

        if($this->option('content')){
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
                    if($this->option('test')){
                        $this->info($content);
                    }else{
                        //TODO update bold
                        FtsText::where('book',$iBook)->where('paragraph',$iPara)->update(['content'=>$content]);
                    }
                    $bar->advance();
                }
                $bar->finish();
                $this->info('done');
            }
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


