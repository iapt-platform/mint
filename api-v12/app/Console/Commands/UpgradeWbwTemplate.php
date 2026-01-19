<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaliSentence;
use App\Models\WbwTemplate;
use App\Models\Sentence;
use App\Http\Api\ChannelApi;
use Illuminate\Support\Str;

class UpgradeWbwTemplate extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan upgrade:wbw.template  129 509
     * @var string
     */
    protected $signature = 'upgrade:wbw.template {book?} {para?} {--debug=false}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'upgrade wbw template by sentence';

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
        $start = time();
		$pali = new PaliSentence;
		if(!empty($this->argument('book'))){
			$pali = $pali->where('book',$this->argument('book'));
		}
		if(!empty($this->argument('para'))){
			$pali = $pali->where('paragraph',$this->argument('para'));
		}
		$bar = $this->output->createProgressBar($pali->count());
        $channelId = ChannelApi::getSysChannel('_System_Wbw_VRI_');
        if($channelId===false){
            $this->error('no channel');
            return 1;
        }
        $this->info('channel id='.$channelId);
		$pali = $pali->select('book','paragraph','word_begin','word_end')->cursor();
		foreach ($pali as $value) {
			# code...
            $wbwContent=[];
			$words = WbwTemplate::where("book",$value->book)
								->where("paragraph",$value->paragraph)
								->where("wid",">=",$value->word_begin)
								->where("wid","<=",$value->word_end)
								->orderBy('wid','asc')
								->get();
			$sent = '';
            $sentId = $value->book.'-'.$value->paragraph.'-'.$value->word_begin.'-'.$value->word_end;
            if(count($words)===0){
                $this->error('no word data in'.$sentId);
            }
			foreach ($words as $wbw_word) {
                # code...
                $type = $wbw_word->type=='?'? '':$wbw_word->type;
                $grammar = $wbw_word->gramma=='?'? '':$wbw_word->gramma;
                $part = $wbw_word->part=='?'? '':$wbw_word->part;
                if(!empty($type) || !empty($grammar)){
                    $case = "{$type}#$grammar";
                }else{
                    $case = "";
                }
                $wbwContent[] = [
                    'sn'=>[$wbw_word->wid],
                    'word'=>['value'=>$wbw_word->word,'status'=>0],
                    'real'=> ['value'=>$wbw_word->real,'status'=>0],
                    'meaning'=> ['value'=>'','status'=>0],
                    'type'=> ['value'=>$type,'status'=>0],
                    'grammar'=> ['value'=>$grammar,'status'=>0],
                    'case'=> ['value'=>$case,'status'=>0],
                    'style'=> ['value'=>$wbw_word->style,'status'=>0],
                    'factors'=> ['value'=>$part,'status'=>0],
                    'factorMeaning'=> ['value'=>'','status'=>0],
                    'confidence'=> 1.0
                ];

            }
            $sent = \json_encode($wbwContent,JSON_UNESCAPED_UNICODE);
            if($this->option('debug')==='true'){
                $this->info($sentId.'='.$sent);
            }
			$newRow = Sentence::firstOrNew(
				[
					"book_id" => $value->book,
					"paragraph" => $value->paragraph,
					"word_start" => $value->word_begin,
					"word_end" => $value->word_end,
					"channel_uid" => $channelId,
				],
				[
					'id' =>app('snowflake')->id(),
					'uid' =>Str::uuid(),
				]
				);
            $newRow->editor_uid = config("mint.admin.root_uuid");
            $newRow->content = trim($sent);
            $newRow->content_type = "json";
            $newRow->strlen = mb_strlen($sent,"UTF-8");
            $newRow->status = 10;
            $newRow->create_time = time()*1000;
            $newRow->modify_time = time()*1000;
            $newRow->language = 'en';
            $newRow->save();

			$bar->advance();
		}
		$bar->finish();
		$this->info("finished ".(time()-$start)."s");
        return 0;
    }
}
