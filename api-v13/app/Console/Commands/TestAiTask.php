<?php

namespace App\Console\Commands;

use App\Jobs\ProcessAITranslateJob;
use App\Models\AiModel;
use App\Models\TaskAssignee;
use Illuminate\Console\Command;

class TestAiTask extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan test:ai.task c77af42f-ffb5-48ae-af71-4c32e1c30dab
     * php artisan test:ai.task f42fa690-c590-400f-9de9-fbc81e838a5a
     *
     * @var string
     */
    protected $signature = 'test:ai.task {id} {--test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'test ai task';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $taskId = $this->argument('id');
        $taskAssignee = TaskAssignee::where('task_id', $taskId)
            ->select('assignee_id')->get();
        $aiAssistant = AiModel::whereIn('uid', $taskAssignee)->first();
        if ($aiAssistant) {
            $count = ProcessAITranslateJob::publish($taskId, $aiAssistant->uid);
            $this->info('publish total:'.$count);
        } else {
            $this->error('no ai assistant');
        }

        return 0;
    }
}
