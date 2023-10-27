<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SentSim;
use App\Models\SentSimIndex;
use Illuminate\Support\Facades\DB;

class UpgradeSimIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:sim.index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '刷新相似句索引';

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
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
        $result = DB::select('select count(*) from (select sent1 from sent_sims where sim>0.5  group by sent1) T');
        $bar = $this->output->createProgressBar($result[0]->count);
        foreach (SentSim::selectRaw('sent1,count(*)')
                        ->where('sim','>',0.5)
                        ->groupBy('sent1')->cursor() as $sent) {
            SentSimIndex::updateOrInsert(
                ['sent_id'=>$sent->sent1],
                ['count'=>$sent->count,]);
            $bar->advance();
        }
        $bar->finish();
        return 0;
    }
}
