<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class InitDependence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:dep';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'init dependence date - pali sencence ect.';

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
		#克隆依赖的数据到本地

		$process = new Process(['git','submodule','update','--remote']);
		$process->run();
		if(!$process->isSuccessful()){
			throw new ProcessFailedException($process);
		}
		$this->info($process->getOutput());	
        return 0;
    }
}
