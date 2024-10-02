<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\TagMap;
use Illuminate\Support\Facades\Log;

class ExportTagmap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:tag.map {db}';

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
        Log::debug('task: export offline tagmap-table start');
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
        $exportFile = storage_path('app/public/export/offline/'.$this->argument('db').'-'.date("Y-m-d").'.db3');
        $dbh = new \PDO('sqlite:'.$exportFile, "", "", array(\PDO::ATTR_PERSISTENT => true));
        $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
        $dbh->beginTransaction();

        $query = "INSERT INTO tag_map ( anchor_id , tag_id )
                                    VALUES ( ? , ? )";
        try{
            $stmt = $dbh->prepare($query);
        }catch(PDOException $e){
            Log::info($e);
            return 1;
        }

        $bar = $this->output->createProgressBar(TagMap::count());
        foreach (TagMap::select(['id','table_name','anchor_id','tag_id'])->cursor() as $row) {
            $currData = array(
                            $row->anchor_id,
                            $row->tag_id,
                            );
            $stmt->execute($currData);
            $bar->advance();
        }
        $dbh->commit();
        $bar->finish();
        Log::debug('task: export offline tagmap-table finished');
        return 0;
    }
}
