<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Sentence;
use App\Models\Channel;
use App\Http\Api\ChannelApi;

class ExportSentence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:sentence {--channel=} {--type=translation}';

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
        $channels = [];
        $channel_id = $this->option('channel');
        if($channel_id){
            $file_suf = $channel_id;
            $channels[] = $channel_id;
        }else{
            $channel_type = $this->option('type');
            $file_suf = $channel_type;
            if($channel_type === "original"){
                $pali_channel = ChannelApi::getSysChannel("_System_Pali_VRI_");
                if($pali_channel === false){
                    return 0;
                }
                $channels[] = $pali_channel;
            }else{
                $nissaya_channel = Channel::where('type',$channel_type)->where('status',30)->select('uid')->get();
                foreach ($nissaya_channel as $key => $value) {
                    # code...
                    $channels[] = $value->uid;
                }
            }
        }
        $db = Sentence::whereIn('channel_uid',$channels);
        $file_name = "public/export/offline/sentence_{$file_suf}.csv";
        Storage::disk('local')->put($file_name, "");
        $file = fopen(storage_path("app/{$file_name}"),"w");
        fputcsv($file,['id','book','paragraph','word_start','word_end','content','content_type','html','channel_id','editor_id','language','updated_at']);
        $bar = $this->output->createProgressBar($db->count());
        foreach ($db->select(['uid','book_id','paragraph','word_start','word_end','content','content_type','channel_uid','editor_uid','language','updated_at'])->cursor() as $chapter) {
            $content = str_replace("\n","<br />",$chapter->content);
            fputcsv($file,[
                            $chapter->uid,
                            $chapter->book_id,
                            $chapter->paragraph,
                            $chapter->word_start,
                            $chapter->word_end,
                            $content,
                            $chapter->content_type,
                            $content,
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
