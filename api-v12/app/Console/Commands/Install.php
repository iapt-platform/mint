<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install {--test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'install new host';

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
		$isTest = $this->option('test');
		if($isTest){
			$this->call('install:wbwtemplate', ['from' => 1]);
		}else{
			$this->call('install:wbwtemplate');
			$this->call('install:palitext');
			$this->call('install:wordbook');
			$this->call('install:wordall');
			$this->call('install:wordindex');

			$this->call('upgrade:palitext');
			$this->call('upgrade:palitoc',['lang'=>'pali']);
			$this->call('upgrade:palitoc',['lang'=>'zh-hans']);
			$this->call('upgrade:palitoc',['lang'=>'zh-hant']);

			$this->call('install:paliseries');
			$this->call('install:wordstatistics');

		}

        return 0;
    }
}
