<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\ProgressChapter;

class ExportChapterIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:chapter.index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'export chapter index';

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
        $filename = "public/export/offline/chapter.csv";
        Storage::disk('local')->put($filename, "");
        $file = fopen(storage_path("app/{$filename}"),"w");
        fputcsv($file,['id','book','paragraph','language','title','channel_id','progress','updated_at']);
        $bar = $this->output->createProgressBar(ProgressChapter::count());
        foreach (ProgressChapter::select(['uid','book','para','lang','title','channel_id','progress','updated_at'])->cursor() as $chapter) {
            fputcsv($file,[
                            $chapter->uid,
                            $chapter->book,
                            $chapter->para,
                            $chapter->lang,
                            $chapter->title,
                            $chapter->channel_id,
                            $chapter->progress,
                            $chapter->updated_at,
                            ]);
            $bar->advance();
        }
        fclose($file);
        $bar->finish();
        return 0;
    }
}
