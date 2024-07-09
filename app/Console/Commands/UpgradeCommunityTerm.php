<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Tools\Tools;
use App\Models\DhammaTerm;
use App\Models\UserOperationDaily;
use App\Models\Sentence;

use App\Http\Api\ChannelApi;
use Illuminate\Support\Str;

class UpgradeCommunityTerm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:community.term {lang} {word?}';

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
        $lang = strtolower($this->argument('lang'));
        $langFamily = explode('-',$lang)[0];
        $localTerm = ChannelApi::getSysChannel("_community_term_{$lang}_");
        if(!$localTerm){
            return 1;
        }

        $channelId = ChannelApi::getSysChannel('_System_Pali_VRI_');
        if($channelId === false){
            $this->error('no channel');
            return 1;
        }
        $table = DhammaTerm::select(['word','tag'])
                            ->whereIn('language',[$this->argument('lang'),$lang,$langFamily])
                            ->groupBy(['word','tag']);

        if($this->argument('word')){
            $table = $table->where('word',$this->argument('word'));
        }
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
                                ->where('tag',$word->tag)
                                ->whereIn('language',[$this->argument('lang'),$lang,$langFamily])
                                ->get();
            $score = [];
            foreach ($allTerm as $key => $term) {
                //经验值
                $exp = UserOperationDaily::where('user_id',$term->editor_id)
                                        ->where('date_int','<=',date_timestamp_get(date_create($term->updated_at))*1000)
                                        ->sum('duration');
                $iExp = (int)($exp/1000);
                $noteStrLen = $term->note? mb_strlen($term->note,'UTF-8'):0;
                $paliStrLen = 0;
                $tranStrLen = 0;
                $noteWithoutPali = "";
                if($term->note && !empty(trim($term->note))){
                    //计算note得分
                    //查找句子模版
                    $pattern = "/\{\{[0-9].+?\}\}/";
                    //获取去掉句子模版的剩余部分
                    $noteWithoutPali = preg_replace($pattern,"",$term->note);
                    $sentences = [];
                    $iSent = preg_match_all($pattern,$term->note,$sentences);
                    if($iSent>0){
                        foreach ($sentences[0] as  $sentence) {
                            $sentId = explode("-",trim($sentence,"{}"));
                            if(count($sentId) === 4){
                                $hasTran = Sentence::where('book_id',$sentId[0])
                                                    ->where('paragraph',$sentId[1])
                                                    ->where('word_start',$sentId[2])
                                                    ->where('word_end',$sentId[3])
                                                    ->exists();

                                $sentLen = Sentence::where('book_id',$sentId[0])
                                                    ->where('paragraph',$sentId[1])
                                                    ->where('word_start',$sentId[2])
                                                    ->where('word_end',$sentId[3])
                                                    ->where("channel_uid", $channelId)
                                                    ->value('strlen');
                                if($sentLen){
                                    $paliStrLen += $sentLen;
                                    if($hasTran){
                                        $tranStrLen += $sentLen;
                                    }
                                }
                            }
                        }
                    }
                }
                //计算该术语总得分
                $score["{$key}"] = $iExp*$noteStrLen;
            }

            $hotMeaning = DhammaTerm::selectRaw('meaning,count(*) as co')
                        ->where('word',$word->word)
                        ->whereIn('language',[$this->argument('lang'),$lang,$langFamily])
                        ->groupBy('meaning')
                        ->orderBy('co','desc')
                        ->first();
            if($hotMeaning){
                $bestNote = "";
                if(count($score)>0){
                    arsort($score);
                    $bestNote = $allTerm[(int)key($score)]->note;
                }

                $term = DhammaTerm::where('channal',$localTerm)->firstOrNew(
                        [
                            "word" => $word->word,
                            "tag" => $word->tag,
                            "channal" => $localTerm,
                        ],
                        [
                            'id' =>app('snowflake')->id(),
                            'guid' =>Str::uuid(),
                            'word_en' =>Tools::getWordEn($word->word),
                            'meaning' => '',
                            'language' => $this->argument('lang'),
                            'owner' => config("mint.admin.root_uuid"),
                            'editor_id' => 0,
                            'create_time' => time()*1000,
                        ]
                    );
                    $term->tag = $word->tag;
                    $term->meaning = $hotMeaning->meaning;
                    $term->note = $bestNote;
                    $term->modify_time = time()*1000;
                    $term->save();
            }
            $bar->advance();
        }
        $bar->finish();

        return 0;
    }
}
