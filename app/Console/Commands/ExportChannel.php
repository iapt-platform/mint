<?php
/**
 * 导出离线用的channel数据
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Channel;
use Illuminate\Support\Facades\Log;

class ExportChannel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:channel {db}';

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
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
        Log::debug('task export offline channel-table start');
        $exportFile = storage_path('app/public/export/offline/'.$this->argument('db').'-'.date("Y-m-d").'.db3');
        $dbh = new \PDO('sqlite:'.$exportFile, "", "", array(\PDO::ATTR_PERSISTENT => true));
        $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
        $dbh->beginTransaction();

        $query = "INSERT INTO channel ( id , name , type , language ,
                                    summary , owner_id , setting,created_at )
                                    VALUES ( ? , ? , ? , ? , ? , ? , ? , ?  )";
        try{
            $stmt = $dbh->prepare($query);
        }catch(PDOException $e){
            Log::info($e);
            return 1;
        }

        $bar = $this->output->createProgressBar(Channel::where('status',30)->count());
        foreach (Channel::where('status',30)
                ->select(['uid','name','type','lang',
                          'summary','owner_uid','setting','created_at'])
                          ->cursor() as $row) {
                $currData = array(
                            $row->uid,
                            $row->name,
                            $row->type,
                            $row->lang,
                            $row->summary,
                            $row->owner_uid,
                            $row->setting,
                            $row->created_at,
                            );
            $stmt->execute($currData);
            $bar->advance();
        }
        $dbh->commit();
        $bar->finish();
        Log::debug('task export offline channel-table finished');
        return 0;
    }
}
