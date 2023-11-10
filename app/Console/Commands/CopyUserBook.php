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
     *
     * @var string
     */
    protected $signature = 'copy:user.book {--lang} {--book=}';

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
        $userBooks = CustomBookSentence::select('book')->groupBy('book')->get();
        $this->info('book '. count($userBooks));
        foreach ($userBooks as $key => $book) {
            $queryBook = $this->option('book');
            if(!empty($queryBook)){
                if($book->book != $queryBook){
                    continue;
                }
            }
            $this->info('doing book '. $book->book);
            $bookInfo = CustomBookSentence::where('book',$book->book)->first();
            $bookLang = $bookInfo->lang;
            //|| $bookLang === 'false' || $bookLang === 'null'
            if(empty($bookLang) || $bookLang === 'false' || $bookLang === 'null'){
                $this->info('language can not be empty change to pa, book='.$book->book);
                Log::warning('copy:user.book language can not be empty ,change to pa, book='.$book->book);
                $bookLang = 'pa';
            }
            $channelName = '_user_book_'.$bookLang;
            $channel = Channel::where('owner_uid',$bookInfo->owner)
                            ->where('name',$channelName)->first();
            if($channel === null){
                $channelUuid = Str::uuid();
                $channel = new Channel;
                $channel->id = app('snowflake')->id();
                $channel->uid = $channelUuid;
                $channel->owner_uid = $bookInfo->owner;
                $channel->name = $channelName;
                $channel->type = 'original';
                $channel->lang = $bookLang;
                $channel->editor_id = 0;
                $channel->create_time = time()*1000;
                $channel->modify_time = time()*1000;
                $channel->status = $bookInfo->status;
                $saveOk = $channel->save();
                if($saveOk){
                    Log::debug('copy user book : create channel success name='.$channelName);
                }else{
                    Log::error('copy user book : create channel fail.',['channel'=>$channelName,'book'=>$book->book]);
                    $this->error('copy user book : create channel fail.  name='.$channelName);
                    continue;
                }
            }
            if(!Str::isUuid($channel->uid)){
                Log::error('copy user book : channel id error.',['channel'=>$channelName,'book'=>$book->book]);
                $this->error('copy user book : channel id error.  name='.$channelName);
                continue;
            }
            CustomBook::where('book_id',$book->book)->update(['channel_id'=>$channel->uid]);

            $bar = $this->output->createProgressBar(CustomBookSentence::where('book',$book->book)
                                                    ->count());
            $bookSentence = CustomBookSentence::where('book',$book->book)->cursor();
            foreach ($bookSentence as $key => $sentence) {
                $bar->advance();
                $newRow = Sentence::firstOrNew(
                    [
                        "book_id" => $sentence->book,
                        "paragraph" => $sentence->paragraph,
                        "word_start" => $sentence->word_start,
                        "word_end" => $sentence->word_end,
                        "channel_uid" => $channel->uid,
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
                $newRow->language = $channel->lang;
                if(empty($newRow->channel_uid)){
                    $this->error('channel uuid is null book='.$sentence->book .' para='.$sentence->paragraph);
                    Log::error('channel uuid is null ',['sentence'=>$sentence->book]);
                }else{
                    $newRow->save();
                }

            }
            $bar->finish();
            $this->info("book {$book->book} finished");
        }
        return 0;
    }
}
