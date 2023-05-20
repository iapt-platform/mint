<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Tools\Tools;
use App\Models\DhammaTerm;
use App\Http\Api\ChannelApi;
use Illuminate\Support\Str;

class UpgradeCommunityTerm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:community.term {lang}';

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
        $lang = strtolower($this->argument('lang'));
        $langFamily = explode('-',$lang)[0];
        $localTerm = ChannelApi::getSysChannel(
            "_community_term_{$lang}_"
        );
        if(!$localTerm){
            return 1;
        }
        $channelId = ChannelApi::getSysChannel('_System_Pali_VRI_');
        if($channelId === false){
            $this->error('no channel');
            return 1;
        }
        $table = DhammaTerm::select('word')->whereIn('language',[$this->argument('lang'),$lang,$langFamily])
                            ->groupBy('word');


        $words = $table->get();
        $bar = $this->output->createProgressBar(count($words));
        foreach ($words as $key => $word) {
            /**
             * 最优算法
             * 1. 找到最常见的意思
             * 2. 找到分数最高的
             */
            $bestNote = "" ;
            $allTerm = DhammaTerm::where('word',$word->word)
                                ->whereIn('language',[$this->argument('lang'),$lang,$langFamily])
                                ->get();
            foreach ($allTerm as $term) {
                # code..
                //经验值
                $exp = UserOperationDaily::where('user_id',$this->creator_id)
                                        ->where('date_int','<=',date_timestamp_get(date_create($term->updated_at))*1000)
                                        ->sum('duration');
                $iExp = (int)($exp/1000);
                $noteStrLen = mb_strlen($term->note);
                $paliStrLen = 0;
                #查找句子模版
                $pattern = "/\{\{([0-9].+?)\}\}/";
                $noteWithoutPali = preg_replace($pattern,"",$term->note);
                $sentences = [];
                $iSent = preg_match_all($pattern,$term->note,$sentences);
                foreach ($sentences as  $sentence) {
                    $sentId = explode("-",$sentence);
                    if(count($sentId) === 4){
                        $countTran = Sentence::where('book_id',$sentId[0])
                                            ->where('paragraph',$sentId[1])
                                            ->where('word_start',$sentId[2])
                                            ->where('word_end',$sentId[3])
                                            ->count();

                        $sentLen = Sentence::where('book_id',$sentId[0])
                                            ->where('paragraph',$sentId[1])
                                            ->where('word_start',$sentId[2])
                                            ->where('word_end',$sentId[3])
                                            ->where("channel_uid", $channelId)
                                            ->value('strlen');
                        if($sentLen){
                            $paliStrLen += $sentLen;
                        }
                    }
                }
                //计算得分

            }
            $hotMeaning = DhammaTerm::selectRaw('meaning,count(*) as co')
                        ->where('word',$word->word)
                        ->whereIn('language',[$this->argument('lang'),$lang,$langFamily])
                        ->groupBy('meaning')
                        ->orderBy('co','desc')
                        ->first();
            if($hotMeaning){
                $term = DhammaTerm::where('channal',$localTerm)->firstOrNew(
                        [
                            "word" => $word->word,
                            "channal" => $localTerm,
                        ],
                        [
                            'id' =>app('snowflake')->id(),
                            'guid' =>Str::uuid(),
                            'word_en' =>Tools::getWordEn($word->word),
                            'meaning' => '',
                            'language' => $this->argument('lang'),
                            'owner' => config("app.admin.root_uuid"),
                            'editor_id' => 0,
                            'create_time' => time()*1000,
                        ]
                    );
                    $term->meaning = $hotMeaning->meaning;
                    $term->modify_time = time()*1000;
                    $term->save();
            }
            $bar->advance();
        }
        $bar->finish();

        return 0;
    }
}
