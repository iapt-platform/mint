<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

use App\Http\Api\ChannelApi;
use App\Models\DhammaTerm;
use App\Models\Relation;
use App\Tools\Tools;

class UpgradeGrammarBook extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan upgrade:grammar.book
     * @var string
     */
    protected $signature = 'upgrade:grammar.book';

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
        $lang = 'zh-Hans';
        $channelId = ChannelApi::getSysChannel('_System_Grammar_Term_'.strtolower($lang).'_');
        if($channelId === false){
            $this->error('no channel');
            return 1;
        }

        $relations = Relation::get();
        $result = [];
        foreach ($relations as $key => $relation) {
            $from = json_decode($relation->from,true);
            $words = [];
            if(isset($from['spell']) && !empty($from['spell'])){
                $words[] =  $from['spell'];
            }
            if(isset($from['case']) && count($from['case'])>0){
                $words = array_merge($words,$from['case']);
            }
            if(count($words)===0){
                continue;
            }
            $word = implode('.',$words);
            if(!isset($result[$word])){
                $result[$word] = array();
            }
            $result[$word][] = $relation;
        }

        foreach ($result as $key => $rows) {
            $this->info('## '.$key);

            $caseLocal = DhammaTerm::where('channal',$channelId)
                                ->where('word',$key)
                                ->value('meaning');
            if($caseLocal){
                $title = "**[[{$key}]]** 用法表\n\n";
            }else{
                $title = "**{$key}** 用法表\n\n";
            }
            $relations = [];
            foreach ($rows as $row) {
                if(!isset($relations[$row['name']])){
                    $relations[$row['name']] = array();
                    $local = DhammaTerm::where('channal',$channelId)
                                        ->where('word',$row['name'])
                                        ->first();
                    if($local){
                        $relations[$row['name']]['meaning']=$local->meaning;
                        $relations[$row['name']]['note']=$local->note;
                    }else{
                        $relations[$row['name']]['meaning']='';
                        $relations[$row['name']]['note']='';
                    }
                    $relations[$row['name']]['to']=array();
                }
                $relations[$row['name']]['to'][] = $row['to'];
            }
            $table = "|名称|解释|\n";
            $table .= "| -- | -- |\n";
            foreach ($relations as $relation => $value) {
                $table .= "| [[{$relation}]] | ". $value['note']." |\n";
            }
            $table .= "\n\n";
            echo $title.$table;
            //更新字典
            $newWord = $key.'.relations';
            $new = DhammaTerm::firstOrNew([
                                'channal' => $channelId,
                                'word' => $newWord,
                            ],[
                                'id'=>app('snowflake')->id(),
                                'guid'=>Str::uuid(),
                                'create_time'=>time()*1000,
                            ]);
            if(empty($caseLocal)){
                $caseLocal = $key;
            }
            $owner = ChannelApi::getById($channelId);
            if(!$owner){
                $this->error('channel id error '.$channelId);
                continue;
            }
            $new->word_en = strtolower($newWord);
            $new->meaning =$caseLocal.'用法表';
            $new->note = $table;
            $new->language = $lang;
            $new->editor_id = 1;
            $new->owner = $owner['studio_id'];
            $new->modify_time = time()*1000;
            $new->save();
        }

        return 0;
    }
}
