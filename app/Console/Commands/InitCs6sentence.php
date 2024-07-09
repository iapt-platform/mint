<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaliSentence;
use App\Models\WbwTemplate;
use App\Models\Sentence;
use Illuminate\Support\Str;
use App\Http\Api\ChannelApi;


class InitCs6sentence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:cs6sentence {book?} {para?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '按照分句数据库，填充cs6的巴利原文句子';

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
        $channelId = ChannelApi::getSysChannel('_System_Pali_VRI_');
        if($channelId === false){
            $this->error('no channel');
            return 1;
        }
        $this->info($channelId);
		$pali = new PaliSentence;
		if(!empty($this->argument('book'))){
			$pali = $pali->where('book',$this->argument('book'));
		}
		if(!empty($this->argument('para'))){
			$pali = $pali->where('paragraph',$this->argument('para'));
		}
		$bar = $this->output->createProgressBar($pali->count());
		$pali = $pali->select('book','paragraph','word_begin','word_end')->cursor();
        $pageHead = ['M','P','T','V','O'];
		foreach ($pali as $value) {
			# code...
			$words = WbwTemplate::where("book",$value->book)
								->where("paragraph",$value->paragraph)
								->where("wid",">=",$value->word_begin)
								->where("wid","<=",$value->word_end)
								->orderBy('wid','asc')
								->get();
			$sent = '';
			$boldStart = false;
			$boldCount = 0;
            $lastWord = null;
			foreach ($words as $word) {
				# code...
				//if($word->style != "note" && $word->type != '.ctl.')
				if( $word->type != '.ctl.'){
                    if($lastWord !== null){
                        if($word->real !== "ti" ){

                            if(!(empty($word->real) && empty($lastWord->real))) {
                                    #如果不是标点符号，在词的前面加空格 。
                                $sent .= " ";
                            }
                        }
                    }

					if(strpos($word->word,'{') !== false ){
                        //一个单词里面含有黑体字的
						$paliWord = \str_replace("{","<strong>",$word->word) ;
						$paliWord = \str_replace("}","</strong>",$paliWord) ;
                        $sent .= $paliWord;
					}else{
                        if($word->style=='bld'){
                            $sent .= "<strong>{$word->word}</strong>";
                        }else{
                            $sent .= $word->word;
                        }
					}

				}else{
                    $type = substr($word->word,0,1);
                    if(in_array($type,$pageHead)){
                        $arrPage = explode('.',$word->word);
                        if(count($arrPage)===2){
                            $pageNumber = $arrPage[0].'.'.(int)$arrPage[1];
                           $sent .= "<code>{$pageNumber}</code>";
                        }
                    }
                }
                $lastWord = $word;
			}

			#将wikipali风格的引用 改为缅文风格
            /*
			$sent = \str_replace('n’’’ ti','’’’nti',$sent);
			$sent = \str_replace('n’’ ti','’’nti',$sent);
			$sent = \str_replace('n’ ti','’nti',$sent);
			$sent = \str_replace('**ti**','**ti',$sent);
			$sent = \str_replace('‘ ','‘',$sent);
            */
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
                    'create_time' => time()*1000,
				]
				);
            $newRow->editor_uid = config("mint.admin.root_uuid");
            $newRow->content = "<span>{$sent}</span>";
            $newRow->strlen = mb_strlen($sent,"UTF-8");
            $newRow->status = 10;
            $newRow->content_type = "html";
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
