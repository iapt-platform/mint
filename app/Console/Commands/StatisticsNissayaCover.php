<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Channel;
use App\Models\Sentence;
use App\Models\PaliSentence;
use App\Tools\RedisClusters;

class StatisticsNissayaCover extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan statistics:nissaya.cover
     * @var string
     */
    protected $signature = 'statistics:nissaya.cover';
    protected $types = [
        'mula'=>[
            69,70,71,72,73,74,
            75,76,77,78,79,80,
            81,82,83,84,85,86,
            87,88,89,90,91,92,
            93,94,95,143,144,145,
            146,147,148,149,150,151,
            152,153,154,155,156,157,
            158,159,160,161,162,163,
            164,165,166,167,168,169,
            170,171,213,214,215,216,217,
        ],
        'atthakatha' => [
            64,65,96,97,98,99,
            100,101,102,103,104,105,
            106,107,108,109,110,111,
            112,113,114,115,116,117,
            118,119,120,121,122,123,
            124,125,126,127,128,129,
            130,131,132,133,134,135,
            136,137,138,139,140,141,142,
        ],
        'tika' => [
            66,67,68,172,173,174,
            175,176,177,178,179,180,
            181,182,183,184,185,186,
            187,188,189,190,191,192,
            193,194,195,196,197,198,
            199,200,201,202,203,204,
            205,206,207,208,209,210,211,212,
        ],
        'vinaya' => [138,139,140,141,142,200,201,202,203,204,205,206,207,208,209,210,211,212,213,214,215,216,217,],
        'sutta' => [
            82,83,84,85,86,
            87,88,89,90,91,92,93,
            94,95,99,100,101,102,
            103,104,105,106,107,108,
            109,110,111,112,113,114,
            115,116,117,118,119,120,
            121,122,123,124,125,126,
            127,128,129,130,131,132,
            133,134,135,136,137,143,
            144,145,146,147,148,149,
            150,151,152,153,154,155,
            156,157,158,159,160,161,
            162,163,164,165,166,167,
            168,169,170,171,181,182,
            183,184,185,186,187,188,
            189,190,191,192,193,194,
            195,196,197,198,199,
        ],
        'abhidhamma' => [69,70,71,72,73,74,75,76,77,78,79,80,81,96,97,98,172,173,174,175,176,177,178,179,180,],
    ];
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '统计nissaya覆盖度';

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
        $nissaya_channels = Channel::where('type','nissaya')
                                ->where('lang','my')
                                ->select('uid')->get();
        $this->info('channel:'.count($nissaya_channels));
        $output = [];
        foreach ($this->types as $type => $books) {
            # code...
            $pali = PaliSentence::whereIn('book',$books)->sum('length');
            $nissayaSentences = Sentence::whereIn('channel_uid',$nissaya_channels)
                                ->whereIn('book_id',$books)
                                ->groupBy(['book_id','paragraph','word_start','word_end'])
                                ->select(['book_id','paragraph','word_start','word_end'])
                                ->get();
            $sentences = [];
            $final = 0;
            $this->info($type . count($nissayaSentences). " sentences");
            if(count($nissayaSentences)>0){
                $count = 0;
                foreach ($nissayaSentences as  $value) {
                    $sentences[] = [
                        $value->book_id,
                        $value->paragraph,
                        $value->word_start,
                        $value->word_end,
                    ];
                    if($count % 100 === 0 ){
                        $final += PaliSentence::whereIns(['book','paragraph','word_begin','word_end'],$sentences)
                                        ->sum('length');
                        $sentences = [];
                        $percent = intval($count * 100 / count($nissayaSentences));
                        $this->info("[{$percent}] {$final}");
                    }
                    $count++;
                }

            }

            $this->info($type . '=' . $pali . '=' . $final);
            $output[] = ['type'=>$type,'total'=>$pali,'final'=>$final];

        }
        RedisClusters::put('/statistics/nissaya/cover',$output,48*3600);
        return 0;
    }
}
