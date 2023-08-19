<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\ProgressChapter;
use App\Models\Channel;
use Illuminate\Support\Facades\Cache;

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
        $exportFile = storage_path('app/public/export/offline/sentence-'.date("Y-m-d").'.db3');
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
        Cache::put("/export/chapter/count",$rows,3600*10);
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
        return 0;
    }
}