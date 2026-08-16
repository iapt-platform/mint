<?php

namespace App\Console\Commands;

use App\Services\OpenSearchService;
use Exception;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Check whether the pali_synonyms dictionary is applied for a given word
 *
 * Usage:
 *   php artisan app:pali-query-health-check          # defaults to "dhamma"
 *   php artisan app:pali-query-health-check dhamma    # custom word
 */
#[Signature('app:pali-query-health-check {word? : Pali word to check, defaults to dhamma}')]
#[Description('Check whether the pali_synonyms dictionary is applied for a given word via the OpenSearch _analyze API')]
class PaliQueryHealthCheck extends Command
{
    /**
     * Execute the console command.
     *
     * @param  OpenSearchService  $service
     * @return int Command::SUCCESS | Command::FAILURE
     */
    public function handle(OpenSearchService $service): int
    {
        $word = $this->argument('word') ?? 'dhamma';

        $this->info("Checking synonym expansion for [{$word}]...");

        try {
            $tokens = $service->pali_query_health_check($word);
        } catch (Exception $e) {
            $this->error('Check failed: '.$e->getMessage());

            return self::FAILURE;
        }

        if (empty($tokens)) {
            $this->warn('No tokens returned. Please check that the index exists and the analyzer is configured correctly.');

            return self::FAILURE;
        }

        $this->table(
            ['#', 'Token'],
            collect($tokens)->values()->map(fn ($token, $i) => [$i + 1, $token])->toArray()
        );

        $expanded = count($tokens) > 1;

        if ($expanded) {
            $this->info("✅ Synonym dictionary is active — [{$word}] expanded into ".count($tokens).' token(s).');
        } else {
            $this->warn("⚠️  [{$word}] did not expand into any synonyms. Check that the entry exists in pali_synonyms.txt, or that the index has reloaded the file.");
        }

        return self::SUCCESS;
    }
}
