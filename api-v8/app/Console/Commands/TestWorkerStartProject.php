<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class TestWorkerStartProject extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan test:worker.start.project 0c3d2f69-1098-428b-95db-f1183667c799
     * @var string
     */
    protected $signature = 'test:worker.start.project {project} {--token=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $appUrl = config('app.url');
        $projectId = $this->argument('project');
        $token = $this->option('token');
        // 如果 role 选项未提供（为空），提示用户输入
        if (empty($token)) {
            $token = $this->ask('Please enter the user token:');
        }

        $status = $this->choice(
            'Which framework do you prefer?',
            ['published', 'restarted', 'stop'],
            0 // 默认选择 Laravel（索引 0）
        );

        $response = Http::withToken($token)
            ->get($appUrl . "/api/v2/task?view=project&project_id={$projectId}&status=all&order=order&dir=asc");
        if ($response->failed()) {
            $this->error('task read fail' . $response->json('message'));
            Log::error('task read fail', ['data' => $response->body()]);
            return 1;
        }
        $tasks = $response->json()['data']['rows'];
        foreach ($tasks as $key => $task) {
            $this->info("[{$key}]task " . $task['title'] . ' status ' . $task['status']);
            $response = Http::withToken($token)
                ->patch($appUrl . "/api/v2/task-status/" . $task['id'], ['status' => $status]);
            if ($response->failed()) {
                $this->error('task status fail' . $response->json('message'));
                Log::error('task status fail', ['data' => $response->body()]);
            }
            $this->info("[{$key}]task status changed {$status}");
        }
        return 0;
    }
}
