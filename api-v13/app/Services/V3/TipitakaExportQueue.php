<?php

namespace App\Services\V3;

use App\Http\Api\Mq;

/**
 * 三藏下载的生成任务投递到 RabbitMQ，由 `mq:export.tipitaka` 消费。
 *
 * 分两个队列：单部作品（几秒钟）走 `export_tipitaka`，打包（分类 / 整个 channel，大的要一个多小时）
 * 走 `export_tipitaka_bulk`，各有自己的 worker——否则一个整库打包会把后面所有单部作品的下载堵上一小时。
 *
 * 单独成类只为测试能换掉它（`$this->mock(TipitakaExportQueue::class)`），不连真的 RabbitMQ。
 */
class TipitakaExportQueue
{
    public const QUEUE = 'export_tipitaka';

    public const BULK_QUEUE = 'export_tipitaka_bulk';

    public function push(string $exportId, bool $bulk): void
    {
        Mq::publish($bulk ? self::BULK_QUEUE : self::QUEUE, ['id' => $exportId]);
    }
}
