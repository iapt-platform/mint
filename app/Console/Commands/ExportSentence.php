<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Sentence;

class ExportSentence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:sentence';

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
        Storage::disk('local')->put("public/export/sentence.csv", "");
        $file = fopen(storage_path('app/public/export/sentence.csv'),"w");
        fputcsv($file,['id','book','paragraph','word_start','word_end','content','content_type','html','channel_id','editor_id','language','updated_at']);
        $bar = $this->output->createProgressBar(Sentence::where('status',30)->count());
        foreach (Sentence::where('status',30)->select(['uid','book_id','paragraph','word_start','word_end','content','content_type','channel_uid','editor_uid','language','updated_at'])->cursor() as $chapter) {
            fputcsv($file,[
                            $chapter->uid,
                            $chapter->book_id,
                            $chapter->paragraph,
                            $chapter->word_start,
                            $chapter->word_end,
                            $chapter->content,
                            $chapter->content_type,
                            $chapter->content,
                            $chapter->channel_uid,
                            $chapter->editor_uid,
                            $chapter->language,
                            $chapter->updated_at,
                            ]);
            $bar->advance();
        }
        fclose($file);
        $bar->finish();
        return 0;
    }
}
