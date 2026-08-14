<?php

namespace App\Http\Resources;

use App\Http\Api\MdRender;
use App\Http\Api\ProjectApi;
use App\Http\Api\StudioApi;
use App\Http\Api\TaskApi;
use App\Http\Api\UserApi;
use App\Models\TaskAssignee;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $htmlRender = new MdRender([
            'mode' => 'read',
            'format' => 'react',
            'footnote' => true,
            'origin' => $request->input('origin', true),
            'paragraph' => $request->input('paragraph', false),
        ]);
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'category' => $this->category,
            'progress' => $this->progress,
            'parent_id' => $this->parent_id,
            'parent' => TaskApi::getById($this->parent_id),
            'roles' => $this->roles,
            'executor_id' => $this->executor_id,
            'executor_relation_task_id' => $this->executor_relation_task_id,
            'executor_relation_task' => TaskApi::getById($this->executor_relation_task_id),
            'pre_task' => TaskApi::getPreTasks($this->id),
            'next_task' => TaskApi::getNextTasks($this->id),
            'is_milestone' => $this->is_milestone,
            'project_id' => $this->project_id,
            'project' => ProjectApi::getById($this->project_id),
            'owner_id' => $this->owner_id,
            'owner' => StudioApi::getById($this->owner_id),
            'editor_id' => $this->editor_id,
            'editor' => UserApi::getByUuid($this->editor_id),
            'order' => $this->order,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'started_at' => $this->started_at,
            'finished_at' => $this->finished_at,
        ];
        $assignees = TaskAssignee::where('task_id', $this->id)->select('assignee_id')->get();
        if (count($assignees) > 0) {
            $assignees_id = [];
            foreach ($assignees as $key => $value) {
                $assignees_id[] = $value->assignee_id;
            }
            $data['assignees_id'] = $assignees_id;
            $data['assignees'] = UserApi::getListByUuid($assignees_id);
        }
        if (! empty($this->description)) {
            $data['html'] = $htmlRender->convert($this->description, []);
        }

        if (Str::isUuid($this->executor_id)) {
            $data['executor'] = UserApi::getByUuid($this->executor_id);
        }

        return $data;
    }
}
