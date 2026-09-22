<?php

namespace App\Http\Controllers;

use App\Http\Api\StudioApi;
use App\Http\Api\TaskApi;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAssignee;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    /**
     * 列出任务
     *
     * 按 view 指定的口径返回任务列表，支持按执行人、责任人（指派）、参与者、
     * 关键字、状态过滤，并支持排序与分页。必须登录，未登录返回 401。
     * 注意：view 取值不在枚举内时不会构造查询，会导致请求失败。
     *
     * @queryParam view string required 查询口径：all=全部有归属的任务，instance=实例任务（type=instance），studio=当前用户名下（owner_id=当前用户）的任务，project=某工程及其子工程下的任务。Enum: all,instance,studio,project
     * @queryParam project_id string view=project 时必填，工程 uid；同时匹配该工程本身及 path 中包含它的子工程
     * @queryParam executor_id_includes string 实际执行人 uid，逗号分隔，只返回执行人在其中的任务
     * @queryParam executor_id_not-includes string 实际执行人 uid，逗号分隔，排除执行人在其中的任务
     * @queryParam assignees_id_includes string 责任人（指派人）uid，逗号分隔，只返回指派给其中任一人的任务
     * @queryParam assignees_id_not-includes string 责任人（指派人）uid，逗号分隔，排除指派给其中任一人的任务
     * @queryParam assignees_id_null string 传该参数（任意值）表示只返回没有任何责任人的任务
     * @queryParam assignees_id_not-null string 传该参数（任意值）表示只返回至少有一个责任人的任务
     * @queryParam sign_up_equals string 值为 true 时只返回可领取的任务，即责任人和实际执行人都为空。Enum: true,false
     * @queryParam participants_id_includes string 参与者 uid，逗号分隔，实际执行人或责任人命中其一即返回
     * @queryParam participants_id_not-includes string 参与者 uid，逗号分隔，排除这些人参与的任务
     * @queryParam keyword string 标题模糊搜索关键字
     * @queryParam status string 任务状态，逗号分隔可多选；传 all 表示不过滤。数据库默认值为 pending
     * @queryParam order string 排序字段。Default: created_at
     * @queryParam dir string 排序方向。Enum: asc,desc Default: asc
     * @queryParam offset integer 分页偏移量。Default: 0
     * @queryParam limit integer 每页条数。Default: 1000
     */
    public function index(Request $request)
    {
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), 401, 401);
        }

        switch ($request->input('view')) {
            case 'all':
                $table = Task::whereNotNull('owner_id');
                break;
            case 'instance':
                $table = Task::where('type', 'instance');
                break;
            case 'studio':
                $table = Task::where('owner_id', $user['user_uid']);
                break;
            case 'project':
                $projects = Project::where('uid', $request->input('project_id'))
                    ->orWhereJsonContains('path', $request->input('project_id'))
                    ->select('uid')
                    ->get();
                $table = Task::whereIn('project_id', $projects);
                break;
            default:
                // FIXME: view 缺失或不在枚举内时 $table 未被赋值，后续 $table->count() 会致命错误，
                // 建议参照 ProjectController::index 在此直接返回参数错误响应。
                // code...
                break;
        }
        if ($request->has('executor_id_includes')) {
            $table = $table->whereIn(
                'executor_id',
                explode(',', $request->input('executor_id_includes'))
            );
        }
        if ($request->has('executor_id_not-includes')) {
            $table = $table->whereNotIn(
                'executor_id',
                explode(',', $request->input('executor_id_not-includes'))
            );
        }
        if ($request->has('assignees_id_includes')) {
            $assigneesId = explode(',', $request->input('assignees_id_includes'));
            $assigneesTasks = TaskAssignee::whereIn('assignee_id', $assigneesId)
                ->select('task_id')->get();
            $table = $table->whereIn('id', $assigneesTasks);
        }
        if ($request->has('assignees_id_not-includes')) {
            $assigneesId = explode(',', $request->input('assignees_id_not-includes'));
            $assigneesTasks = TaskAssignee::whereIn('assignee_id', $assigneesId)
                ->select('task_id')->get();
            $table = $table->whereNotIn('id', $assigneesTasks);
        }
        // 指派给
        if ($request->has('assignees_id_null')) {
            $table = $table->doesntHave('task_assignees');
        }
        if ($request->has('assignees_id_not-null')) {
            $table = $table->has('task_assignees');
        }

        // FIXME: 责任人现已存放在 task_assignees 表，tasks.assignees_id 列不再写入，
        // 这里的 whereNull('assignees_id') 形同虚设，建议改用 doesntHave('task_assignees')。
        if ($request->input('sign_up_equals') === 'true') {
            $table = $table->whereNull('assignees_id')
                ->whereNull('executor_id');
        }
        /**某人参与的 */
        if ($request->has('participants_id_includes')) {
            $id = explode(',', $request->input('participants_id_includes'));
            $tasks_id = TaskAssignee::whereIn('assignee_id', $id)->select('task_id')->get();
            $table = $table->where(function ($query) use ($id, $tasks_id) {
                $query->whereIn('executor_id', $id)
                    ->orWhereIn('id', $tasks_id);
            });
        }

        if ($request->has('participants_id_not-includes')) {
            $id = explode(',', $request->input('participants_id_not-includes'));
            $tasks_id = TaskAssignee::whereIn('assignee_id', $id)->select('task_id')->get();
            $table = $table->where(function ($query) use ($id, $tasks_id) {
                $query->whereNotIn('executor_id', $id)
                    ->orWhereNotIn('id', $tasks_id);
            });
        }

        if ($request->has('keyword')) {
            $table = $table->where('title', 'like', '%'.$request->input('keyword').'%');
        }
        if ($request->has('status') && $request->input('status') !== 'all') {
            $table = $table->whereIn('status', explode(',', $request->input('status')));
        }
        $count = $table->count();

        $table = $table->orderBy(
            $request->input('order', 'created_at'),
            $request->input('dir', 'asc')
        );

        $table = $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 1000));

        $result = $table->get();

        return $this->ok(
            [
                'rows' => TaskResource::collection(resource: $result),
                'count' => $count,
            ]
        );
    }

    /**
     * 新建任务
     *
     * 在指定 studio 下创建任务。必须登录（否则 401），且当前用户必须是该 studio 本人
     * （studio_name 解析出的 id 等于当前用户 uid），否则返回 403。
     * 新任务的 order 自动取同一 parent（无 parent 时取同一 project）下最大 order + 1，
     * 没有兄弟节点时为 1；owner_id 为该 studio，creator_id 与 editor_id 为当前用户。
     *
     * @bodyParam studio_name string required studio 名称，用于解析归属 studio 并做权限校验
     * @bodyParam id string 任务 uuid；为合法 uuid 时用它，否则服务端生成新 uuid
     * @bodyParam title string required 任务标题
     * @bodyParam project_id string 所属工程 uid
     * @bodyParam parent_id string 父任务 uuid，填写后为子任务
     * @bodyParam type string 任务类型。Default: project
     */
    public function store(Request $request)
    {
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), 401, 401);
        }
        $studioId = StudioApi::getIdByName($request->input('studio_name'));

        if (! self::canEdit($user['user_uid'], $studioId)) {
            return $this->error(__('auth.failed'), 403, 403);
        }
        // FIXME: 传入已存在的 id 时会命中旧任务并覆盖其 title/type/parent_id 并重算 order，
        // 而权限只校验了 studio_name 而非该任务自身归属，建议按 id 存在与否区分新建/更新并校验任务 owner。
        $new = Task::firstOrNew(
            [
                'id' => $request->input('id'),
            ],
            [
                'owner_id' => $studioId,
                'creator_id' => $user['user_uid'],
                'project_id' => $request->input('project_id'),
            ],
        );

        if (Str::isUuid($request->input('id'))) {
            $new->id = $request->input('id');
        } else {
            $new->id = Str::uuid();
        }
        $new->title = $request->input('title');
        $new->editor_id = $user['user_uid'];
        $new->parent_id = $request->input('parent_id');
        $new->type = $request->input('type');
        // 处理任务顺序
        if ($request->input('parent_id')) {
            $maxOrder = Task::where('parent_id', $request->input('parent_id'))
                ->max('order');
        } else {
            $maxOrder = Task::where('project_id', $request->input('project_id'))
                ->max('order');
        }
        if ($maxOrder === null) {
            $new->order = 1;
        } else {
            $new->order = $maxOrder + 1;
        }
        $new->save();

        return $this->ok(new TaskResource($new));
    }

    /**
     * 查看单个任务
     *
     * 通过路由模型绑定按任务 uuid 取出任务并返回详情，任务不存在时返回 404。
     * 该接口不做登录与权限校验，任何人都能读取。
     *
     * @urlParam task string required 任务 uuid
     */
    public function show(Task $task)
    {
        // FIXME: 该接口完全不鉴权，任何匿名用户按 uuid 即可读取任意任务详情，
        // 建议补上 AuthService::current 校验并按任务归属/可见性过滤。
        return $this->ok(new TaskResource($task));
    }

    /**
     * 修改任务
     *
     * 局部更新：只更新请求体里出现的字段，未出现的字段保持不变。必须登录（否则 401）；
     * 只有任务拥有者、实际执行人或责任人之一可以修改，否则返回 403。
     * 副作用：传 assignees_id 会先删除该任务全部责任人再整体重建；
     * 传 pre_task_id / next_task_id 会整体重写任务的前置 / 后续关联关系。
     * 每次保存都会把 editor_id 更新为当前用户。
     *
     * @urlParam task string required 任务 uuid
     *
     * @bodyParam title string 任务标题
     * @bodyParam description string 任务描述（markdown）
     * @bodyParam category string 类别，如 翻译、审稿、百科
     * @bodyParam progress integer 进度，0-100
     * @bodyParam assignees_id array 责任人 uid 数组，整体覆盖原有责任人；传空数组等于清空
     * @bodyParam roles array 领取该任务所需的角色要求，存为 json
     * @bodyParam executor_id string 实际执行人 uid
     * @bodyParam executor_relation_task_id string 执行人关联任务 uuid，表示执行人与该任务保持一致
     * @bodyParam project_id string 所属工程 uid
     * @bodyParam pre_task_id string 前置任务 uuid，逗号分隔，整体覆盖原有前置关系
     * @bodyParam next_task_id string 后续任务 uuid，逗号分隔，整体覆盖原有后续关系
     * @bodyParam is_milestone boolean 是否为里程碑
     * @bodyParam order integer 拖拽排序顺序
     */
    public function update(Request $request, Task $task)
    {
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), 401, 401);
        }
        if (! self::canUpdate($user['user_uid'], $task)) {
            return $this->error(__('auth.failed'), 403, 403);
        }
        if ($request->has('title')) {
            $task->title = $request->input('title');
        }
        if ($request->has('description')) {
            $task->description = $request->input('description');
        }
        if ($request->has('category')) {
            $task->category = $request->input('category');
        }
        if ($request->has('progress')) {
            $task->progress = $request->input('progress');
        }
        if ($request->has('assignees_id')) {
            $delete = TaskAssignee::where('task_id', $task->id)->delete();
            $assigneesData = [];
            foreach ($request->input('assignees_id') as $key => $id) {
                $assigneesData[] = [
                    'id' => Str::uuid(),
                    'task_id' => $task->id,
                    'assignee_id' => $id,
                    'editor_id' => $user['user_uid'],
                ];
            }
            TaskAssignee::insert($assigneesData);
        }
        if ($request->has('roles')) {
            $task->roles = json_encode($request->input('roles'), JSON_UNESCAPED_UNICODE);
        }
        if ($request->has('executor_id')) {
            $task->executor_id = $request->input('executor_id');
        }
        if ($request->has('executor_relation_task_id')) {
            $task->executor_relation_task_id = $request->input('executor_relation_task_id');
        }
        if ($request->has('project_id')) {
            $task->project_id = $request->input('project_id');
        }
        if ($request->has('pre_task_id')) {
            TaskApi::setRelationTasks(
                $task->id,
                explode(',', $request->input('pre_task_id')),
                $user['user_uid'],
                'pre'
            );
        }
        if ($request->has('next_task_id')) {
            TaskApi::setRelationTasks(
                $task->id,
                explode(',', $request->input('next_task_id')),
                $user['user_uid'],
                'next'
            );
        }
        if ($request->has('is_milestone')) {
            $task->is_milestone = $request->input('is_milestone');
        }
        if ($request->has('order')) {
            $task->order = $request->input('order');
        }

        $task->editor_id = $user['user_uid'];
        $task->save();

        return $this->ok(new TaskResource($task));
    }

    /**
     * 删除任务
     *
     * 必须登录（否则 401），且当前用户必须是任务拥有者，否则返回 403。
     * 删除成功返回 ok，失败返回 500。
     *
     * @urlParam task string required 任务 uuid
     */
    public function destroy(Request $request, Task $task)
    {
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), 401, 401);
        }
        // FIXME: tasks 表只有 owner_id 列，Task 模型也没有 owner 关系，$task->owner 恒为 null，
        // 导致删除接口对所有人恒返回 403；应改为 $task->owner_id，但需与下方 trashed() 一并修复。
        if (! self::canEdit($user['user_uid'], $task->owner)) {
            return $this->error(__('auth.failed'), 403, 403);
        }
        $task->delete();
        // FIXME: Task 模型未 use SoftDeletes（tasks 表也无 deleted_at 列），trashed() 会抛
        // BadMethodCallException；上面 owner 那条修好后就会走到这里，两处必须一起改（加软删除或改判 delete() 返回值）。
        if ($task->trashed()) {
            return $this->ok('ok');
        } else {
            return $this->error('fail', 500, 500);
        }
    }

    public static function canEdit($user_uid, $owner_uid)
    {
        return $user_uid === $owner_uid;
    }

    /**
     * 判断用户是否有权修改该任务
     *
     * 任务拥有者、实际执行人，或该任务的责任人之一，均可修改。
     *
     * @param  string  $user_uid  用户 uuid
     * @param  Task  $task  任务
     */
    public static function canUpdate($user_uid, $task): bool
    {
        if ($user_uid === $task->owner_id) {
            return true;
        }
        if ($user_uid === $task->executor_id) {
            return true;
        }
        if (TaskAssignee::where('task_id', $task->id)
            ->where('assignee_id', $user_uid)
            ->exists()
        ) {
            return true;
        }

        return false;
    }
}
