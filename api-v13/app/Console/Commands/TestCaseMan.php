<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Tools\CaseMan;
use App\Models\UserDict;


class TestCaseMan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:case {word}';

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
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
		$caseMan = new CaseMan();
        $case = $caseMan->Declension($this->argument('word'),'.n:base.','.nt.',0.5);
        print_r($case);
        return 0;

		$parents = $caseMan->WordToBase($this->argument('word'),1);
			# code...

		foreach ($parents as $base => $rows) {
			# code...
			if(count($rows)==0){
				$this->error("base={$base}-(".count($rows).")");
			}else{
				$this->warn("base={$base}-(".count($rows).")");
			}

			foreach ($rows as $value) {
				# code...
				$this->info($value['word'].'-'.$value['type'].'-'.$value['grammar'].'-'.$base);
			}
		}
        return 0;
    }
}
