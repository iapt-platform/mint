<?php

/**
 * related_paragraphs 重建工程的公共引导。
 *
 * 这些脚本刻意留在 scripts/ 而不是 app/Console/Commands/：重建期间它们会被反复
 * 改写、丢弃、重跑，不属于应用的常驻命令面。导出器稳定后再收编成 artisan command。
 */

use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../../../vendor/autoload.php';

$app = require __DIR__.'/../../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

require __DIR__.'/Logger.php';

/** 本工程所有产物的落地目录。 */
function rp_report_dir(): string
{
    $dir = __DIR__.'/../report';
    if (! is_dir($dir)) {
        mkdir($dir, 0o755, true);
    }

    return $dir;
}

function rp_data_dir(): string
{
    return __DIR__.'/../data';
}

/** 命令行参数：--key=value / --flag，返回 [$options, $positional]。 */
function rp_args(array $argv): array
{
    $options = [];
    $positional = [];
    foreach (array_slice($argv, 1) as $arg) {
        if (str_starts_with($arg, '--')) {
            $body = substr($arg, 2);
            if (str_contains($body, '=')) {
                [$k, $v] = explode('=', $body, 2);
                $options[$k] = $v;
            } else {
                $options[$body] = true;
            }
        } else {
            $positional[] = $arg;
        }
    }

    return [$options, $positional];
}

/**
 * 表名白名单校验。这些脚本会把表名拼进 SQL（标识符不能用绑定参数），
 * 所以只允许我们自己的表族，避免手滑打错表名时打到生产数据。
 */
function rp_assert_table(string $table): string
{
    if (! preg_match('/^related_paragraphs(_[a-z0-9_]+)?$/', $table)) {
        throw new InvalidArgumentException("拒绝操作非 related_paragraphs 表族的表：$table");
    }

    return $table;
}

function rp_json_write(string $path, mixed $data): void
{
    file_put_contents(
        $path,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n"
    );
}

function rp_json_read(string $path): mixed
{
    if (! file_exists($path)) {
        throw new RuntimeException("找不到文件：$path");
    }

    return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
}
