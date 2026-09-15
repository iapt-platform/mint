<?php

namespace App\Console\Commands;

use App\Services\OpenSearchService;
use Illuminate\Console\Command;

class CreateOpenSearchIndex extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan create:opensearch.index
     *
     * @var string
     */
    protected $signature = 'create:opensearch.index';

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
        $openSearch = app(OpenSearchService::class);

        // Test OpenSearch connection
        $open = $openSearch->testConnection();
        if ($open[0]) {
            $this->info($open[1]);
        } else {
            $this->error($open[1]);

            return 1; // Exit with error code
        }

        // Attempt to create or update index
        try {
            $this->info('create openSearch index. index name='.config('mint.opensearch.index'));
            $crate = $openSearch->createIndex();
            if ($crate['acknowledged']) {
                $this->info('Index created successfully: '.$crate['index']);
            }
            if ($crate['shards_acknowledged']) {
                $this->info('Shards initialized successfully for index: '.$crate['index']);
            } else {
                $this->error('Shard initialization failed for index: '.$crate['index']);

                return 1;
            }
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'exists')) {
                $this->warn('Index already exists, attempting to update...');
            } else {
                $this->error('Failed to create index: '.$e->getMessage());
            }

            return 1;
        }

        return 0;
    }
}
