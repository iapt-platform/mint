<?php

namespace App\Console\Commands;

use App\Models\DhammaTerm;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExportIKPaliTeam extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan export:ik.pali.team
     *
     * @var string
     */
    protected $signature = 'export:ik.pali.team';

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
        $path = storage_path('app/export/fts');
        if (! is_dir($path)) {
            $res = mkdir($path, 0700, true);
            if (! $res) {
                Log::error('mkdir fail path='.$path);

                return 1;
            }
        }
        $filename = '/pali_term.txt';
        $fp = fopen($path.$filename, 'w') or exit('Unable to open file!');
        $wordsList = [];
        $teams = DhammaTerm::select(['meaning', 'other_meaning'])->get();
        foreach ($teams as $term) {
            if (! empty($term->meaning)) {
                $wordsList[$term->meaning] = 1;
            }
        }
        foreach ($wordsList as $word => $value) {
            fwrite($fp, $word.PHP_EOL);
        }
        // 关闭文件
        fclose($fp);
        $this->info('done');

        return 0;
    }
}
