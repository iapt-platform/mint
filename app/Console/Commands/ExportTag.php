<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Tag;

class ExportTag extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:tag';

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
        $filename = "public/export/offline/tag.csv";
        Storage::disk('local')->put($filename, "");
        $file = fopen(storage_path("app/{$filename}"),"w");
        fputcsv($file,['id','name','description','color','owner_id']);
        $bar = $this->output->createProgressBar(Tag::count());
        foreach (Tag::select(['id','name','description','color','owner_id'])->cursor() as $chapter) {
            fputcsv($file,[
                            $chapter->id,
                            $chapter->name,
                            $chapter->description,
                            $chapter->color,
                            $chapter->owner_id,
                            ]);
            $bar->advance();
        }
        fclose($file);
        $bar->finish();
        return 0;
    }
}
