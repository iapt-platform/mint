<?php

namespace App\Console\Commands;

use App\Services\OpenSearchService;
use Exception;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * 把索引的 pali_synonyms filter 指向新的同义词文件（两步流程的第二步）
 *
 * 第一步 export:pali.synonyms 把文件导出到 storage/app/private，
 * ops 脚本把文件分发到 OpenSearch 各节点后再执行本命令。
 * 实际路径 = OPENSEARCH_SYNONYMS_PATH + '/' + name，
 * 文件必须已存在于各节点，否则索引关闭后无法重新打开。
 *
 * Usage:
 *   php artisan export:pali.synonyms.set-path --name=pali-synonyms-20261021.txt
 */
#[Signature('export:pali.synonyms.set-path {--name= : 同义词文件名，不能带路径}')]
#[Description('切换 OpenSearch 索引使用的巴利语同义词文件')]
class ExportPaliSynonymsSetPath extends Command
{
    /**
     * Execute the console command.
     *
     * @return int Command::SUCCESS | Command::FAILURE
     */
    public function handle(OpenSearchService $service): int
    {
        $name = (string) $this->option('name');

        if (preg_match(ExportPaliSynonyms::FILENAME_PATTERN, $name) !== 1) {
            $this->error('--name must be a plain file name matching '.ExportPaliSynonyms::FILENAME_PATTERN);

            return self::FAILURE;
        }

        try {
            $result = $service->updatePaliSynonymsPath($name);
        } catch (Exception $e) {
            $this->error('Update failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("synonyms_path => {$result['path']}");

        return self::SUCCESS;
    }
}
