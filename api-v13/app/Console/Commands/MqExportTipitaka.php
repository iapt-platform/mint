<?php

namespace App\Console\Commands;

use App\Http\Api\Mq;
use App\Services\V3\TipitakaExportQueue;
use App\Services\V3\TipitakaExportService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * 三藏下载（`/v3/tipitaka-exports`）的 worker：生成文件存进临时区（生产是 S3）。单部作品的队列 `export_tipitaka`
 * 与打包的队列 `export_tipitaka_bulk`（`--bulk`）各跑一个，大打包不会堵住单部作品的下载。
 *
 * 一次只处理一条（prefetch 1），生成完才 ack。最大的 channel 整个打包约 4 分钟（实测 cs6 html 217 秒，2026-11-22），
 * RabbitMQ 的 `consumer_timeout` 必须比它长（开发机 rabbitmq.conf 里是 92 小时），否则
 * 跑到一半连接被服务端断开。失败只写进导出状态（status = failed），用户可以重新发起。
 *
 * 不走队列直接生成一份（排查用）：`php artisan mq:export.tipitaka --id=<导出 id>`。
 */
#[Signature('mq:export.tipitaka {--bulk : 消费打包队列 export_tipitaka_bulk} {--id= : 不连队列，直接生成这一份}')]
#[Description('三藏下载：生成导出文件（队列 export_tipitaka / export_tipitaka_bulk）')]
class MqExportTipitaka extends Command
{
    public function handle(TipitakaExportService $exports): int
    {
        if ($id = $this->option('id')) {
            $exports->run((string) $id);

            return self::SUCCESS;
        }

        $queue = $this->option('bulk') ? TipitakaExportQueue::BULK_QUEUE : TipitakaExportQueue::QUEUE;
        $this->info(" [*] Waiting for {$queue}. To exit press CTRL+C");
        Mq::worker($queue, function ($message) use ($exports) {
            if (! is_object($message) || ! is_string($message->id ?? null)) {
                return 1;
            }
            $exports->run($message->id);
            $this->info("done {$message->id}");

            return 0;
        });

        return self::SUCCESS;
    }
}
