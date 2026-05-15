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

        $this->call('create:opensearch.index');
        $this->call('init:system.channel');
        $this->call('init:system.dict');

        $this->info('post install done');
    }
}
