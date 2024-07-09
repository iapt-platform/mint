<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\PaliText;
use Illuminate\Support\Facades\Log;

class ExportPalitext extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:pali.text';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '导出离线用的巴利段落数据';

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
        Log::debug('task export offline palitext-table start');
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
        $exportFile = storage_path('app/public/export/offline/wikipali-offline-'.date("Y-m-d").'.db3');
        $dbh = new \PDO('sqlite:'.$exportFile, "", "", array(\PDO::ATTR_PERSISTENT => true));
        $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
        $dbh->beginTransaction();

        $query = "INSERT INTO pali_text ( id , book , paragraph, level, toc,
                                    chapter_len , parent   )
                                    VALUES ( ? , ? , ? , ? , ? , ? , ? )";
        try{
            $stmt = $dbh->prepare($query);
        }catch(PDOException $e){
            Log::info($e);
            return 1;
        }

        $bar = $this->output->createProgressBar(PaliText::count());
        foreach (PaliText::select(['uid','book','paragraph',
                    'level','toc','lenght','chapter_len',
                    'next_chapter','prev_chapter','parent','chapter_strlen'])
                    ->orderBy('book')
                    ->orderBy('paragraph')
                    ->cursor() as $chapter) {
            $currData = array(
                            $chapter->uid,
                            $chapter->book,
                            $chapter->paragraph,
                            $chapter->level,
                            $chapter->toc,
                            $chapter->chapter_len,
                            $chapter->parent,
                            );
            $stmt->execute($currData);
            $bar->advance();
        }
        $dbh->commit();
        $bar->finish();
        Log::debug('task: export offline palitext-table finished');

        return 0;
    }
}
