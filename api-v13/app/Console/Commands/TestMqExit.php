<?php

namespace App\Console\Commands;

use App\Http\Api\Mq;
use Illuminate\Console\Command;

class TestMqExit extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan test:mq.exit
     *
     * @var string
     */
    protected $signature = 'test:mq.exit';

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
        for ($i = 0; $i < 10; $i++) {
            Mq::publish('ai_translate', ['hello world']);
        }

        return 0;
    }
}
