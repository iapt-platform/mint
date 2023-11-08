<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Api\Mq;
use Illuminate\Support\Facades\Log;

class MqExport extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan mq:export
     * @var string
     */
    protected $signature = 'mq:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '导出功能用的消息队列';

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
        $queue = 'export';
        $this->info(" [*] Waiting for {$queue}. To exit press CTRL+C");
        Log::debug("mq:progress start.");
        Mq::worker($exchange,$queue,function ($message){
            $data = [
                        'book'=>$message->book,
                        'para'=>$message->para,
                        'channel'=>$message->channel,
                        '--format'=>$message->format,
                        'filename'=>$message->filename,
                    ];
            if(isset($message->origin) && is_string($message->origin)){
                $data['--origin'] = $message->origin;
            }
            if(isset($message->translation) && is_string($message->translation)){
                $data['--translation'] = $message->translation;
            }
            $ok = $this->call('export:chapter',$data);
            if($ok !== 0){
                Log::error('mq:progress upgrade:progress fail',$data);
            }else{
                $this->info("Received book=".$message->book.' result='.$ok);
                Log::debug("mq:export: done ",$data);
                return $ok;
            }
        });
        return 0;
    }
}
