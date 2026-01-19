<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Api\Mq;
use Illuminate\Support\Facades\Log;

class MqProgress extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan mq:progress
     * @var string
     */
    protected $signature = 'mq:progress';

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
        $exchange = 'router';
        $queue = 'progress';
        $this->info(" [*] Waiting for {$queue}. To exit press CTRL+C");
        Log::debug("mq:progress start.");
        Mq::worker($exchange,$queue,function ($message){
            $data = [
                        '--book'=>$message->book,
                        '--para'=>$message->para,
                        '--channel'=>$message->channel,
                    ];
            $ok1 = $this->call('upgrade:progress',$data);
            if($ok1 !== 0){
                Log::error('mq:progress upgrade:progress fail',$data);
            }
            $ok2 = $this->call('upgrade:progress.chapter',$data);
            if($ok2 !== 0){
                Log::error('mq:progress upgrade:progress.chapter fail',$data);
            }
            $this->info("Received book=".$message->book.' progress='.$ok1.' chapter='.$ok2);
            Log::debug("mq:progress: done book=".$message->book.' progress='.$ok1.' chapter='.$ok2);
            return $ok1+$ok2;
        });
        return 0;

    }
}
