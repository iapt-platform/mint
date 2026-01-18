<?php

namespace App\Http\Api;

use App\Models\Task;
use App\Models\TaskRelation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use App\Tools\RedisClusters;
use Illuminate\Support\Str;

class TaskApi
{
    public static function getById($id)
    {
        if (!$id) {
            return null;
        };
        $task = Task::where('id', $id)->first();
        if ($task) {
            return [
                'id' => $id,
                'title' => $task->title,
                'description' => $task->description,
            ];
        } else {
            return null;
        }
    }

    public static function getListByIds($ids)
    {
        if (!$ids) {
            return null;
        };
        $tasks = Task::whereIn('id', $ids)->get();
        $output = array();
        foreach ($ids as $key => $id) {
            foreach ($tasks as $task) {
                if ($task->id === $id) {
                    $output[] = [
                        'id' => $id,
                        'title' => $task->title,
                        'description' => $task->description,
                        "executor" => UserApi::getByUuid($task->executor_id)
                    ];
                    continue;
                };
            }
        }
        return $output;
    }

    public static function setRelationTasks($taskId, $relationTasksId, $editor_id, $relation = 'pre')
    {
        if ($relation === 'pre') {
            $where = 'next_task_id';
            $task1 = 'task_id';
            $task2 = 'next_task_id';
        } else {
            $where = 'task_id';
            $task1 = 'next_task_id';
            $task2 = 'task_id';
        }
        TaskApi::removeTaskRelationRedisKey($taskId, $relation);
        $delete = TaskRelation::where($where, $taskId)
            ->delete();
        foreach ($relationTasksId as $key => $id) {
            if (Str::isUuid($taskId) && Str::isUuid($id)) {
                $data[] = [
                    $task1 => $id,
                    $task2 => $taskId,
                    'editor_id' => $editor_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        if (isset($data)) {
            TaskRelation::insert($data);
        }
        TaskApi::removeTaskRelationRedisKey($taskId, $relation);
    }
    public static function getRelationTasks($taskId, $relation = 'pre')
    {
        $key = TaskApi::taskRelationRedisKey($taskId, $relation);
        //Log::debug('task redis key=' . $key . ' has=' . RedisClusters::has($key));
        $data = RedisClusters::remember($key,  24 * 3600, function () use ($taskId, $relation) {
            Log::debug('getRelationTasks task=' . $taskId . ' relation=' . $relation);
            if ($relation === 'pre') {
                $where = 'next_task_id';
                $select = 'task_id';
            } else {
                $where = 'task_id';
                $select = 'next_task_id';
            }
            $tasks = TaskRelation::where($where, $taskId)
                ->select($select)->get();
            $tasksId = [];
            foreach ($tasks as $key => $task) {
                $tasksId[] = $task[$select];
            }
            return TaskApi::getListByIds($tasksId);
        });
        return $data;
    }

    public static function getNextTasks($taskId)
    {
        return TaskApi::getRelationTasks($taskId, 'next');
    }
    public static function getPreTasks($taskId)
    {
        return TaskApi::getRelationTasks($taskId, 'pre');
    }
    public static function removeTaskRelationRedisKey($taskId, $relation = 'pre')
    {
        //查询相关task
        $relations = TaskRelation::where('task_id', $taskId)
            ->orWhere('next_task_id', $taskId)
            ->select('task_id', 'next_task_id')->get();
        $relationsId = [];
        $relationsId[$taskId] = 1;
        foreach ($relations as $key => $value) {
            $relationsId[$value->task_id] = 1;
            $relationsId[$value->next_task_id] = 1;
        }
        foreach ($relationsId as $taskId => $value) {
            $key = TaskApi::taskRelationRedisKey($taskId, 'pre');
            RedisClusters::forget($key);
            $key = TaskApi::taskRelationRedisKey($taskId, 'next');
            RedisClusters::forget($key);
        }
    }
    public static function taskRelationRedisKey($taskId, $relation = 'pre')
    {
        return "task/relation/{$relation}/{$taskId}";
    }
}
