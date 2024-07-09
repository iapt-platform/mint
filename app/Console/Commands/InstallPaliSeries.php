<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BookTitle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstallPaliSeries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:pali.series';

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
		$this->info("upgrade pali serieses");
		$startTime = time();

		DB::transaction(function () {
			#删除目标数据库中数据
			BookTitle::where('book','>',0)->delete();

		// 打开csv文件并读取数据
			$strFileName = config("mint.path.pali_title") . "/pali_serieses.csv";
			if(!file_exists($strFileName)){
				return 1;
			}
			$inputRow = 0;
			if (($fp = fopen($strFileName, "r")) !== false) {
				while (($data = fgetcsv($fp, 0, ',')) !== false) {
					if($inputRow>0){
						$newData = [
							'sn'=>$data[0],
							'book'=>$data[1],
							'paragraph'=>$data[2],
							'title'=>$data[3],
						];

						BookTitle::create($newData);
					}
					$inputRow++;
				}
				fclose($fp);
				Log::info("res load：" .$strFileName);
			} else {
				$this->error("can not open csv $strFileName");
				Log::error("can not open csv $strFileName");
			}
		});
		$this->info("ok");
        return 0;
    }
}
