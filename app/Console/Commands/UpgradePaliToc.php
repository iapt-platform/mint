<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ResIndex;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpgradePaliToc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:palitoc {lang} {from?} {to?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'upgrade pali toc from csv';
    protected $usage = 'upgrade:palitoc lang from to';

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
		$this->info("upgrade pali text");
		$startTime = time();

		$_lang = $this->argument('lang');
		$_from = $this->argument('from');
		$_to = $this->argument('to');
		if(empty($_from) && empty($_to)){
			$_from = 1;
			$_to = 217;
		}else if(empty($_to)){
			$_to = $_from;
		}
		if ($_lang == "pali") {
			$type = 1;
		} else {
			$type = 2;
		}

		$bar = $this->output->createProgressBar($_to-$_from+1);
		for ($from=$_from; $from <= $_to; $from++) {
			// 打开csv文件并读取数据
			$strFileName = config("mint.path.pali_title") . "/{$from}_{$_lang}.csv";
			if(!file_exists($strFileName)){
				continue;
			}
			#删除目标数据库中数据
			ResIndex::where('book', $from)
					->where('language', $_lang)
					->delete();
			DB::transaction(function ()use($from,$strFileName,$type,$_lang) {
				$inputRow = 0;
				if (($fp = fopen($strFileName, "r")) !== false) {
					while (($data = fgetcsv($fp, 0, ',')) !== false) {
						if ($inputRow > 0 && $data[3] != 100 && !empty($data[6])) {
							if (isset($data[7])) {
								$author = $data[7];
							}else {
								$author = "cscd4";
							}
							$data[6] = mb_substr($data[6],0,1024);

							$newData = [
								'book'=>$from,
								'paragraph'=>$data[2],
								'title'=>$data[6],
								'title_en'=>$this->getWordEn($data[6]),
								'level'=>$data[3],
								'type'=>$type,
								'language'=>$_lang,
								'author'=>$author,
								'share'=>1,
								'create_time'=>time()*1000,
								'update_time'=>time()*1000,
							];

							ResIndex::create($newData);
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
			$bar->advance();
		}
		$bar->finish();
		$msg = "upgrade pali toc finished. in ". time()-$startTime . "s";
		$this->info($msg .PHP_EOL);
		Log::info($msg);
        return 0;
    }

	private function  getWordEn($strIn)
	{
		$strIn = strtolower($strIn);
		$search = array('ā', 'ī', 'ū', 'ṅ', 'ñ', 'ṭ', 'ḍ', 'ṇ', 'ḷ', 'ṃ');
		$replace = array('a', 'i', 'u', 'n', 'n', 't', 'd', 'n', 'l', 'm');
		return (str_replace($search, $replace, $strIn));
	}
}
