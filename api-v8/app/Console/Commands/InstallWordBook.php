<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BookWord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstallWordBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:wordbook {from?} {to?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'install palibook word list in each book';

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

		$this->info("instert word in palibook ");
		Log::info("instert word in palibook ");

		$_from = $this->argument('from');
		$_to = $this->argument('to');
		if(empty($_from) && empty($_to)){
			$_from = 1;
			$_to = 217;
		}else if(empty($_to)){
			$_to = $_from;
		}

		$bar = $this->output->createProgressBar($_to-$_from+1);

		for ($book=$_from; $book <= $_to; $book++) {
			Log::info("doing ".($book));

			#删除目标数据库中数据
			BookWord::where('book', $book)->delete();

			//分类汇总得到单词表
			$bookword = array();
			$fileId = $book-1;
			if (($fpoutput = fopen(config("mint.path.paliword_book") . "/{$fileId}_words.csv", "r")) !== false) {
				$count = 0;
				while (($data = fgetcsv($fpoutput, 0, ',')) !== false) {
					$book = $data[1];
					if (isset($bookword[$data[3]])) {
						$bookword[$data[3]]++;
					} else {
						$bookword[$data[3]] = 1;
					}

					$count++;
				}
			}else{
				Log::error("open csv fail");
				continue;
			}
			DB::transaction(function ()use($book,$bookword) {
				foreach ($bookword as $key => $value) {
					$newData = [
						'book'=>$book,
						'wordindex'=>$key,
						'count'=>$value,
					];
					BookWord::create($newData);
				}
			});
			$bar->advance();
		}
		$bar->finish();

		$msg = "all done in ". time()-$startTime . "s";
		$this->info($msg.PHP_EOL);
		Log::info($msg);

        return 0;
    }
}
