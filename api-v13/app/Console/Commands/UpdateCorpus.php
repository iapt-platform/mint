<?php

namespace App\Console\Commands;

use App\Services\SentenceService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Channel;

#[Signature('app:update-corpus')]
#[Description('Update corpus from JSONL files in corpus directory')]
class UpdateCorpus extends Command
{
    /**
     * The SentenceService instance.
     *
     * @var SentenceService
     */
    protected SentenceService $sentenceService;

    /**
     * Create a new command instance.
     *
     * @param SentenceService $sentenceService
     */
    public function __construct(SentenceService $sentenceService)
    {
        parent::__construct();
        $this->sentenceService = $sentenceService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Starting corpus update process...');

        // Get the corpus base path from config
        $corpusBasePath = config('mint.path.corpus');

        if (!is_dir($corpusBasePath)) {
            $this->error("Corpus directory not found: {$corpusBasePath}");
            return self::FAILURE;
        }

        // Scan subdirectories of the corpus path
        $subdirectories = $this->getSubdirectories($corpusBasePath);

        if (empty($subdirectories)) {
            $this->warn('No subdirectories found in corpus path.');
            return self::SUCCESS;
        }

        $this->info("Found " . count($subdirectories) . " subdirectories to process.");

        $totalProcessed = 0;
        $totalErrors = 0;

        foreach ($subdirectories as $subdir) {
            $this->info("Processing directory: {$subdir}");

            try {
                $stats = $this->processCorpusDirectory($subdir);
                $totalProcessed += $stats['processed'];
                $totalErrors += $stats['errors'];
                $this->info("Directory processed: {$stats['processed']} records saved, {$stats['errors']} errors");
            } catch (\Exception $e) {
                $this->error("Failed to process directory {$subdir}: {$e->getMessage()}");
                $totalErrors++;
            }
        }

        $this->info("Corpus update completed. Total processed: {$totalProcessed}, Total errors: {$totalErrors}");

        return $totalErrors > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Get all subdirectories of a given directory.
     *
     * @param string $path
     * @return array
     */
    protected function getSubdirectories(string $path): array
    {
        $directories = [];

        $items = scandir($path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $path . DIRECTORY_SEPARATOR . $item;
            if (is_dir($fullPath)) {
                $directories[] = $fullPath;
            }
        }

        return $directories;
    }

    /**
     * Process a single corpus directory.
     *
     * @param string $directoryPath
     * @return array
     * @throws \Exception
     */
    protected function processCorpusDirectory(string $directoryPath): array
    {
        $stats = [
            'processed' => 0,
            'errors' => 0,
        ];

        // Read meta.json file
        $metaFile = $directoryPath . DIRECTORY_SEPARATOR . 'meta.json';

        if (!file_exists($metaFile)) {
            $this->warn("meta.json not found in directory: {$directoryPath}");
            return $stats;
        }

        $metaData = json_decode(file_get_contents($metaFile), true);

        if (!isset($metaData['id'])) {
            $this->error("Invalid meta.json: missing 'id' field in {$directoryPath}");
            return $stats;
        }

        $sourceId = $metaData['id'];
        $this->info("Processing {$directoryPath} source ID: {$sourceId}");

        // Find all channel records with matching source_id
        $channels = Channel::where('source_id', $sourceId)->get();

        if ($channels->isEmpty()) {
            $this->warn("No channels found with source_id: {$sourceId}");
            return $stats;
        }

        $this->info("Found {$channels->count()} channel(s) for source ID: {$sourceId}");

        // Scan subdirectories of the current directory for JSONL files
        $childDirectories = $this->getSubdirectories($directoryPath);

        foreach ($childDirectories as $childDir) {
            $this->info("Scanning directory for JSONL files: {$childDir}");
            $jsonlFiles = glob($childDir . DIRECTORY_SEPARATOR . '*.jsonl');

            foreach ($jsonlFiles as $jsonlFile) {
                $this->line("Processing file: {$jsonlFile}");
                $fileStats = $this->processJsonlFile($jsonlFile, $channels);
                $stats['processed'] += $fileStats['processed'];
                $stats['errors'] += $fileStats['errors'];
            }
        }

        return $stats;
    }

    /**
     * Process a single JSONL file and save records for each channel.
     *
     * @param string $filePath
     * @param \Illuminate\Database\Eloquent\Collection $channels
     * @return array
     */
    protected function processJsonlFile(string $filePath, $channels): array
    {
        $stats = [
            'processed' => 0,
            'errors' => 0,
        ];

        $handle = fopen($filePath, 'r');

        if (!$handle) {
            $this->error("Failed to open file: {$filePath}");
            return $stats;
        }

        $lineNumber = 0;
        $robotUid = config('mint.admin.robot_uuid');

        if (!$robotUid) {
            $this->error('robot_uuid not configured in mint.admin.robot_uuid');
            fclose($handle);
            return $stats;
        }

        while (($line = fgets($handle)) !== false) {
            $lineNumber++;
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            // Parse JSON line
            $data = json_decode($line, true);

            if ($data === null) {
                $this->error("Failed to parse JSON at line {$lineNumber} in file: {$filePath}");
                $stats['errors']++;
                continue;
            }

            // Save for each channel
            foreach ($channels as $channel) {
                try {
                    $saveData = [
                        'book_id' => $data['book'],
                        'paragraph' => $data['paragraph'],
                        'word_start' => $data['start'],
                        'word_end' => $data['end'],
                        'content' => $data['content'],
                        'channel_uid' => $channel->uid,
                        'editor_uid' => $robotUid,
                    ];

                    DB::transaction(function () use ($saveData) {
                        $this->sentenceService->save($saveData);
                    });

                    $stats['processed']++;
                    //$this->line("Saved record for channel: {$channel->uid}");
                } catch (\Exception $e) {
                    $this->error("Failed to save record for channel {$channel->uid} at line {$lineNumber}: {$e->getMessage()}");
                    $stats['errors']++;
                }
            }
        }

        fclose($handle);
        $this->line("$lineNumber lines write");
        return $stats;
    }
}
