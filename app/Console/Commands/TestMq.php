<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Http\Api\Mq;

class TestMq extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:mq';

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
        //一对一
		//Mq::publish('wbw-analyses',[13607982709477376]);
		Mq::publish('hello',['hello world']);

        //一对多
        /*
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USERNAME, MQ_PASSWORD);
        $channel->exchange_declare('hello_exchange','fanout',false,true);
        $channel->queue_declare('hello', false, true, false, false);
        $channel->exchange_bind('hello','exchange',"");
*/
        return 0;
    }
}
