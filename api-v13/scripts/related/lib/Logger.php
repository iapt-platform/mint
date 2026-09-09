<?php

/**
 * 重建工程专用日志器。
 *
 * 迭代流程是「脚本跑 → 日志和报告交给 LLM 评估 → 改规则 → 再跑」，所以日志的读者
 * 是模型不是人：宁可啰嗦，但必须结构化。unresolved() 记录的是脚本自己判定
 * 「我处理不了」的点，它们是下一轮规则迭代的输入，单独成文件。
 */
class RpLogger
{
    private $handle;

    private array $counters = [];

    /** @var array<int, array<string, mixed>> */
    private array $unresolved = [];

    private float $startedAt;

    public function __construct(
        private string $name,
        private string $reportDir,
        private bool $echoToStdout = true,
    ) {
        $this->startedAt = microtime(true);
        $this->handle = fopen($this->reportDir."/{$name}.log", 'w');
        $this->line('INFO', "==== $name 开始 ".date('Y-m-d H:i:s').' ====');
    }

    public function info(string $message): void
    {
        $this->line('INFO', $message);
    }

    public function warn(string $message): void
    {
        $this->line('WARN', $message);
    }

    public function error(string $message): void
    {
        $this->line('ERROR', $message);
    }

    /** 计数器：高频事件不逐条打印，只累计，收尾时汇总。 */
    public function count(string $key, int $n = 1): void
    {
        $this->counters[$key] = ($this->counters[$key] ?? 0) + $n;
    }

    public function counters(): array
    {
        return $this->counters;
    }

    /**
     * 脚本无法判定的点。type 是规则名，供下一轮按类型聚合决定要不要加规则。
     */
    public function unresolved(string $type, array $context): void
    {
        $this->unresolved[] = ['type' => $type] + $context;
        $this->count("unresolved.$type");
    }

    /** 收尾：写计数汇总与 unresolved 清单，返回汇总数组。 */
    public function finish(): array
    {
        $elapsed = round(microtime(true) - $this->startedAt, 1);
        ksort($this->counters);

        $this->line('INFO', '---- 计数汇总 ----');
        foreach ($this->counters as $key => $value) {
            $this->line('INFO', sprintf('  %-46s %10d', $key, $value));
        }

        $byType = [];
        foreach ($this->unresolved as $item) {
            $byType[$item['type']] = ($byType[$item['type']] ?? 0) + 1;
        }
        if ($byType !== []) {
            $this->line('WARN', '---- 未决问题（按类型） ----');
            foreach ($byType as $type => $n) {
                $this->line('WARN', sprintf('  %-46s %10d', $type, $n));
            }
        }

        $path = $this->reportDir."/{$this->name}.unresolved.json";
        rp_json_write($path, [
            'generated_at' => date('c'),
            'total' => count($this->unresolved),
            'by_type' => $byType,
            'items' => $this->unresolved,
        ]);

        $this->line('INFO', "未决清单：$path");
        $this->line('INFO', "==== {$this->name} 结束，耗时 {$elapsed}s ====");
        fclose($this->handle);

        return [
            'counters' => $this->counters,
            'unresolved_total' => count($this->unresolved),
            'unresolved_by_type' => $byType,
            'elapsed_seconds' => $elapsed,
        ];
    }

    private function line(string $level, string $message): void
    {
        $row = sprintf('[%s] %-5s %s', date('H:i:s'), $level, $message);
        fwrite($this->handle, $row."\n");
        if ($this->echoToStdout) {
            fwrite(STDOUT, $row."\n");
        }
    }
}
