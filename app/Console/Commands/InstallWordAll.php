<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WordList;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstallWordAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:wordall {from?} {to?}';

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
			DB::transaction(function ()use($book) {
				$fileSn = $book-1;
				if (($fpoutput = fopen(config("mint.path.paliword_book") . "/{$fileSn}_words.csv", "r")) !== false){
					#删除目标数据库中数据
					WordList::where('book', $book)->delete();
					while (($data = fgetcsv($fpoutput, 0, ',')) !== false)
					{
						$newData = [
							'sn'=>$data[0],
							'book'=>$data[1],
							'paragraph'=>$data[2],
							'wordindex'=>$data[3],
							'bold'=>$data[4],
						];
						WordList::create($newData);
					}
					return 0;
				}else{
					Log::error("open csv fail");
					return 1;
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
