<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OpenSearchService;
use Illuminate\Support\Facades\App;


class IndexOpenSearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:index-open-search';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '如果是新的索引 全量更新openSearch';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $service = app(OpenSearchService::class);
        if ($service->count() === 0) {
            $this->call('opensearch:index-pali');
        } else {
            if (App::environment('local')) {
                $this->info('data exist');
            }
        }
    }
}
