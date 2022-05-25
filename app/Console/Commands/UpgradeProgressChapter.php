<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sentence;
use App\Models\PaliSentence;
use App\Models\Progress;
use App\Models\ProgressChapter;
use App\Models\PaliText;
use App\Models\Tag;
use App\Models\TagMap;

class UpgradeProgressChapter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:progresschapter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新章节完成度，以channel为单位';

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
        $tagCount=0;
        #第一步 查询有多少书有译文
		$books = Sentence::where('strlen','>',0)
                          ->where('book_id','<',1000)
                          ->where('channel_uid','<>','')
                          ->groupby('book_id')
                          ->select('book_id')
                          ->get();

        $bar = $this->output->createProgressBar(count($books));

        foreach ($books as $book) {
            # code...
            $chapters = PaliText::where('book',$book->book_id)
                                ->where('level','>',0)
                                ->where('level','<',8)
                                ->select('paragraph','chapter_strlen','chapter_len')
                                ->get();
            foreach ($chapters as $key => $chapter) {
                # code...
                $chapter_strlen = PaliSentence::where('book',$book->book_id)
                                    ->whereBetween('paragraph',[$chapter->paragraph,$chapter->paragraph+$chapter->chapter_len-1])
                                    ->sum('length');
                if($chapter_strlen == 0){
                    $this->error('chapter_strlen is 0 book:'.$book->book_id.' paragraph:'.$chapter->paragraph.'-'.($chapter->paragraph+$chapter->chapter_len-1));
                    continue;
                }
                $strlen = Progress::where('book',$book->book_id)
                                  ->whereBetween('para',[$chapter->paragraph,$chapter->paragraph+$chapter->chapter_len-1])
                                  ->groupby('channel_id')
                                  ->selectRaw('channel_id, sum(all_strlen) as cp_len')
                                  ->get();
                foreach ($strlen as $final) {
                    # code...
                    # 计算此段落完成时间
                    $finalAt = Progress::where('book',$book->book_id)
                                ->whereBetween('para',[$chapter->paragraph,$chapter->paragraph+$chapter->chapter_len-1])
                                ->where('channel_id',$final->channel_id)
                                ->max('created_at');
                    $updateAt = Progress::where('book',$book->book_id)
                                ->whereBetween('para',[$chapter->paragraph,$chapter->paragraph+$chapter->chapter_len-1])
                                ->where('channel_id',$final->channel_id)
                                ->max('updated_at');
                    #查询标题
                    $title = Sentence::where('book_id',$book->book_id)
                          ->where('paragraph',$chapter->paragraph)
                          ->where('channel_uid',$final->channel_id)
                          ->value('content');


                    $chapterData = ProgressChapter::updateOrCreate(
                        [
                            'book'=>$book->book_id,
                            'para'=>$chapter->paragraph,
                            'channel_id'=>$final->channel_id
                        ],
                        [
                            'lang'=>'en',
                            'all_trans'=>$final->cp_len/$chapter_strlen,
                            'public'=>$final->cp_len/$chapter_strlen,
                            'progress'=>$final->cp_len/$chapter_strlen,
                            'title'=>mb_substr($title,0,255,"UTF-8"),
                            'created_at'=>$finalAt,
                            'updated_at'=>$updateAt,
                        ]);
                    
                    #查询路径
                    $path = json_decode(
                                PaliText::where('book',$book->book_id)
                                ->where('paragraph',$chapter->paragraph)
                                ->value('path'));
                    
                    if($path){
                        //查询标签
                        $tags = [];
                        foreach ($path as $key => $value) {
                            # code...
                            if($value->level>0){
                                $paliTextUuid = PaliText::where('book',$value->book)
                                                ->where('paragraph',$value->paragraph)
                                                ->value('uid');
                                $tagUuids = TagMap::where('table_name','pali_texts')
                                                ->where('anchor_id',$paliTextUuid)
                                                ->select(['tag_id'])
                                                ->get();
                                foreach ($tagUuids as $key => $taguuid) {
                                    # code...
                                    $tags[$taguuid['tag_id']]=1;
                                }
                                
                            }
                        }

                        //更新标签映射表
                        foreach ($tags as $key => $tag) {
                            # code...
                            $tagmap = TagMap::firstOrCreate([
                                        'table_name' => 'progress_chapters',
                                        'anchor_id' => $chapterData->uid,
                                        'tag_id' => $key
                                    ]);
                            if($tagmap){
                                $tagCount++;
                            }
                        }
                    }
                }
            }
            $bar->advance();
        }
        $bar->finish();
        $this->info("tag count:".$tagCount);
        return 0;
    }
}
