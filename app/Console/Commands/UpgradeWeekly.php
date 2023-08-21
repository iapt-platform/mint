<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpgradeWeekly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '周更';

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
        # 段落更新图
        $this->call('upgrade:chapterdynamic');
        $this->call('upgrade:chapter.dynamic.weekly');
        return 0;
    }
}
