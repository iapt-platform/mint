<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FtsText;
use App\Models\WbwTemplate;
use App\Models\BookTitle;
use App\Models\PaliText;
use App\Models\PageNumber;

class UpgradePcdBookId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:pcd.book.id {--table=all}';

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
        $table = $this->option('table');
        $bookTitles = BookTitle::orderBy('sn')->get();
        $bar = $this->output->createProgressBar(count($bookTitles));
        foreach ($bookTitles as $key => $value) {
            # code...
            if($table === 'all' || $table ==='fts'){
                FtsText::where('book',$value->book)
                    ->where('paragraph','>=',$value->paragraph)
                    ->update(['pcd_book_id'=>$value->sn]);
            }
            if($table === 'all' || $table ==='wbw'){
                WbwTemplate::where('book',$value->book)
                    ->where('paragraph','>=',$value->paragraph)
                    ->update(['pcd_book_id'=>$value->sn]);
            }
            if($table === 'all' || $table ==='pali_text'){
                PaliText::where('book',$value->book)
                    ->where('paragraph','>=',$value->paragraph)
                    ->update(['pcd_book_id'=>$value->sn]);
            }
            if($table === 'all' || $table ==='page_number'){
                PageNumber::where('book',$value->book)
                    ->where('paragraph','>=',$value->paragraph)
                    ->update(['pcd_book_id'=>$value->sn]);
            }
            $bar->advance();
        }
        $bar->finish();

        return 0;
    }
}
