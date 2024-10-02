<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaliSentence;
use App\Models\WbwTemplate;
use App\Models\Sentence;
use App\Http\Api\ChannelApi;
use Illuminate\Support\Str;

class UpgradeQuote extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:quote {book?}';

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
        $start = time();
        $channelId = ChannelApi::getSysChannel('_System_Quote_');
        if($channelId===false){
            $this->error('no channel');
            return 1;
        }


        for ($i=1; $i <= 217; $i++) {
            if(!empty($this->argument('book'))){
                if($i != $this->argument('book')){
                    continue;
                }
            }
            $pts_book=0;
            $pts_page=0;
            $myanmar_book=0;
            $myanmar_page=0;
            $cs_para=0;

            $pali = PaliSentence::where('book',$i);
            $bar = $this->output->createProgressBar(PaliSentence::where('book',$i)->count());
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
                foreach ($words as $wbw_word) {
                    # code...
                    if($wbw_word->type === '.ctl.'){
                        if(substr($wbw_word->word,0,1) === 'M'){
                            $m = explode('.',substr($wbw_word->word,1));
                            $myanmar_book=(int)$m[0];
                            $myanmar_page=(int)$m[1];
                        }
                        if(substr($wbw_word->word,0,1) === 'P'){
                            $m = explode('.',substr($wbw_word->word,1));
                            $pts_book=(int)$m[0];
                            $pts_page=(int)$m[1];
                        }
                    }
                    if($wbw_word->style === 'paranum'){
                        $cs_para=(int)$wbw_word->word;
                    }
                }
                $wbwContent = [
                    'pts_book'=>$pts_book,
                    'pts_page'=>$pts_page,
                    'myanmar_book'=> $myanmar_book,
                    'myanmar_page'=> $myanmar_page,
                    'cs_para'=> $cs_para,
                ];
                $sent = \json_encode($wbwContent,JSON_UNESCAPED_UNICODE);

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
        }



		$this->info("finished ".(time()-$start)."s");
        return 0;
    }
}
