<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Api\Mq;
use Illuminate\Support\Facades\Log;

class MqExportPaliChapter extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan mq:export.pali.chapter
     * @var string
     */
    protected $signature = 'mq:export.pali.chapter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '导出巴利文章节';

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
        $exchange = 'router';
        $queue = 'export_pali_chapter';
        $this->info(" [*] Waiting for {$queue}. To exit press CTRL+C");
        Log::debug("mq:export_pali_chapter start.");
        Mq::worker($exchange,$queue,function ($message){
            $data = [
                        'book'=>$message->book,
                        'para'=>$message->para,
                        'channel'=>$message->channel,
                        '--format'=>$message->format,
                        'query_id'=>$message->queryId,
                    ];
            if(isset($message->origin) && is_string($message->origin)){
                $data['--origin'] = $message->origin;
            }
            if(isset($message->translation) && is_string($message->translation)){
                $data['--translation'] = $message->translation;
            }
            if(isset($message->token) && is_string($message->token)){
                $data['--token'] = $message->token;
            }
            $ok = $this->call('export:chapter',$data);
            if($ok !== 0){
                Log::error('mq:export.pali.chapter upgrade:progress fail',$data);
            }else{
                $this->info("Received book=".$message->book.' result='.$ok);
                Log::debug("mq:export.pali.chapter done ",$data);
                return $ok;
            }
        });
        return 0;
    }
}
