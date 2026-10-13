<?php

namespace App\Console\Commands;

use App\Services\OpenSearchService;
use Illuminate\Console\Command;

class ClearEmbeddingsCache extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected $signature = 'embeddings:clear {text? : 指定要清理的文本，不传则清理全部缓存}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '清理 Redis 中的 embedding 缓存';

    /**
     * 执行命令
     */
    public function handle(OpenSearchService $service)
    {
        $text = $this->argument('text');

        if ($text) {
            $ok = $service->clearEmbeddingCache($text);
            if ($ok) {
                $this->info("已清理指定文本的缓存: \"{$text}\"");
            } else {
                $this->warn("缓存不存在: \"{$text}\"");
            }
        } else {
            $service->clearAllEmbeddingCache();
            $this->info('已让所有 embedding 缓存失效，旧缓存 7 天内自然过期');
        }

        return 0;
    }
}
