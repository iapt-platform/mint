<?php

namespace App\Console\Commands;

use App\Models\Channel;
use App\Models\PaliSentence;
use App\Models\Progress;
use App\Models\ProgressChapter;
use App\Services\AuthService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

#[Signature('test:mq-progress {--book=93} {--channel=} {--limit=} {--wait=1} {--base-url=} {--mq-log=} {--no-start} {--keep} {--show-every=10} {--drain-timeout=14400}')]
#[Description('Loop-test mq:progress by writing one sentence translation per API call and monitoring MQ output, logs and DB tables')]
class TestMqProgress extends Command
{
    /** worker 输出 / 日志里需要高亮为异常的关键词 */
    private const ERROR_MARKERS = [
        'ambiguous',
        'Command "',
        'is not defined',
        'exception',
        'Exception',
        'SQLSTATE',
        'fail',
        'task error',
    ];

    /** laravel.log 里值得展示的关键词（其余 debug/info 噪音折叠为计数） */
    private const LARAVEL_RELEVANT = [
        'mq:progress',
        'upgrade:progress',
        'mq worker',
        'received message',
        'mq done',
        'ERROR',
        'exception',
        'ambiguous',
        'Command "',
        'SQLSTATE',
        'fail',
    ];

    private string $mqLogPath = '';

    private int $mqLogOffset = 0;

    private int $laravelLogOffset = 0;

    private int $laravelSuppressed = 0;

