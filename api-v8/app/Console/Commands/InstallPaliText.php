<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaliText;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstallPaliText extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:palitext {from?} {to?}';

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
		$this->info("instert pali text");
		$startTime = time();

		$_from = $this->argument('from');
		$_to = $this->argument('to');
		if(empty($_from) && empty($_to)){
			$_from = 1;
			$_to = 217;
		}else if(empty($_to)){
			$_to = $_from;
		}
		$fileListFileName = config("mint.path.palitext_filelist");

		$filelist = array();

		if (($handle = fopen($fileListFileName, 'r')) !== false) {
			while (($filelist[] = fgetcsv($handle, 0, ',')) !== false) {
			}
		}
		$bar = $this->output->createProgressBar($_to-$_from+1);

		for ($from=$_from; $from <=$_to ; $from++) {
			# code...

			$fileSn = $from-1;
			$FileName = $filelist[$fileSn][1];

			$dirXmlBase = config("mint.path.palicsv") . "/";
			$GLOBALS['data'] = array();

			// 打开vri html文件并读取数据
			$pali_text_array = array();
			$htmlFile = config("mint.path.palitext") .'/'. $FileName.'.htm';
			if (($fpPaliText = fopen($htmlFile, "r")) !== false) {
				while (($data = fgets($fpPaliText)) !== false) {
					if (substr($data, 0, 2) === "<p") {
						array_push($pali_text_array, $data);
					}
				}
				fclose($fpPaliText);
				//$this->info("pali text load：" . $htmlFile . PHP_EOL);
			} else {
				$this->error( "can not pali text file. filename=" . $htmlFile . PHP_EOL) ;
				Log::error( "can not pali text file. filename=" . $htmlFile . PHP_EOL) ;
			}

			$inputRow = 0;
			$csvFile = config("mint.path.palicsv") .'/'. $FileName .'/'. $FileName.'_pali.csv';
			if (($fp = fopen($csvFile, "r")) !== false) {
				while (($data = fgetcsv($fp, 0, ',')) !== false) {
					if ($inputRow > 0) {
						if (($inputRow - 1) < count($pali_text_array)) {
							$data[5] = $pali_text_array[$inputRow - 1];
						}
						$data[1] = mb_substr($data[1],1,null,"UTF-8");
						$GLOBALS['data'][] = $data;
					}
					$inputRow++;
				}
				fclose($fp);
				//$this->info("单词表load：" . $csvFile.PHP_EOL);
			} else {
				$this->error( "can not open csv file. filename=" . $csvFile. PHP_EOL) ;
				Log::error( "can not open csv file. filename=" . $csvFile. PHP_EOL) ;
				continue;
			}

			if (($inputRow - 1) != count($pali_text_array)) {
				$this->error( "line count error $FileName ".PHP_EOL);
				Log::error( "line count error $FileName ".PHP_EOL);
			}


			#删除目标数据库中数据
			PaliText::where('book', $from)->delete();


			// 打开文件并读取数据


			DB::transaction(function () {
				foreach ($GLOBALS['data'] as $oneParam) {
					if ($oneParam[3] < 100) {
						$toc = $oneParam[6];
					} else {
						$toc = "";
					}
					$params = [
						'book'=>$oneParam[1],
						'paragraph'=>$oneParam[2],
						'level'=>$oneParam[3],
						'class'=> $oneParam[4],
						'toc'=>$toc,
						'text'=>$oneParam[6],
						'html'=>$oneParam[5],
						'lenght'=>mb_strlen($oneParam[6], "UTF-8"),
					];
					PaliText::create($params);
				}

			});

			$bar->advance();
		}
		$bar->finish();
		$this->info("instert pali text finished. in ". time()-$startTime . "s" .PHP_EOL);

        return 0;

    }
}
