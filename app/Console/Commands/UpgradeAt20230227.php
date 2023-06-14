<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpgradeAt20230227 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:at20230227';

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
        $this->call('init:system.channel');
        $this->call('init:system.dict');
        $this->call('upgrade:dict');
        $this->call('upgrade:dict.vocabulary');
        $this->call('upgrade:dict.default.meaning');
        $this->call('upgrade:related.paragraph');
        $this->call('upgrade:fts',['--content'=>true]);
        $this->call('upgrade:pcd.book.id');

        return 0;
    }
}
