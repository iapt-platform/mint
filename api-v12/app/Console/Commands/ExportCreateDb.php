<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ExportCreateDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:create.db';

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
        Log::debug('task export offline create-db start');
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
        $this->create('sentence.sql','wikipali-offline');
        $this->create('sentence.sql','wikipali-offline-index');

        return 0;
    }

    private function create($sqlFile,$dbFile){
        $sqlPath = database_path('export/'.$sqlFile);
        $exportDir = storage_path('app/public/export/offline');
        $exportFile = $exportDir.'/'.$dbFile.'-'.date("Y-m-d").'.db3';
        $file = fopen($exportFile,'w');
        fclose($file);
        $dbh = new \PDO('sqlite:'.$exportFile, "", "", array(\PDO::ATTR_PERSISTENT => true));
        $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
        //建立数据库
        $_sql = file_get_contents($sqlPath);
        $_arr = explode(';', $_sql);
        //执行sql语句
        foreach ($_arr as $_value) {
            $dbh->query($_value . ';');
        }
        Log::debug('task export offline create-db finished');
    }
}
