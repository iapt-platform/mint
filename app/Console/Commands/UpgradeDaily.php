<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpgradeDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每天的任务';

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
        $this->call('upgrade:palitext');
        $this->call('upgrade:palitextid');
        $this->call('upgrade:palitexttag');
        $this->call('upgrade:progress');
        $this->call('upgrade:progresschapter');
        $this->call('upgrade:wbwanalyses');
        return 0;
    }
}
