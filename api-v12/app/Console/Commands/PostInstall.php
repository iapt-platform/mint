<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeplyInit extends Command
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
        $this->info('deploy init start');
        $this->call('create:opensearch.index');
        $this->info('deploy init done');
    }
}
