<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Api\Mq;
use Illuminate\Support\Facades\Log;

class MqWbwAnalyses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mq:wbw.analyses';

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
        $queue = 'wbw-analyses';
        $this->info(" [*] Waiting for {$queue}. To exit press CTRL+C");
        Log::debug("mq:wbw.analyses start.");
        Mq::worker($exchange,$queue,function ($message){
            $data = ['id'=>implode(',',$message)];
            $ok = $this->call('upgrade:wbw.analyses',$data);
            if($ok === 0){
                $this->info("Received count=".count($message).' ok='.$ok);
                Log::debug('mq:wbw.analyses done count='.count($message));
            }else{
                Log::error('mq:wbw.analyses',$data);
            }
            return $ok;
        });

        return 0;
    }
}
