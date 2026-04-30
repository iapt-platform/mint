<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Sentence;
use App\Models\Channel;
use App\Http\Api\ChannelApi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Http\Api\MdRender;

class ExportSentence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:sentence {--channel=} {--type=translation} {--driver=morus}';

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
        Log::debug('task export offline sentence-table start');
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
        \App\Tools\Markdown::driver($this->option('driver'));
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


        $exportFile = storage_path('app/public/export/offline/wikipali-offline-'.date("Y-m-d").'.db3');
        $dbh = new \PDO('sqlite:'.$exportFile, "", "", array(\PDO::ATTR_PERSISTENT => true));
        $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
        $dbh->beginTransaction();

        if($channel_type === "original"){
            $table = 'sentence';
        }else{
            $table = 'sentence_translation';
        }

        $query = "INSERT INTO {$table} ( book , paragraph ,
                                    word_start , word_end , content , channel_id  )
                                    VALUES ( ? , ? , ? , ? , ? , ? )";
        try{
            $stmt = $dbh->prepare($query);
        }catch(PDOException $e){
            Log::info($e);
            return 1;
        }

        $db = Sentence::whereIn('channel_uid',$channels);
        $bar = $this->output->createProgressBar($db->count());
        $srcDb = $db->select(['uid','book_id','paragraph',
                                'word_start','word_end',
                                'content','content_type','channel_uid',
                                'editor_uid','language','updated_at'])->cursor();
        foreach ($srcDb as $sent) {
            if(Str::isUuid($sent->channel_uid)){
                $channel = ChannelApi::getById($sent->channel_uid);
                $currData = array(
                        $sent->book_id,
                        $sent->paragraph,
                        $sent->word_start,
                        $sent->word_end,
                        MdRender::render($sent->content,
                                        [$sent->channel_uid],
                                        null,
                                        'read',
                                        $channel['type'],
                                        $sent->content_type,
                                        'unity',
                                        ),
                        $sent->channel_uid,
                    );
                $stmt->execute($currData);

            }
            $bar->advance();
        }
        $dbh->commit();
        $bar->finish();
        Log::debug('task export sentence finished');
        return 0;
    }
}
