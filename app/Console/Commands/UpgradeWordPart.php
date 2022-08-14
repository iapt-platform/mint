<?php

namespace App\Console\Commands;

use App\Models\WordPart;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpgradeWordPart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:wordpart';

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
		#载入csv数据
		$csvFile = config("app.path.dict_text") .'/system/part.csv';
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
