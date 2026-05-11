<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Task;
use App\Models\TaskRelation;
use App\Models\TaskAssignee;
use App\Models\AiModel;
use App\Http\Resources\TaskResource;
use App\Services\AuthService;
use App\Http\Api\WatchApi;
use App\Http\Api\UserApi;



class TaskStatusController extends Controller
{
    protected $changeTasks = array();
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        //
    }

    /**
     *
     *
     * @param  string  $status
     * @param  string  $id
     * @return void
     */
    private function pushChange(string $status, string $id)
    {
        if (!isset($this->changeTasks[$status])) {
            $this->changeTasks[$status] = array();
        }
        $this->changeTasks[$status][] = $id;
    }
    private function getChange(string $status)
    {
        if (!isset($this->changeTasks[$status])) {
            $this->changeTasks[$status] = array();
        }
        return $this->changeTasks[$status];
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $id)
    {
        //
        $task = Task::findOrFail($id);
        $user = AuthService::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'), 401, 401);
        }
        if (!TaskController::canUpdate($user['user_uid'], $task)) {
            return $this->error(__('auth.failed'), 403, 403);
        }

        if (!$request->has('status')) {
            return $this->error('no status', 400, 400);
        }

        $task->status = $request->input('status');
        $task->editor_id = $user['user_uid'];
        $task->save();
        if ($task->type === 'workflow') {
            return $this->ok(
                [
                    "rows" => TaskResource::collection(resource: [$task]),
                    "count" => 1,
                ]
            );
        }
        switch ($request->input('status')) {
            case 'published':
                $this->pushChange('published', $id);
                # 开启子任务
                $children = Task::where('parent_id', $id)
                    ->where('status', 'pending')
                    ->select('id')->get();
                foreach ($children as $key => $child) {
                    $this->pushChange('published', $child->id);
                }
                break;
            case 'running':
                $task->started_at = now();
                $task->executor_id = $user['user_uid'];
                $task->save();
                $this->pushChange('running', $task->id);
                break;
            case 'restarted':
                $this->pushChange('restarted', $task->id);
                break;
            case 'quit':
                $this->pushChange('quit', $task->id);
                break;
            case 'done':
                $this->pushChange('done', $task->id);
                $task->finished_at = now();
                $preTask = [$task->id];
                //开启父任务
                if ($task->parent_id) {
                    $notCompleted = Task::where('parent_id', $task->parent_id)
                        ->where('id', '!=', $task->id)
                        ->where('status', '!=', 'done')
                        ->count();
                    if ($notCompleted === 0) {
                        //父任务已经完成
                        $preTask[] = $task->parent_id;
                        $this->pushChange('done', $task->parent_id);
                    }
                }
                //开启后置任务
                $nextTasks = TaskRelation::whereIn('task_id', $preTask)
                    ->select('next_task_id')->get();
                foreach ($nextTasks as $key => $value) {
                    $nextTask = Task::find($value->next_task_id);
                    if ($nextTask->status === 'pending') {
                        $this->pushChange('published', $value->next_task_id);
                    }
                }
                //开启后置任务的子任务
                $nextTasksChildren = Task::whereIn('parent_id', $this->getChange('published'))
                    ->where('status', 'pending')
                    ->select('id')->get();
                foreach ($nextTasksChildren as $child) {
                    $this->pushChange('published', $child->id);
                }

                $nextTasks = TaskRelation::whereIn('task_id', $preTask)
                    ->select('next_task_id')->get();
                foreach ($nextTasks as $key => $value) {
                    $nextTask = Task::find($value->next_task_id);
                    if ($nextTask->status === 'requested_restart') {
                        //$runningTask[] = $value->next_task_id;
                        $this->pushChange('running', $value->next_task_id);
                    }
                }
                break;
            case 'requested_restart':
                $this->pushChange('requested_restart', $task->id);
                //从新开启前置任务
                $preTasks = TaskRelation::where('next_task_id', $task->id)
                    ->select('task_id')->get();
                foreach ($preTasks as $key => $value) {
                    //$restartTask[] = $value->task_id;
                    $this->pushChange('restarted', $value->task_id);
                }
                break;
        }

        # auto start with ai assistant
        $autoStart = array_merge($this->getChange('published'), $this->getChange('restarted'));
        foreach ($autoStart as $taskId) {
            $taskAssignee = TaskAssignee::where('task_id', $taskId)
                ->select('assignee_id')->get();
            $aiAssistant = AiModel::whereIn('uid', $taskAssignee)->first();
            if ($aiAssistant) {
                $aiTask = Task::find($taskId);
                try {
                    $msgId = \App\Jobs\ProcessAITranslateJob::publish($taskId, $aiAssistant->uid);
                    $aiTask->executor_id = $aiAssistant->uid;
                    $aiTask->status = 'queue';
                    $this->pushChange('queue', $taskId);
                } catch (\Exception $e) {
                    $aiTask->status = 'pending';
                    Log::error('ai assistant start fail', [
                        'task' => $taskId,
                        'error' => $e->getMessage()
                    ]);
                } finally {
                    $aiTask->save();
                }
            }
        }

        $allChanged = [];
        $discussion = app(\App\Services\DiscussionService::class);
        foreach ($this->changeTasks as $key => $tasksId) {
            $allChanged = array_merge($allChanged, $tasksId);
            #change status in related
            $data = [
                'status' => $key,
                'editor_id' => $user['user_uid'],
                'updated_at' => now(),
            ];
            if ($key === 'done') {
                $data['finished_at'] = now();
            }
            if ($key === 'running') {
                $data['started_at'] = now();
            }
            if ($key === 'restart') {
                $data['finished_at'] = null;
            }
            if ($key === 'quit') {
                $data['executor_id'] = null;
            }
            Task::whereIn('id', $tasksId)
                ->update($data);

            try {
                //发送站内信
                $send = WatchApi::change(
                    resId: $tasksId,
                    from: $user['user_uid'],
                    message: "任务状态变为 {$key}",
                );
                Log::debug('watch message', [
                    'send-to' => $send,
                ]);
                $editor = UserApi::getByUuid($user['user_uid']);
                foreach ($tasksId as $taskId) {
                    $discussion->create([
                        'res_id' => $taskId,
                        'res_type' => 'task',
                        'title' => "{$editor['nickName']} 将任务状态变为 {$key}",
                        'content' => isset($msgId) ? "message id:{$msgId}" : null,
                        'editor_uid' => $user['user_uid'],
                    ]);
                }
            } catch (\Throwable $th) {
                Log::error($th->getMessage());
            }
        }

        //changed tasks
        $result = Task::whereIn('id', $allChanged)
            ->get();
        return $this->ok(
            [
                "rows" => TaskResource::collection(resource: $result),
                "count" => count($result),
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        //
    }

    public  function canEdit(string $user_uid, Task $task)
    {
        if ($user_uid === $task->owner_id || $user_uid === $task->executor_id) {
            return true;
        }
        $has = TaskAssignee::where('task_id', $task->id)
            ->where('assignee_id', $user_uid)->exists();
        if ($has) {
            return true;
        }
        return false;
    }
}
