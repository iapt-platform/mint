<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\TaskRelation;
use App\Models\TaskAssignee;

use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;
use App\Http\Api\AuthApi;

use Illuminate\Support\Str;

class TaskGroupController extends Controller
{
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

        $user = AuthApi::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'), 401, 401);
        }
        //获取全部的project_id
        $input = $request->get(key: 'data');
        $id = [];
        foreach ($input as $key => $value) {
            $id[$value['project_id']] = 1;
        }
        $projectsId = array_keys($id);
        //鉴权
        $projects = Project::whereIn('uid', $projectsId)
            ->select(['uid', 'owner_id'])->get();
        foreach ($projects as $key => $project) {
            $id[$project->uid] = $project->owner_id;
            if (!TaskController::canEdit($user['user_uid'], $project->owner_id)) {
                Log::error(__('auth.failed'), ['user' => $user['user_uid'], 'owner' => $project->owner_id]);
                return $this->error(__('auth.failed'), 403, 403);
            }
        }

        $data = [];
        $trData = [];
        $taData = [];
        foreach ($input as $key => $project) {
            # code...
            $projectData = [];
            $tasks = [];
            $taskRelationData = [];
            $taskAssigneesData = [];
            foreach ($project['tasks'] as $key => $task) {
                $uuid = Str::uuid();
                $tasks[$task['id']] = $uuid;
                $projectData[] = [
                    'id' => $uuid,
                    'old_id' => $task['id'],
                    'title' => $task['title'],
                    'type' => 'instance',
                    'category' => $task['category'],
                    'status' => $task['status'],
                    'description' => $task['description'],
                    'order' => $task['order'],
                    'parent_id' => $task['parent_id'],
                    'project_id' => $project['project_id'],
                    'owner_id' => $id[$project['project_id']],
                    'editor_id' => $user['user_uid'],
                    'creator_id' => $user['user_uid'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            foreach ($projectData as $key => $value) {
                if ($value['parent_id']) {
                    $found = array_filter($projectData, function ($element) use ($value) {
                        return $element['old_id'] === $value['parent_id'];
                    });
                    if (count($found) > 0) {
                        $projectData[$key]['parent_id'] = $found[0]['id'];
                    }
                }
            }
            foreach ($projectData as $key => $value) {
                unset($projectData[$key]['old_id']);
            }
            $data = [...$data, ...$projectData];

            //处理 task relation
            $tasksId = array_keys($tasks);
            $taskRelations = TaskRelation::whereIn('task_id', $tasksId)
                ->get();
            foreach ($taskRelations as $key => $value) {
                $taskRelationData[] = [
                    'task_id' => $tasks[$value->task_id],
                    'next_task_id' => $tasks[$value->next_task_id],
                    'editor_id' => $user['user_uid'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $trData = [...$trData, ...$taskRelationData];

            //处理 task assignee
            $ta = TaskAssignee::whereIn('task_id', $tasksId)->get();
            foreach ($ta as $key => $value) {
                $taskAssigneesData[] = [
                    'id' => Str::uuid(),
                    'task_id' => $tasks[$value->task_id],
                    'assignee_id' => $value->assignee_id,
                    'editor_id' => $user['user_uid'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $taData = [...$taData, ...$taskAssigneesData];
        }

        $taskOk = Task::insert($data);
        $taskRelationOk = TaskRelation::insert($trData);
        $taskAssigneeOk = TaskAssignee::insert($taData);
        if ($taskOk && $taskRelationOk && $taskAssigneeOk) {
            return $this->ok([
                'taskCount' => count($data),
                'taskRelationCount' => count($trData),
                'taskAssigneeCount' => count($taData),
            ]);
        } else {
            return $this->error('error', 200, 200);
        }
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task)
    {
        //
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
}
