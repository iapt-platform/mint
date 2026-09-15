<?php

namespace App\Console\Commands;

use App\Models\DhammaTerm;
use App\Tools\Tools;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExportTerm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:term';

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
        Log::info('task export offline term-table start');
        $startAt = time();
        if (Tools::isStop()) {
            return 0;
        }
        $exportFile = storage_path('app/public/export/offline/wikipali-offline-'.date('Y-m-d').'.db3');
        $dbh = new \PDO('sqlite:'.$exportFile, '', '', [\PDO::ATTR_PERSISTENT => true]);
        $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
        $dbh->beginTransaction();

        $query = 'INSERT INTO dhamma_terms ( uuid , word , word_en , meaning ,
                                    other_meaning , note , tag , channel_id,
                                    language, owner, editor_id,
                                    created_at,updated_at,deleted_at)
                                    VALUES ( ? , ? , ? , ? ,
                                            ? , ? , ? , ? ,
                                            ?, ?, ?,
                                            ?, ?, ? )';
        try {
            $stmt = $dbh->prepare($query);
        } catch (\PDOException $e) {
            Log::error($e->getMessage(), ['exception' => $e]);

            return 1;
        }

        $total = DhammaTerm::count();
        $channels = DhammaTerm::select([
            'guid',
            'word',
            'word_en',
            'meaning',
            'other_meaning',
            'note',
            'tag',
            'channal',
            'language',
            'owner',
            'editor_id',
            'created_at',
            'updated_at',
            'deleted_at',
        ])
            ->cursor();
        foreach ($channels as $key => $row) {
            $currData = [
                $row->guid,
                $row->word,
                $row->word_en,
                $row->meaning,
                $row->other_meaning,
                $row->note,
                $row->tag,
                $row->channal,
                $row->language,
                $row->owner,
                $row->editor_id,
                $row->created_at,
                $row->updated_at,
                $row->deleted_at,
            ];
            $stmt->execute($currData);

            if ($key % 1000 === 0) {
                $precent = (int) ($key * 100 / $total);
                $this->line("[{$precent}%]");
            }
        }
        $dbh->commit();
        $bar->finish();
        $this->info(' time='.(time() - $startAt).'s');
        Log::info('task export offline term-table finished');

        return 0;
    }
}
