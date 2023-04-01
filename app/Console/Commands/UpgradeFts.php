<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BookTitle;
use App\Models\FtsText;

class UpgradeFts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:fts {--book}';

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
        if($this->option('book')){
            $bookTitles = BookTitle::orderBy('id')->get();
            $bar = $this->output->createProgressBar(count($bookTitles));
            foreach ($bookTitles as $key => $value) {
                # code...
                FtsText::where('book',$value->book)
                        ->where('paragraph','>=',$value->paragraph)
                        ->update(['pcd_book_id'=>$value->id]);
                $bar->advance();
            }
            $bar->finish();
            return 0;
        }
        return 0;
    }
}
