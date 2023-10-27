<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WbwTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstallWbwTemplate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:wbwtemplate {from?} {to?}';

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
		$this->info("instert wbw template");


		$_from = $this->argument('from');
		$_to = $this->argument('to');
		if(empty($_from) && empty($_to)){
			$_from = 1;
			$_to = 217;
		}else if(empty($_to)){
			$_to = $_from;
		}
		$fileListFileName = public_path('/palihtml/filelist.csv');

		$filelist = array();

		if (($handle = fopen($fileListFileName, 'r')) !== false) {
			while (($filelist[] = fgetcsv($handle, 0, ',')) !== false) {
			}
		}
		$bar = $this->output->createProgressBar($_to-$_from+1);

		for ($from=$_from; $from <=$_to ; $from++) {
			# code...

			$fileSn = $from-1;
			$outputFileNameHead = $filelist[$fileSn][1];

			$dirXmlBase = public_path('/tmp/palicsv') . "/";
			$dirXml = $outputFileNameHead . "/";


			#删除目标数据库中数据
			WbwTemplate::where('book', $from)->delete();


			// 打开文件并读取数据

			if (($GLOBALS["fp"] = fopen($dirXmlBase . $dirXml . $outputFileNameHead . ".csv", "r")) !== false) {
				$GLOBALS["row"]=0;
				DB::transaction(function () {
					while (($data = fgetcsv($GLOBALS["fp"], 0, ',')) !== false) {
						$GLOBALS["row"]++;
						if($GLOBALS["row"]==1){
							continue;
						}
						#或略第一行 标题行
						$params = [
							'book'=>mb_substr($data[2], 1),
							'paragraph'=>$data[3],
							'wid'=>$data[16],
							'word'=>$data[4],
							'real'=>$data[5],
							'type'=>$data[6],
							'gramma'=>$data[7],
							'part'=>$data[10],
							'style'=>$data[15]
						];
						WbwTemplate::insert($params);
					}
				});
				fclose($GLOBALS["fp"]);
			} else {
				$this->error("can not open csv file. filename=" . $dirXmlBase . $dirXml . $outputFileNameHead . ".csv".PHP_EOL) ;
				Log::error("can not open csv file. filename=" . $dirXmlBase . $dirXml . $outputFileNameHead . ".csv".PHP_EOL) ;
			}

			$bar->advance();
		}
		$bar->finish();
        return 0;

	}
}
