<?php

namespace App\Console\Commands;

use App\Services\OpenSearchService;
use Illuminate\Console\Command;

class UpdateOpenSearchIndex extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan create:opensearch.index
     * @var string
     */
    protected $signature = 'update:opensearch.index';

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
        $this->warn('Index already exists, attempting to update...');
        try {
            $update = $openSearch->updateIndex();
            if (!empty($update['settings']) && $update['settings']['acknowledged']) {
                $this->info('Index settings updated successfully');
            }
            if (!empty($update['mappings']) && $update['mappings']['acknowledged']) {
                $this->info('Index mappings updated successfully');
            }
            if (empty($update['settings']) && empty($update['mappings'])) {
                $this->warn('No settings or mappings provided for update');
            }
        } catch (\Exception $updateException) {
            $this->error('Failed to update index: ' . $updateException->getMessage());
            return 1;
        }
        return 0;
    }
}
