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
    protected $signature = 'install {--queue}';

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
		$this->call('install:wbwtemplate', [
			'from' => 1, 'to' => 217
		]);
        return 0;
    }
}
