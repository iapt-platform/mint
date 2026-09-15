<?php

namespace App\Console\Commands;

use App\Models\Sentence;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('export:pali-word-frequency {--limit=0 : 只处理前 N 条记录，0 表示全部（测试用）}')]
#[Description('扫描 channel.type=translation 的句子，统计 [[巴利文]] 单词出现次数并输出 CSV')]
class ExportPaliWordFrequency extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $query = Sentence::type('translation')->select(['uid', 'content'])->orderBy('uid');
        $total = $query->count();
        $this->info("符合要求的 sentences 总数: {$total}".($limit > 0 ? "（本次仅处理前 {$limit} 条）" : ''));

        if ($total === 0) {
            $this->warn('没有找到任何记录，退出。');

            return 0;
        }

        if ($limit > 0) {
            $query = $query->limit($limit);
        }

        $wordCounts = [];
        $processed = 0;
        $matchedSentences = 0;

        foreach ($query->cursor() as $sent) {
            $processed++;
            if (! empty($sent->content) && preg_match_all('/\[\[([^\[\]]+)\]\]/u', $sent->content, $matches) > 0) {
                $matchedSentences++;
                foreach ($matches[1] as $word) {
                    $word = trim($word);
                    if ($word === '') {
                        continue;
                    }
                    $wordCounts[$word] = ($wordCounts[$word] ?? 0) + 1;
                }
            }

            if ($processed % 1000 === 0) {
                $this->info("已处理 {$processed}/{$total} 条，其中 {$matchedSentences} 条包含 [[巴利文]]，累计发现 ".count($wordCounts).' 个不同单词');
            }
        }

        arsort($wordCounts, SORT_NUMERIC);

        $fileName = 'pali-word-frequency-'.date('Ymd-His').($limit > 0 ? "-limit{$limit}" : '').'.csv';
        $exportDir = storage_path('app/public/export');
        if (! is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }
        $filePath = $exportDir.'/'.$fileName;

        $file = fopen($filePath, 'w');
        fwrite($file, "\xEF\xBB\xBF");
        fputcsv($file, ['word', 'count']);
        foreach ($wordCounts as $word => $count) {
            fputcsv($file, [$word, $count]);
        }
        fclose($file);

        $this->info("扫描完成：共处理 {$processed} 条记录，{$matchedSentences} 条包含 [[巴利文]]，".count($wordCounts).' 个不同单词');
        $this->info("CSV 已输出: {$filePath}");

        return 0;
    }
}
