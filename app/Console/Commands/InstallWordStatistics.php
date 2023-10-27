<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WordStatistic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstallWordStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:wordstatistics';

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
		$startTime = time();

		$info = "instert wordstatistics ";
		$this->info($info.PHP_EOL);
		Log::info($info);

		#删除目标数据库中数据
		WordStatistic::where('id', '>',-1)->delete();

		$scan = scandir(config("mint.path.word_statistics"));
		$bar = $this->output->createProgressBar(count($scan));
		foreach($scan as $filename) {
			$bar->advance();
			$filename = config("mint.path.word_statistics")."/".$filename;
			if (is_file($filename)) {
				Log::info("doing ".$filename);
				DB::transaction(function ()use($filename) {
				if (($fpoutput = fopen($filename, "r")) !== false) {
						$count = 0;
						while (($data = fgetcsv($fpoutput, 0, ',')) !== false) {
							$newData = [
								'bookid'=>$data[0],
								'word'=>$data[1],
								'count'=>$data[2],
								'base'=>$data[3],
								'end1'=>$data[4],
								'end2'=>$data[5],
								'type'=>$data[6],
								'length'=>$data[7],
							];
							WordStatistic::create($newData);
							$count++;
						}
						Log::info("insert ".$count);
					}
				});
			}
		}
		$bar->finish();
		$msg = "all done in ". time()-$startTime . "s";
		Log::info($msg);
		$this->info($msg);
        return 0;
        return 0;
    }
}
