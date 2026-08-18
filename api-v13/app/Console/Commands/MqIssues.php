<?php

namespace App\Console\Commands;

use App\Http\Api\Mq;
use App\Tools\Tools;
use Illuminate\Console\Command;

class MqIssues extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan mq:issues
     *
     * @var string
     */
    protected $signature = 'mq:issues';

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
        if (Tools::isStop()) {
            return 0;
        }
        $exchange = 'router';
        $queue = 'issues';
        $this->info(" [*] Waiting for {$queue}. To exit press CTRL+C");
        Mq::worker($exchange, $queue, function ($message) {
            print_r($message);

            return 0;
        });

        return 0;
    }
}
