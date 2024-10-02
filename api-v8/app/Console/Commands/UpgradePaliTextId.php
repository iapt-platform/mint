<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaliText;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpgradePaliTextId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:palitextid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'upgrade pali text uuid';

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
        $this->info("upgrade pali text uuid");
        $startTime = time();

        $bar = $this->output->createProgressBar(PaliText::count());
        #载入csv数据
        $csvFile = config("mint.path.pali_title") .'/pali_text_uuid.csv';
        if (($fp = fopen($csvFile, "r")) === false) {
            $this->error( "can not open csv file. filename=" . $csvFile. PHP_EOL) ;
            Log::error( "can not open csv file. filename=" . $csvFile) ;
        }
        Log::info("csv load:" . $csvFile);
        $inputRow=0;
        while (($data = fgetcsv($fp, 0, ',')) !== false) {
            if ($inputRow > 0) {
                PaliText::where('book',$data[0])
                        ->where('paragraph',$data[1])
                        ->update(['uid'=>$data[2]]);
            }
            $inputRow++;
            $bar->advance();

        }
        fclose($fp);
        $bar->finish();
        $this->info("mission finished. in ". time()-$startTime . "s");
		Log::info("mission finished. in ". time()-$startTime . "s");

        return 0;
    }
}
