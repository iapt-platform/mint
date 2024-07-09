<?php

namespace App\Console\Commands;

use App\Models\WordPart;
use App\Models\UserDict;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UpgradeWordPart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:word.part';

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
        $delete = WordPart::where('id','>',0)->delete();
        #载入纸质词典数据
        $paper = UserDict::selectRaw('word,count(*)')->where("source",'_PAPER_')->groupBy('word')->cursor();
        $sql = "select
                      count(*) from (
                        select word, count(*) from user_dicts ud where source = '_PAPER_' group by word) as T";
        $count = DB::select($sql);
        $bar = $this->output->createProgressBar($count[0]->count);
        foreach ($paper as $key => $word) {
            $newWord = new WordPart;
            $newWord->word = $word->word;
            $newWord->weight = $word->count;
            $newWord->save();
            $bar->advance();
        }
        $bar->finish();

		#载入csv数据
		$csvFile = config("mint.path.dict_text") .'/system/part2.csv';
		if (($fp = fopen($csvFile, "r")) !== false) {
			Log::info("csv load：" . $csvFile);
			while (($data = fgetcsv($fp, 0, ',')) !== false) {
				WordPart::updateOrCreate(['word' => $data[0],],['weight' => $data[1],]);
			}
			fclose($fp);
		} else {
			$this->error( "can not open csv file. filename=" . $csvFile. PHP_EOL) ;
			Log::error( "can not open csv file. filename=" . $csvFile) ;
		}
		$this->info('ok');
        return 0;
    }
}
