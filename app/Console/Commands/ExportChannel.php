<?php
/**
 * 导出离线用的channel数据
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Channel;

class ExportChannel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:channel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '导出离线用的channel数据';

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
        $filename = "public/export/offline/channel.csv";
        Storage::disk('local')->put($filename, "");
        $file = fopen(storage_path("app/{$filename}"),"w");
        fputcsv($file,['id','name','type','language','summary','owner_id','setting','created_at']);
        $bar = $this->output->createProgressBar(Channel::where('status',30)->count());
        foreach (Channel::where('status',30)->select(['uid','name','type','lang','summary','owner_uid','setting','created_at'])->cursor() as $chapter) {
            fputcsv($file,[
                            $chapter->uid,
                            $chapter->name,
                            $chapter->type,
                            $chapter->lang,
                            $chapter->summary,
                            $chapter->owner_uid,
                            $chapter->setting,
                            $chapter->created_at,
                            ]);
            $bar->advance();
        }
        fclose($file);
        $bar->finish();
        return 0;
    }
}
