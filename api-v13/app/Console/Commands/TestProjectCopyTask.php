<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TestProjectCopyTask extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan test:project.copy.task project-50 dd9bcba8-ad3f-4082-9b52-4f5f8acdbd5f visuddhinanda
     *
     * @var string
     */
    protected $signature = 'test:project.copy.task {project} {task} {studio} {--token=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '建立project 并复制task';

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
        $projectTitle = $this->argument('project');
        $taskId = $this->argument('task');
        $studioName = $this->argument('studio');
        $token = $this->option('token');

        // 如果 role 选项未提供（为空），提示用户输入
        if (empty($token)) {
            $token = $this->ask('Please enter the user token:');
        }

        $taskCount = $this->ask('Please enter the task count:');
        $url = $appUrl.'/api/v2/project-tree';
        $this->info('create project '.$url);
        $projects = [];
        $rootId = Str::uuid();
        $projects[] = [
            'id' => $rootId,
            'title' => $projectTitle,
            'type' => 'instance',
            'parent_id' => '',
            'weight' => 0,
            'res_id' => $rootId,
        ];
        for ($i = 0; $i < $taskCount; $i++) {
            $uid = Str::uuid();
            $projects[] = [
                'id' => $uid,
                'title' => "{$projectTitle}_{$i}",
                'type' => 'instance',
                'parent_id' => $rootId,
                'weight' => 0,
                'res_id' => $uid,
            ];
        }
        $response = Http::withToken($token)
            ->post($url, [
                'studio_name' => $studioName,
                'data' => $projects,
            ]);
        if ($response->failed()) {
            $this->error('project create fail'.$response->json('message'));
            Log::error('project create fail', ['data' => $response->body()]);

            return 1;
        }

        $projectsData = $response->json()['data']['rows'];
        $this->info('project :'.count($projectsData));
        // 获取task
        $response = Http::withToken($token)
            ->get($appUrl.'/api/v2/task/'.$taskId);
        if ($response->failed()) {
            $this->error('task read fail'.$response->json('message'));
            Log::error('task read fail', ['data' => $response->body()]);

            return 1;
        }

        // 建立task
        $task = $response->json()['data'];
        $taskTitle = $task['title'];
        $this->info('task title:'.$task['title']);
        $tasks = [];
        foreach ($projectsData as $key => $project) {
            if ($project['isLeaf']) {
                $task['title'] = "{$taskTitle}_{$key}";
                $tasks[] = [
                    'project_id' => $project['id'],
                    'tasks' => [$task],
                ];
            }
        }

        $response = Http::withToken($token)
            ->post($appUrl.'/api/v2/task-group', [
                'data' => $tasks,
            ]);
        if ($response->failed()) {
            $this->error('task create fail'.$response->json('message'));
            Log::error('task create fail', ['data' => $response->body()]);

            return 1;
        }

        return 0;
    }
}
