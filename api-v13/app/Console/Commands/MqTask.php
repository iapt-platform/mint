<?php

namespace App\Console\Commands;

use App\Http\Api\Mq;
use App\Tools\Tools;
use Illuminate\Console\Command;

class MqTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mq:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'run task';

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
        if (Tools::isStop()) {
            return 0;
        }
        $this->info('env='.env('RABBITMQ_HOST'));
        $this->info('config='.config('queue.connections.rabbitmq.host'));
        $exchange = 'router';
        $queue = 'task';
        $this->info(" [*] Waiting for {$queue}. To exit press CTRL+C");
        Mq::worker($exchange, $queue, function ($message) {
            $message = json_decode(json_encode($message), true);
            $this->info('name=', $message['name']);

            return $this->call($message['name'], $message['param']);
        });

        return 0;
    }
}
