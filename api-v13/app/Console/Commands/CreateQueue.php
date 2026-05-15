<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use App\Services\RabbitMQService;

#[Signature('app:create-queue')]
#[Description('create queues')]
class CreateQueue extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $this->info("queues create start");
        $created = app(RabbitMQService::class)->createQueue();
        $total = count($created);
        $this->info("[{$total}] queues created");
        foreach ($created as $key => $queue) {
            $this->line($queue);
        }
    }
}
