<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WordIndex;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstallWordIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:wordindex';

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

		$info = "instert word in palibook ";
		$this->info($info);
		Log::info($info);

		#删除目标数据库中数据
		WordIndex::where('id', '>',-1)->delete();

		$scan = scandir(config("mint.path.paliword_index"));
		$bar = $this->output->createProgressBar(count($scan));
		foreach($scan as $filename) {
			$bar->advance();
			$filename = config("mint.path.paliword_index")."/".$filename;
			if (is_file($filename)) {
				Log::info("doing ".$filename);
				DB::transaction(function ()use($filename) {
				if (($fpoutput = fopen($filename, "r")) !== false) {
						$count = 0;
						while (($data = fgetcsv($fpoutput, 0, ',')) !== false) {
							$newData = [
								'id'=>$data[0],
								'word'=>$data[1],
								'word_en'=>$data[2],
								'normal'=>$data[3],
								'bold'=>$data[4],
								'is_base'=>$data[5],
								'len'=>$data[6],
							];
							WordIndex::create($newData);
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
    }
}
