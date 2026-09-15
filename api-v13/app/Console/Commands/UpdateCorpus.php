<?php

namespace App\Console\Commands;

use App\Http\Api\UserApi;
use App\Models\Channel;
use App\Services\SentenceService;
use App\Services\TermService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Signature('app:update-corpus {--dir=} {--es}')]
#[Description('Update corpus from JSONL files in corpus directory')]
class UpdateCorpus extends Command
{
    /**
     * The SentenceService instance.
     */
    protected SentenceService $sentenceService;

    protected TermService $termService;

    /**
     * Create a new command instance.
     */
    public function __construct(SentenceService $sentenceService, TermService $termService)
    {
        parent::__construct();
        $this->sentenceService = $sentenceService;
        $this->termService = $termService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting corpus update process...');

        // Get the corpus base path from config
        if ($this->option('dir')) {
            $corpusBasePath = $this->option('dir');
        } else {
            $corpusBasePath = config('mint.path.corpus');
        }

        if (! is_dir($corpusBasePath)) {
            $this->error("Corpus directory not found: {$corpusBasePath}");

            return self::FAILURE;
        }

        // Scan subdirectories of the corpus path
        $stores = $this->getSubdirectories($corpusBasePath);

        if (empty($stores)) {
            $this->warn('No subdirectories found in corpus path.');

            return self::SUCCESS;
        }

        $this->info('Found '.count($stores).' subdirectories to process.');

        $totalProcessed = 0;
        $totalErrors = 0;

        foreach ($stores as $store) {
            $this->info("Processing directory: {$store}");

            try {
                $stats = $this->processCorpusDirectory($store);
                $totalProcessed += $stats['processed'];
                $totalErrors += $stats['errors'];
                $this->info("Directory processed: {$stats['processed']} records saved, {$stats['errors']} errors");
                if ($this->option('es') && isset($stats['channels'])) {
                    foreach ($stats['channels'] as $key => $channelId) {
                        $this->call('upgrade:progress', ['--channel' => $channelId]);
                        $this->call('upgrade:progress.chapter', ['--channel' => $channelId]);
                        $this->call('opensearch:index-tipitaka', [
                            'book' => 0,
                            '--channel' => $channelId,
                            '--granularity' => 'chapter',
                            '--summary' => 'off',
                        ]);
                    }
                }
            } catch (\Exception $e) {
                $this->error("Failed to process directory {$store}: {$e->getMessage()}");
                Log::error('Failed to process directory', [
                    'dir' => $store,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $totalErrors++;
            }
        }

        $this->info("Corpus update completed. Total processed: {$totalProcessed}, Total errors: {$totalErrors}");

        return $totalErrors > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Get all subdirectories of a given directory.
     */
    protected function getSubdirectories(string $path): array
    {
        $directories = [];

        $items = scandir($path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $path.DIRECTORY_SEPARATOR.$item;
            if (is_dir($fullPath)) {
                $directories[] = $fullPath;
            }
        }

        return $directories;
    }

    /**
     * Process a single corpus directory.
     *
     * @throws \Exception
     */
    protected function processCorpusDirectory(string $directoryPath): array
    {
        $stats = [
            'processed' => 0,
            'errors' => 0,
        ];

        // Read meta.json file
        $metaFile = $directoryPath.DIRECTORY_SEPARATOR.'meta.json';

        if (! file_exists($metaFile)) {
            $this->warn("meta.json not found in directory: {$directoryPath}");

            return $stats;
        }

        $metaData = json_decode(file_get_contents($metaFile), true);

        if (! isset($metaData['id'])) {
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

        $glossaryFile = $directoryPath.DIRECTORY_SEPARATOR.'glossary.csv';

        if (file_exists($glossaryFile)) {
            $status = $this->processGlossary($glossaryFile, $channels);
            $this->line('glossary load');
        }

        // Scan subdirectories of the current directory for JSONL files
        $childDirectories = $this->getSubdirectories($directoryPath);

        foreach ($childDirectories as $childDir) {
            $this->info("Scanning directory for JSONL files: {$childDir}");
            $jsonlFiles = glob($childDir.DIRECTORY_SEPARATOR.'*.jsonl');

            foreach ($jsonlFiles as $jsonlFile) {
                $this->line("Processing file: {$jsonlFile}");
                $fileStats = $this->processJsonlFile($jsonlFile, $channels);
                $stats['processed'] += $fileStats['processed'];
                $stats['errors'] += $fileStats['errors'];
            }
        }
        $stats['channels'] = array_map(fn ($item) => $item['uid'], $channels->toArray());

        return $stats;
    }

    /**
     * Process a glossary csv file and save glossary for each channel.
     *
     * @param  Collection  $channels
     */
    protected function processGlossary(string $filePath, $channels): array
    {
        $stats = [
            'processed' => 0,
            'errors' => 0,
        ];

        $handle = fopen($filePath, 'r');

        if (! $handle) {
            $this->error("Failed to open file: {$filePath}");

            return $stats;
        }

        $robotUid = config('mint.admin.robot_uuid');

        if (! $robotUid) {
            $this->error('robot_uuid not configured in mint.admin.robot_uid');
            fclose($handle);

            return $stats;
        }

        // 读取表头行
        $headers = fgetcsv($handle);

        if ($headers === false) {
            $this->error("Failed to read CSV headers from: {$filePath}");
            fclose($handle);

            return $stats;
        }

        $lineNumber = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if (count($row) !== count($headers)) {
                $this->error("Column count mismatch at line {$lineNumber} in file: {$filePath}");
                $stats['errors']++;

                continue;
            }

            $data = array_combine($headers, $row);
            $editor_id = UserApi::getIdByUuid($robotUid);
            foreach ($channels as $channel) {
                try {
                    $saveData = [
                        'word' => $data['pali_word'],
                        'tag' => $data['tag'] ?? null,
                        'channel_id' => $channel->uid,
                        'meaning' => $data['meaning'],
                        'redirect' => $data['redirect'] ?? null,
                        'other_meaning' => $data['meaning2'] ?: null,
                        'note' => $data['note'] ?: null,
                        'editor_id' => $editor_id,
                    ];

                    DB::transaction(function () use ($saveData) {
                        $this->termService->updateOrCreateByWord($saveData);
                    });

                    $stats['processed']++;
                } catch (\Exception $e) {
                    $this->error("Failed to save glossary for channel {$channel->uid} at line {$lineNumber}: {$e->getMessage()}");
                    $stats['errors']++;
                }
            }
        }

        fclose($handle);
        $this->line("glossary {$lineNumber} lines processed");

        return $stats;
    }

    /**
     * Process a single JSONL file and save records for each channel.
     *
     * @param  Collection  $channels
     */
    protected function processJsonlFile(string $filePath, $channels): array
    {
        $stats = [
            'processed' => 0,
            'errors' => 0,
        ];

        $handle = fopen($filePath, 'r');

        if (! $handle) {
            $this->error("Failed to open file: {$filePath}");

            return $stats;
        }

        $lineNumber = 0;
        $robotUid = config('mint.admin.robot_uuid');

        if (! $robotUid) {
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
                    [$book, $para, $start, $end] = explode('-', $data['id']);
                    $saveData = [
                        'book_id' => $book,
                        'paragraph' => $para,
                        'word_start' => $start,
                        'word_end' => $end,
                        'content' => $data['content'],
                        'channel_uid' => $channel->uid,
                        'editor_uid' => $robotUid,
                    ];

                    DB::transaction(function () use ($saveData) {
                        $this->sentenceService->save($saveData);
                    });

                    $stats['processed']++;
                    // $this->line("Saved record for channel: {$channel->uid}");
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