    public function handle(): int
    {
        $book = (int) ($this->option('book') ?: 93);
        $channelUid = $this->option('channel');
        $limit = $this->option('limit') !== null && $this->option('limit') !== '' ? (int) $this->option('limit') : null;
        $wait = max(0, (int) ($this->option('wait') ?: 1));
        $baseUrl = rtrim((string) ($this->option('base-url') ?: config('app.url')), '/');
        $this->mqLogPath = (string) ($this->option('mq-log') ?: storage_path('logs/mq-progress-test.log'));
        $showEvery = max(1, (int) ($this->option('show-every') ?: 10));
        $drainTimeout = max(0, (int) ($this->option('drain-timeout') ?: 14400));
        $noStart = (bool) $this->option('no-start');
        $keep = (bool) $this->option('keep');

        $token = trim((string) env('TESTING_TOKEN', ''));
        $account = (string) env('TESTING_ACCOUNT', '');

        if ($token === '') {
            $this->error('TESTING_TOKEN is empty in .env');

            return 1;
        }

        $accountUid = $this->decodeUid($token);
        if (! $accountUid) {
            $this->error('TESTING_TOKEN is invalid or expired');

            return 1;
        }

        if (! $channelUid) {
            $channelUid = $this->resolveChannel($accountUid);
            if (! $channelUid) {
                $this->error('No translation channel found for the account. Pass --channel=<uid>.');

                return 1;
            }
            $this->warn("Auto-selected channel: {$channelUid}");
        }

        $this->newLine();
        $this->info('== test:mq-progress ==');
        $this->line("  account     : {$account} ({$accountUid})");
        $this->line("  book        : {$book}");
        $this->line("  channel     : {$channelUid}");
        $this->line("  base url    : {$baseUrl}");
        $this->line('  mq log      : '.$this->mqLogPath);
        $this->line('  laravel log : '.storage_path('logs/laravel.log'));
        $this->newLine();

        // 先确认 token 有效，避免每一句都 401
        $authResp = Http::timeout(15)->withToken($token)->get("{$baseUrl}/api/v2/auth/current");
        if ($authResp->failed() || $authResp->json('ok') !== true) {
            $this->error('auth check failed: HTTP '.$authResp->status().' '.mb_substr($authResp->body(), 0, 200));

            return 1;
        }
        $this->info('auth check ok.');

        $paliRows = PaliSentence::where('book', $book)
            ->orderBy('paragraph')
            ->orderBy('word_begin')
            ->get();
        $total = $paliRows->count();
        if ($limit !== null) {
            $paliRows = $paliRows->take($limit);
        }
        $this->info("pali_sentences book={$book}: {$total}".($limit !== null ? " (writing first {$paliRows->count()})" : ''));

        if ($paliRows->isEmpty()) {
            $this->error('no pali sentences found');

            return 1;
        }

        $before = $this->snapshot($book, $channelUid);
        $this->info('DB snapshot (before):');
        $this->table(['metric', 'value'], $this->snapshotRows($before));
        $this->newLine();

        // 重置 mq 输出文件并记录 laravel.log 当前位置
        $this->resetFile($this->mqLogPath);
        $this->mqLogOffset = 0;
        $this->laravelLogOffset = $this->fileSize(storage_path('logs/laravel.log'));
        $this->laravelSuppressed = 0;

        $process = null;
        if (! $noStart) {
            $process = $this->startWorker();
            $this->info('mq:progress started (pid='.$process->getPid().'), waiting for queue...');
            sleep(2);
            $this->flushMqLog($process);
        } else {
            $this->warn('--no-start: assuming mq:progress is already running');
        }

        $url = "{$baseUrl}/api/v2/sentence";
        $okCount = 0;
        $failCount = 0;
        $lastFingerprint = $before['fingerprint'];
        $mqErrors = 0;

        $this->newLine();
        $this->info("Start writing {$paliRows->count()} sentences (one POST each)...");
        $this->newLine();

        foreach ($paliRows as $i => $pali) {
            $idx = $i + 1;
            $content = mb_substr((string) $pali->text, 0, 20, 'UTF-8');
            if ($content === '') {
                $this->warn("[{$idx}] skip empty text para={$pali->paragraph} word_begin={$pali->word_begin}");

                continue;
            }

            $payload = [
                'channel' => $channelUid,
                'sentences' => [[
                    'book_id' => $book,
                    'paragraph' => (int) $pali->paragraph,
                    'word_start' => (int) $pali->word_begin,
                    'word_end' => (int) $pali->word_end,
                    'content' => $content,
                ]],
            ];

            $status = 0;
            $body = null;

            try {
                $resp = Http::timeout(30)->withToken($token)->post($url, $payload);
                $status = $resp->status();
                $body = $resp->json();
                $written = $body['data']['count'] ?? null;
                $ok = $resp->successful() && ($body['ok'] ?? false) === true;
            } catch (\Throwable $e) {
                $ok = false;
                $written = null;
                $this->error("[{$idx}] HTTP exception: {$e->getMessage()}");
            }

            if ($ok) {
                $okCount++;
                $this->line("[{$idx}/{$paliRows->count()}] para={$pali->paragraph} ws={$pali->word_begin} HTTP={$status} written={$written}");
            } else {
                $failCount++;
                $this->error("[{$idx}] para={$pali->paragraph} ws={$pali->word_begin} HTTP={$status} FAILED ".mb_substr((string) json_encode($body, JSON_UNESCAPED_UNICODE), 0, 220));
            }

            if ($wait > 0) {
                sleep($wait);
            }

            // 拉取 worker 控制台输出和 laravel.log 新内容
            $mqErrors += $this->flushMqLog($process);
            $mqErrors += $this->flushLaravelLog();

            // 每 N 句报告一次 DB 指纹变化
            if ($idx % $showEvery === 0 || $idx === $paliRows->count()) {
                $snap = $this->snapshot($book, $channelUid);
                if ($snap['fingerprint'] !== $lastFingerprint) {
                    $lastFingerprint = $snap['fingerprint'];
                    $this->line('  [db] progress_rows='.$snap['progress_count'].' all_strlen='.$snap['progress_sum']
                        .' chapter_rows='.$snap['chapter_count'].' titles='.$snap['chapter_titles_count']);
                }
            }
        }

        // 等 worker 把队列全部消费完，保证最终 DB 快照准确
        $received = $okCount;
        if ($process !== null && $okCount > 0 && $drainTimeout > 0) {
            [$received, $drainErrors] = $this->waitForWorkerToDrain($process, $okCount, $drainTimeout);
            $mqErrors += $drainErrors;
        }

        sleep(1);
        $mqErrors += $this->flushMqLog($process);
        $mqErrors += $this->flushLaravelLog();

        $this->newLine();
        $this->info('== results ==');
        $after = $this->snapshot($book, $channelUid);

        $this->line('-- DB changes --');
        $this->table(['metric', 'before', 'after', 'delta'], [
            ['progress rows', (string) $before['progress_count'], (string) $after['progress_count'], (string) ($after['progress_count'] - $before['progress_count'])],
            ['progress all_strlen sum', (string) $before['progress_sum'], (string) $after['progress_sum'], (string) ($after['progress_sum'] - $before['progress_sum'])],
            ['progress_chapters rows', (string) $before['chapter_count'], (string) $after['chapter_count'], (string) ($after['chapter_count'] - $before['chapter_count'])],
            ['progress_chapters titles', (string) $before['chapter_titles_count'], (string) $after['chapter_titles_count'], (string) ($after['chapter_titles_count'] - $before['chapter_titles_count'])],
        ]);

        $this->line('-- progress_chapters sample (title 应为写入内容的前 20 字符) --');
        $samples = ProgressChapter::where('book', $book)
            ->where('channel_id', $channelUid)
            ->orderBy('para')
            ->limit(10)
            ->get(['para', 'title', 'progress', 'updated_at'])
            ->toArray();
        if ($samples) {
            $this->table(
                ['para', 'title', 'progress', 'updated_at'],
                array_map(fn ($r) => [
                    (string) $r['para'],
                    mb_substr((string) $r['title'], 0, 30, 'UTF-8'),
                    (string) $r['progress'],
                    (string) $r['updated_at'],
                ], $samples)
            );
        } else {
            $this->line('  (no rows)');
        }

        $this->newLine();
        $this->line('-- summary --');
        $this->line("  api writes ok    : {$okCount}");
        $this->line("  api writes failed: {$failCount}");
        $this->line("  worker processed : {$received}/{$okCount}");
        $this->line("  error lines (mq console + laravel.log): {$mqErrors}");
        $this->line('  laravel.log suppressed noise: '.$this->laravelSuppressed);
        $dbChanged = $after['fingerprint'] !== $before['fingerprint'];
        $this->line('  db changed       : '.($dbChanged ? 'yes' : 'no'));

        if ($okCount > 0 && ! $dbChanged) {
            $this->error('⚠ 有写入成功但 progress / progress_chapters 完全没有变化 —— 说明 MqProgress 没有真正更新 DB（疑似 bug）。');
        }

        if ($process && ! $keep) {
            $this->info('stopping mq:progress worker...');
            $this->stopWorker($process);
        } elseif ($keep) {
            $this->warn('--keep: worker left running');
        }

        return $failCount === 0 && $mqErrors === 0 ? 0 : 1;
    }

