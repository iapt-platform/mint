<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Api\Mq;

class MqProgress extends Command
{
    /**
     * The name and signature of the console command.
     *
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
        $exchange = 'router';
        $queue = 'progress';
        $this->info(" [*] Waiting for {$queue}. To exit press CTRL+C");
        Mq::worker($exchange,$queue,function ($message){
            $ok1 = $this->call('upgrade:progress',['--book'=>$message->book,
                                            '--para'=>$message->para,
                                            '--channel'=>$message->channel,
                                            ]);
            $ok2 = $this->call('upgrade:progress.chapter',['--book'=>$message->book,
                                                '--para'=>$message->para,
                                                '--channel'=>$message->channel,
                                                ]);
            $this->info("Received book=".$message->book.' progress='.$ok1.' chapter='.$ok2);
            return $ok1+$ok2;
        });
        return 0;

    }
}