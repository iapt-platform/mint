<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\TagMap;
class ExportTagmap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:tag.map';

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
        $filename = "public/export/offline/tag_map.csv";
        Storage::disk('local')->put($filename, "");
        $file = fopen(storage_path("app/{$filename}"),"w");
        fputcsv($file,['id','table_name','anchor_id','tag_id']);
        $bar = $this->output->createProgressBar(TagMap::count());
        foreach (TagMap::select(['id','table_name','anchor_id','tag_id'])->cursor() as $chapter) {
            fputcsv($file,[
                            $chapter->id,
                            $chapter->table_name,
                            $chapter->anchor_id,
                            $chapter->tag_id,
                            ]);
            $bar->advance();
        }
        fclose($file);
        $bar->finish();
        return 0;
    }
}
