<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomBookSentence;
use App\Models\CustomBook;

use App\Models\Channel;
use App\Models\Sentence;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CopyUserBook extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan copy:user.book
     * @var string
     */
    protected $signature = 'copy:user.book {--lang} {--book=} {--test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '复制用户自定书到sentence表';

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
        //获取全部语言列表
        $lang = CustomBookSentence::select('lang')->groupBy('lang')->get();
        foreach ($lang as $key => $value) {
            $this->info('language:'.$value->lang);
        }
        if($this->option('lang')){
            return 0;
        }

        if($this->option('test')){
            $this->info('run in test mode');
        }

        $this->info('给CustomBook 添加channel');
        $newChannel = 0;

        foreach (CustomBook::get() as $key => $customBook) {
            $this->info('doing book='.$customBook->book_id);
            if(empty($customBook->channel_id)){
                $bookLang = $customBook->lang;
                if(empty($bookLang) || $bookLang === 'false' || $bookLang === 'null'  || $bookLang === 'none'){
                    $this->info('language can not be empty change to pa, book='.$customBook->book_id);
                    Log::warning('copy:user.book language can not be empty ,change to pa, book='.$customBook->book_id);
                    $bookLang = 'pa';
                }
                $customBook->lang = $bookLang;
                $channelName = '_user_book_'.$bookLang;
                $channel = Channel::where('owner_uid',$customBook->owner)
                                ->where('name',$channelName)->first();
                if($channel === null){
                    $this->info('create new channel');
                    $channelUuid = Str::uuid();
                    $channel = new Channel;
                    $channel->id = app('snowflake')->id();
                    $channel->uid = $channelUuid;
                    $channel->owner_uid = $customBook->owner;
                    $channel->name = $channelName;
                    $channel->type = 'original';
                    $channel->lang = $bookLang;
                    $channel->editor_id = 0;
                    $channel->is_system = true;
                    $channel->create_time = time()*1000;
                    $channel->modify_time = time()*1000;
                    $channel->status = $customBook->status;
                    if(!$this->option('test')){
                        $saveOk = $channel->save();
                        if($saveOk){
                            $newChannel++;
                            Log::debug('copy user book : create channel success name='.$channelName);
                        }else{
                            Log::error('copy user book : create channel fail.',['channel'=>$channelName,'book'=>$customBook->book_id]);
                            $this->error('copy user book : create channel fail.  name='.$channelName);
                            continue;
                        }
                    }
                }
                if(!Str::isUuid($channel->uid)){
                    Log::error('copy user book : channel id error.',['channel'=>$channelName,'book'=>$customBook->book_id]);
                    $this->error('copy user book : channel id error.  name='.$channelName);
                    continue;
                }
                $customBook->channel_id = $channel->uid;
                if(!$this->option('test')){
                    $ok = $customBook->save();
                    if(!$ok){
                        Log::error('copy user book : create channel fail.',['book'=>$customBook->book_id]);
                        continue;
                    }
                }
            }
        }
        $this->info('给CustomBook 添加channel 结束');

        $userBooks = CustomBook::get();
        $this->info('book '. count($userBooks));
        $copySent = 0;
        foreach ($userBooks as $key => $book) {

            $queryBook = $this->option('book');
            if(!empty($queryBook)){
                if($book->book_id != $queryBook){
                    continue;
                }
            }
            if(empty($book->channel_id)){
                $this->error('book channel is empty');
                continue;
            }
            $this->info('doing book '. $book->book_id);

            $bookSentence = CustomBookSentence::where('book',$book->book_id)->cursor();
            foreach ($bookSentence as $key => $sentence) {
                $newRow = Sentence::firstOrNew(
                    [
                        "book_id" => $sentence->book,
                        "paragraph" => $sentence->paragraph,
                        "word_start" => $sentence->word_start,
                        "word_end" => $sentence->word_end,
                        "channel_uid" => $book->channel_id,
                    ],
                    [
                        'id' => app('snowflake')->id(),
                        'uid' => Str::uuid(),
                        'create_time' => $sentence->create_time,
                        'modify_time' => $sentence->modify_time,
                    ]
                    );
                $newRow->editor_uid = $sentence->owner;
                $newRow->content = $sentence->content;
                $newRow->strlen = mb_strlen($sentence->content,"UTF-8");
                $newRow->status = $sentence->status;
                $newRow->content_type = $sentence->content_type;
                $newRow->language = $book->lang;
                if(empty($newRow->channel_uid)){
                    $this->error('channel uuid is null book='.$sentence->book .' para='.$sentence->paragraph);
                    Log::error('channel uuid is null ',['sentence'=>$sentence->book]);
                }else{
                    if(!$this->option('test')){
                        $ok = $newRow->save();
                        if(!$ok){
                            Log::error('copy fail ',['sentence'=>$sentence->id]);
                        }
                        $copySent++;
                    }
                }
            }
            $this->info("book {$book->book} finished");
        }
        $this->info('all done ');
        $this->info('channel create '.$newChannel);
        $this->info('sentence copy '.$copySent);
        return 0;
    }
}
