<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sentence;
use App\Models\SentHistory;
use App\Models\Discussion;

use Illuminate\Support\Facades\DB;

class UpdateSentenceUnique extends Command
{
    /**
     * 将channel+book+paragraph+start+end重复的数据筛查，合并
     * 与此句相关的资源也要合并，包括，pr,history,discussion
     * 多的句子软删除
     * php artisan update:sentence.unique
     * @var string
     */
    protected $signature = 'update:sentence.unique';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将sentence中的重复数据合并';

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
        $queryCount = "SELECT count(*) from (SELECT * from (SELECT book_id ,paragraph ,word_start ,word_end ,channel_uid , count(*) as co from sentences s where ver = 2  group by book_id ,paragraph ,word_start ,word_end ,channel_uid) T where co>1) TT ";
        $total = DB::select($queryCount);
        $querySame = "SELECT * from (SELECT book_id ,paragraph ,word_start ,word_end ,channel_uid , count(*) as co from sentences s where ver = 2  group by book_id ,paragraph ,word_start ,word_end ,channel_uid) T where co>1";
        $query = DB::select($querySame);

        $count = 0;
        foreach ($query as $key => $value) {
            $count++;
            $same = Sentence::where('book_id',$value->book_id)
                            ->where('paragraph',$value->paragraph)
                            ->where('word_start',$value->word_start)
                            ->where('word_end',$value->word_end)
                            ->where('channel_uid',$value->channel_uid)
                            ->orderBy('updated_at','desc')
                            ->get();
            $per = (int)($count*100 / $total[0]->count);
            $this->info("[{$per}]-{$count} ".$same[0]->updated_at.' '.$same[1]->updated_at.' '.count($same));

            for ($i=1; $i < count($same); $i++) {
                //将旧数据的历史记录 重新定位到新数据
                $history = SentHistory::where('sent_uid',$same[$i]->uid)
                                      ->update(['sent_uid'=>$same[0]->uid]);
                //将旧数据的discussion 重新定位到新数据
                $discussion = Discussion::where('res_id',$same[$i]->uid)
                                        ->update(['res_id'=>$same[0]->uid]);
                $this->info("{$history}-$discussion");
                //将旧数据的 pr 重新定位到新数据
                //删除旧数据
                $same[$i]->delete();
                if($same[$i]->trashed()){
                    $this->info('软删除成功！');
                }else{
                    $this->error('软删除失败！');
                }
            }

            if($count >= 1){
                break;
            }
        }
        return 0;
    }
}
