<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Api\Mq;
use Illuminate\Support\Facades\Log;

class MqExportArticle extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan mq:export.article
     * @var string
     */
    protected $signature = 'mq:export.article';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '导出文章';

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
        $queue = 'export_article';
        $this->info(" [*] Waiting for {$queue}. To exit press CTRL+C");
        Log::debug("mq:export_article start.");
        Mq::worker($exchange,$queue,function ($message){
            $data = [
                        'id'=>$message->id,
                        '--format'=>$message->format,
                        'query_id'=>$message->queryId,
                        '--origin'=>$message->origin,
                        '--translation'=>$message->translation,
                    ];
            if(isset($message->token) && is_string($message->token)){
                $data['--token'] = $message->token;
            }
            if(isset($message->anthology) && is_string($message->anthology)){
                $data['--anthology'] = $message->anthology;
            }
            if(isset($message->channel) && is_string($message->channel)){
                $data['--channel'] = $message->channel;
            }
            $ok = $this->call('export:article',$data);
            if($ok !== 0){
                Log::error('mq:export.article fail',$data);
            }else{
                $this->info("Received article id=".$message->id.' result='.$ok);
                Log::debug("mq:export.article done ",$data);
                return $ok;
            }
        });

        return 0;
    }
}
