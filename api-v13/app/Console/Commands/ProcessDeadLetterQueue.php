<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;

class ProcessDeadLetterQueue extends Command
{
    /**
     * The name and signature of the console command.
     * 查看死信队列消息
     * php artisan rabbitmq:process-dlq orders_dlq
     *
     * 重新入队死信消息
     * php artisan rabbitmq:process-dlq orders_dlq --requeue
     *
     * 删除死信消息
     * php artisan rabbitmq:process-dlq orders_dlq --delete
     * @var string
     */
    protected $signature = 'rabbitmq:process-dlq {dlq_name} {--requeue} {--delete}';
    protected $description = '处理死信队列中的消息';

    public function handle()
    {
        $dlqName = $this->argument('dlq_name');
        $requeue = $this->option('requeue');
        $delete = $this->option('delete');

        $config = config('queue.connections.rabbitmq');
        $connection = new AMQPStreamConnection(
            $config['host'],
            $config['port'],
            $config['user'],
            $config['password'],
            $config['virtual_host']
        );

        $channel = $connection->channel();

        $this->info("开始处理死信队列: {$dlqName}");

        $messageCount = 0;

        while (true) {
            $msg = $channel->basic_get($dlqName, false);

            if (!$msg) {
                break; // 队列为空
            }

            $messageCount++;
            $data = json_decode($msg->body, true);

            $this->info("处理第 {$messageCount} 条死信消息");
            $this->line("原始队列: " . ($data['queue'] ?? 'unknown'));
            $this->line("失败原因: " . ($data['failure_reason'] ?? 'unknown'));
            $this->line("失败时间: " . ($data['failed_at'] ?? 'unknown'));

            if ($requeue) {
                // 重新入队到原始队列
                $originalQueue = $data['queue'] ?? null;
                if ($originalQueue && isset($data['original_message'])) {
                    $requeueMsg = new AMQPMessage(
                        json_encode($data['original_message']),
                        ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
                    );

                    $channel->basic_publish($requeueMsg, '', $originalQueue);
                    $this->info("消息已重新入队到: {$originalQueue}");
                }
            }

            if ($delete || $requeue) {
                $msg->ack();
            } else {
                // 只是查看，不删除
                $msg->nack(false, true);
            }
        }

        $this->info("死信队列处理完成，共处理 {$messageCount} 条消息");

        $channel->close();
        $connection->close();

        return 0;
    }
}
