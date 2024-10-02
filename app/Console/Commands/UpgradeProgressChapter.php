<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Validator;
use Illuminate\Console\Command;
use App\Models\Sentence;
use App\Models\PaliSentence;
use App\Models\Progress;
use App\Models\ProgressChapter;
use App\Models\PaliText;
use App\Models\Tag;
use App\Models\TagMap;
use App\Models\Channel;
use App\Http\Api\MdRender;

class UpgradeProgressChapter extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan upgrade:progress.chapter --book=168 --para=915 --channel=19f53a65-81db-4b7d-8144-ac33f1217d34
     * @var string
     */
    protected $signature = 'upgrade:progress.chapter  {--book=} {--para=} {--channel=} {--driver=str}';

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
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
		$this->info("upgrade:progresschapter start.");
		$startTime = time();
        $book = $this->option('book');
        $para = $this->option('para');
        $channelId = $this->option('channel');

        \App\Tools\Markdown::driver($this->option('driver'));

        $tagCount=0;
        #第一步 查询有多少书有译文
        if($book){
            $books = Sentence::where('book_id',$book)
                            ->groupby('book_id')
                            ->select('book_id')
                            ->get();
        }else{
            $books = Sentence::where('strlen','>',0)
                ->where('book_id','<',1000)
                ->where('channel_uid','<>','')
                ->groupby('book_id')
                ->select('book_id')
                ->get();
        }


        $bar = $this->output->createProgressBar(count($books));

        foreach ($books as $book) {
            if($para){
                $table = PaliText::where('book',$book->book_id)
                            ->where('paragraph','<=',$para);
            }else{
                $table = PaliText::where('book',$book->book_id);
            }
            $chapters = $table->where('level','>',0)
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
                $table = Progress::where('book',$book->book_id)
                                  ->whereBetween('para',[$chapter->paragraph,$chapter->paragraph+$chapter->chapter_len-1]);
                if($channelId){
                    $table->where('channel_id',$channelId);
                }
                $strlen = $table->groupby('channel_id')
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
                    $transTexts = Sentence::where('book_id',$book->book_id)
                                ->whereBetween('paragraph',[$chapter->paragraph+1,$chapter->paragraph+$chapter->chapter_len-1])
                                ->where('channel_uid',$final->channel_id)
                                ->select('content')
                                ->orderBy('paragraph')
                                ->orderBy('word_start')
                                ->get();

                    $mdRender = new MdRender(['format'=>'simple']);

                    #查询标题
                    $title = Sentence::where('book_id',$book->book_id)
                          ->where('paragraph',$chapter->paragraph)
                          ->where('channel_uid',$final->channel_id)
                          ->value('content');
                    $title = $mdRender->convert($title,[$final->channel_id]);

                    $summaryText = "";
                    foreach ($transTexts as $text) {
                        # code...
                        $textContent = $mdRender->convert($text->content,[$final->channel_id]);
                        $summaryText .= str_replace("\n","",$textContent);
                        if(mb_strlen($summaryText,"UTF-8")>255){
                            break;
                        }
                    }

                    //查询语言
                    $channelLang = Channel::where('uid',$final->channel_id)->value('lang');
                    $lang = explode('-',$channelLang)[0];
                    $attributes = [
                            'book'=>$book->book_id,
                            'para'=>$chapter->paragraph,
                            'channel_id'=>$final->channel_id];

                    $rules = array(
                        'book' => 'integer',
                        'para' => 'integer',
                        'channel_id' => 'uuid'
                    );

                    $validator = Validator::make($attributes, $rules);
                    if ($validator->fails()) {
                        $this->error("Validator is fails");
                        return 0;
                    }
                    if(ProgressChapter::where($attributes)->exists()){
                        $chapterData = ProgressChapter::where($attributes)->first();
                    }else{
                        $chapterData = new ProgressChapter;
                        $chapterData->book = $attributes["book"];
                        $chapterData->para = $attributes["para"];
                        $chapterData->channel_id = $attributes["channel_id"];

                    }
                    $chapterData->lang = $lang;
                    $chapterData->all_trans = $final->cp_len/$chapter_strlen;
                    $chapterData->public = $final->cp_len/$chapter_strlen;
                    $chapterData->progress = $final->cp_len/$chapter_strlen;
                    $chapterData->title = $title? mb_substr($title,0,255,"UTF-8"):"";
                    $chapterData->summary = $summaryText? mb_substr($summaryText,0,255,"UTF-8"):"";
                    $chapterData->created_at = $finalAt;
                    $chapterData->updated_at = $updateAt;
                    $chapterData->save();

                    $wasCreated = $chapterData->wasRecentlyCreated;
                    $wasChanged = $chapterData->wasChanged();
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
                        //删除旧的标签映射表
                        TagMap::where('table_name' , 'progress_chapters')
                                ->where('anchor_id' , $chapterData->uid)
                                ->delete();
                        foreach ($tags as $key => $tag) {
                            # code...
                            $tagmap = TagMap::create([
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
		$time = time() - $startTime;
		$this->info("upgrade:progresschapter finished in {$time}s tag count:{$tagCount}");
        return 0;
    }
}
