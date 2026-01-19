<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\ProgressChapter;
use App\Models\Channel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Tools\RedisClusters;

class ExportChapterIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:chapter.index {db : db file name wikipali-offline or wikipali-offline-index}';

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
        Log::debug('task export offline chapter-index-table start');
        if(\App\Tools\Tools::isStop()){
            return 0;
        }

        $exportFile = storage_path('app/public/export/offline/'.$this->argument('db').'-'.date("Y-m-d").'.db3');
        $dbh = new \PDO('sqlite:'.$exportFile, "", "", array(\PDO::ATTR_PERSISTENT => true));
        $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
        $dbh->beginTransaction();

        $query = "INSERT INTO chapter ( id , book , paragraph,
                                    language , title , channel_id , progress,updated_at  )
                                    VALUES ( ? , ? , ? , ? , ? , ? , ? , ?  )";
        try{
            $stmt = $dbh->prepare($query);
        }catch(PDOException $e){
            Log::info($e);
            return 1;
        }

        $publicChannels = Channel::where('status',30)->select('uid')->get();
        $rows = ProgressChapter::whereIn('channel_id',$publicChannels)->count();
        RedisClusters::put("/export/chapter/count",$rows,3600*10);
        $bar = $this->output->createProgressBar($rows);
        foreach (ProgressChapter::whereIn('channel_id',$publicChannels)
                                ->select(['uid','book','para',
                                'lang','title','channel_id',
                                'progress','updated_at'])->cursor() as $row) {
            $currData = array(
                            $row->uid,
                            $row->book,
                            $row->para,
                            $row->lang,
                            $row->title,
                            $row->channel_id,
                            $row->progress,
                            $row->updated_at,
                            );
            $stmt->execute($currData);
            $bar->advance();
        }
        $dbh->commit();
        $bar->finish();
        Log::debug('task export offline chapter-index-table finished');
        return 0;
    }
}