    /** 从 token 解析账号 uid */
    private function decodeUid(string $token): ?string
    {
        try {
            $jwt = JWT::decode($token, new Key(AuthService::getJwtKey(), 'HS512'));

            return $jwt->uid ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** 默认选账号名下第一个 translation channel */
    private function resolveChannel(string $accountUid): ?string
    {
        $candidates = Channel::where('owner_uid', $accountUid)
            ->where('type', 'translation')
            ->where('status', 30)
            ->orderBy('name')
            ->get(['uid', 'name']);

        if ($candidates->isEmpty()) {
            return null;
        }
        $this->line('candidate translation channels:');
        foreach ($candidates as $c) {
            $this->line("  - {$c->uid} ({$c->name})");
        }

        return $candidates->first()->uid;
    }

    private function snapshot(int $book, string $channelUid): array
    {
        $progressQuery = Progress::where('book', $book)->where('channel_id', $channelUid);
        $chapterQuery = ProgressChapter::where('book', $book)->where('channel_id', $channelUid);

        $snap = [
            'progress_count' => (clone $progressQuery)->count(),
            'progress_sum' => (int) (clone $progressQuery)->sum('all_strlen'),
            'progress_max_updated_at' => (string) (clone $progressQuery)->max('updated_at'),
            'chapter_count' => (clone $chapterQuery)->count(),
            'chapter_titles_count' => (clone $chapterQuery)->whereNotNull('title')->where('title', '!=', '')->count(),
            'chapter_max_updated_at' => (string) (clone $chapterQuery)->max('updated_at'),
        ];
        $snap['fingerprint'] = implode('|', [
            $snap['progress_count'],
            $snap['progress_sum'],
            $snap['progress_max_updated_at'],
            $snap['chapter_count'],
            $snap['chapter_titles_count'],
            $snap['chapter_max_updated_at'],
        ]);

        return $snap;
    }

    /** @return array<int, array{0: string, 1: string}> */
    private function snapshotRows(array $snap): array
    {
        return [
            ['progress rows', (string) $snap['progress_count']],
            ['progress all_strlen sum', (string) $snap['progress_sum']],
            ['progress max updated_at', $snap['progress_max_updated_at']],
            ['progress_chapters rows', (string) $snap['chapter_count']],
            ['progress_chapters titles', (string) $snap['chapter_titles_count']],
            ['progress_chapters max updated_at', $snap['chapter_max_updated_at']],
        ];
    }

    private function startWorker(): Process
    {
        $this->resetFile($this->mqLogPath);

        // 数组形式会让 Symfony 自动加 `exec`，stop() 的信号能直接打到 php 进程，不会留下孤儿 worker。
        $process = new Process(['php', 'artisan', 'mq:progress'], base_path(), null, null, null);
        $process->setTimeout(null);
        $process->start();

        return $process;
    }

    /** 把 worker 管道里的增量输出转存到 mq 日志文件（stdout 行缓冲，管道可实时读到） */
    private function drainWorker(Process $process): void
    {
        $out = $process->getIncrementalOutput();
        $err = $process->getIncrementalErrorOutput();
        if ($out !== '') {
            file_put_contents($this->mqLogPath, $out, FILE_APPEND);
        }
        if ($err !== '') {
            file_put_contents($this->mqLogPath, $err, FILE_APPEND);
        }
    }

    /**
     * 等待 worker 处理完 expected 条消息（以控制台里的 "Received book=" 行计数）。
     *
     * @return array{0: int, 1: int} [已处理条数, 期间命中的错误行数]
     */
    private function waitForWorkerToDrain(Process $process, int $expected, int $timeoutSeconds): array
    {
        $deadline = time() + $timeoutSeconds;
        $lastLog = 0;
        $received = 0;
        $errors = 0;

        while (true) {
            $before = $this->fileSize($this->mqLogPath);
            $this->drainWorker($process);
            $after = $this->fileSize($this->mqLogPath);

            if ($after > $before) {
                $errors += $this->scanFileErrors($this->mqLogPath, $before, $after);
                // 静默推进 offset，避免结束时把几千行 drain 内容刷屏
                $this->mqLogOffset = $after;
            }

            $received = substr_count((string) @file_get_contents($this->mqLogPath), 'Received book=');
            $now = time();

            if ($received >= $expected) {
                $this->info("worker drained all messages ({$received}/{$expected})");

                break;
            }
            if (! $process->isRunning()) {
                $this->warn("worker exited before draining ({$received}/{$expected})");

                break;
            }
            if ($now >= $deadline) {
                $this->warn("drain timeout after {$timeoutSeconds}s ({$received}/{$expected})");

                break;
            }
            if ($now - $lastLog >= 30) {
                $this->line("  [drain] worker processed {$received}/{$expected} messages, waiting...");
                $lastLog = $now;
            }

            sleep(2);
        }

        return [$received, $errors];
    }

    /** 统计文件 [start,end) 字节区间内命中错误关键词的行数 */
    private function scanFileErrors(string $path, int $start, int $end): int
    {
        $fh = @fopen($path, 'r');
        if (! $fh) {
            return 0;
        }
        fseek($fh, $start);
        $content = (string) fread($fh, $end - $start);
        fclose($fh);

        $errors = 0;
        foreach (preg_split('/\r\n|\r|\n/', $content) ?: [] as $line) {
            if ($line !== '' && $this->hasErrorMarker($line)) {
                $errors++;
            }
        }

        return $errors;
    }

    private function stopWorker(Process $process): void
    {
        if (! $process->isRunning()) {
            return;
        }
        try {
            $process->stop(5, 15);
        } catch (\Throwable $e) {
            // 忽略超时，下面强制杀
        }
        if ($process->isRunning()) {
            try {
                $process->stop(0, 9);
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    /** 输出 worker 控制台文件新内容，返回命中的错误行数 */
    private function flushMqLog(?Process $process = null): int
    {
        if ($process !== null) {
            $this->drainWorker($process);
        }

        return $this->flushFile($this->mqLogPath, $this->mqLogOffset, 'mq', true);
    }

    private function flushLaravelLog(): int
    {
        return $this->flushFile(storage_path('logs/laravel.log'), $this->laravelLogOffset, 'laravel', false);
    }

    /** @param  bool  $showAll  true=逐行全打印；false=只打印相关/错误行，其余折叠计数 */
    private function flushFile(string $path, int &$offset, string $label, bool $showAll): int
    {
        $size = $this->fileSize($path);
        if ($size <= $offset) {
            return 0;
        }
        $fh = @fopen($path, 'r');
        if (! $fh) {
            return 0;
        }
        fseek($fh, $offset);
        $content = (string) fread($fh, $size - $offset);
        $offset = $size;
        fclose($fh);

        $errors = 0;
        $lines = preg_split('/\r\n|\r|\n/', $content) ?: [];
        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }
            $hasError = $this->hasErrorMarker($line);
            if ($hasError) {
                $errors++;
            }
            if ($showAll || $hasError || $this->hasRelevantMarker($line)) {
                if ($hasError) {
                    $this->error("  [{$label}] {$line}");
                } else {
                    $this->line("  <comment>[{$label}]</comment> {$line}");
                }
            } elseif ($label === 'laravel') {
                $this->laravelSuppressed++;
            }
        }

        return $errors;
    }

    private function hasErrorMarker(string $line): bool
    {
        foreach (self::ERROR_MARKERS as $marker) {
            if (str_contains($line, $marker)) {
                return true;
            }
        }

        return false;
    }

    private function hasRelevantMarker(string $line): bool
    {
        foreach (self::LARAVEL_RELEVANT as $marker) {
            if (str_contains($line, $marker)) {
                return true;
            }
        }

        return false;
    }

    private function resetFile(string $path): void
    {
        @file_put_contents($path, '');
    }

    private function fileSize(string $path): int
    {
        $size = @filesize($path);

        return $size === false ? 0 : $size;
    }
}
