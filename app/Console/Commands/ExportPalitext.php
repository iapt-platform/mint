<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\PaliText;

class ExportPalitext extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:pali.text';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '导出离线用的巴利段落数据';

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
        $filename = "public/export/offline/pali_text.csv";
        Storage::disk('local')->put($filename, "");
        $file = fopen(storage_path("app/{$filename}"),"w");
        fputcsv($file,['id','book','paragraph','level','toc','length','chapter_len','next_chapter','prev_chapter','parent','chapter_strlen']);
        $bar = $this->output->createProgressBar(PaliText::count());
        foreach (PaliText::select(['uid','book','paragraph','level','toc','lenght','chapter_len','next_chapter','prev_chapter','parent','chapter_strlen'])
                    ->orderBy('book')
                    ->orderBy('paragraph')
                    ->cursor() as $chapter) {
            fputcsv($file,[
                            $chapter->uid,
                            $chapter->book,
                            $chapter->paragraph,
                            $chapter->level,
                            $chapter->toc,
                            $chapter->lenght,
                            $chapter->chapter_len,
                            $chapter->next_chapter,
                            $chapter->prev_chapter,
                            $chapter->parent,
                            $chapter->chapter_strlen,
                            ]);
            $bar->advance();
        }
        fclose($file);
        $bar->finish();

        return 0;
    }
}
