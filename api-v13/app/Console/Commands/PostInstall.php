<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PostInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:post-install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $this->info('post install start');

        // openSearch
        $this->call('create:opensearch.index');

        // rabbitmq
        $this->call('app:create-queue');

        // system channel
        $this->call('init:system.channel');

        // system dictionary
        $this->call('init:system.dict');

        $this->info('post install done');
    }
}
