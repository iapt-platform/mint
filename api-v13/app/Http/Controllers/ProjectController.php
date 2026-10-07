<?php

namespace App\Http\Controllers;

use App\Http\Api\ShareApi;
use App\Http\Api\StudioApi;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    /**
     * 列出工程（project）
     *
     * 按 view 指定的口径返回工程列表，支持关键字搜索、状态过滤、排序和分页。
     * 必须登录，否则返回 401；view 缺失或不在枚举内时返回错误提示 view。
     * 未传 studio 时，studio 默认取当前用户自己。
     *
     * @queryParam view string required 查询口径：studio=某 studio 下的顶层工程，project-tree=某工程及其所有子工程，shared=分享给该 studio 的工程，community=其他 studio 的公开顶层工程。Enum: studio,project-tree,shared,community
     * @queryParam studio string studio 名称；不传则使用当前登录用户
     * @queryParam type string 工程类型，用于 studio/shared/community 口径。Enum: instance,article Default: instance
     * @queryParam project_id string view=project-tree 时必填，工程 uid；同时匹配该工程本身及 path 中包含它的子工程
     * @queryParam keyword string 标题模糊搜索关键字
     * @queryParam status string 工程状态，逗号分隔可多选
     * @queryParam order string 排序字段。Default: id
     * @queryParam dir string 排序方向。Enum: asc,desc Default: asc
     * @queryParam offset integer 分页偏移量。Default: 0
     * @queryParam limit integer 每页条数。Default: 10000
     */
    public function index(Request $request)
    {
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), 401, 401);
        }
        if ($request->has('studio')) {
            $studioId = StudioApi::getIdByName($request->input('studio'));
        } else {
            $studioId = $user['user_uid'];
        }

        switch ($request->input('view')) {
            case 'studio':
                $table = Project::where('owner_id', $studioId)
                    ->whereNull('parent_id')
                    ->where('type', $request->input('type', 'instance'));
                break;
            case 'project-tree':
                $table = Project::where('uid', $request->input('project_id'))
                    ->orWhereJsonContains('path', $request->input('project_id'));
                break;
            case 'shared':
                $type = $request->input('type', 'instance');
                $resList = ShareApi::getResList($studioId, $type === 'instance' ? 7 : 6);
                $resId = [];
                foreach ($resList as $res) {
                    $resId[] = $res['res_id'];
                }
                $table = Project::whereIn('uid', $resId);
                break;
            case 'community':
                $table = Project::where('owner_id', '<>', $studioId)
                    ->whereNull('parent_id')
                    ->where('privacy', 'public')
                    ->where('type', $request->input('type', 'instance'));
                break;
            default:
                // FIXME: view 非法时用 200 状态码表达错误，与项目其他接口的 4xx 约定不一致，
                // 建议改为 422 或 400。
                return $this->error('view', 200, 200);
                break;
        }

        if ($request->has('keyword')) {
            $table = $table->where('title', 'like', '%'.$request->input('keyword').'%');
        }
        if ($request->has('status')) {
            // FIXME: projects.status 是 jsonb 列，这里却按字符串 whereIn 比较，PostgreSQL 下匹配不到或报类型错误，
            // 建议改用 whereJsonContains 或把 status 规整为字符串列。
            $table = $table->whereIn('status', explode(',', $request->input('status')));
        }
        $count = $table->count();

        $table = $table->orderBy($request->input('order', 'id'), $request->input('dir', 'asc'));

        $table = $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 10000));

        $result = $table->get();

        return $this->ok(
            [
                'rows' => ProjectResource::collection($result),
                'count' => $count,
            ]
        );
    }

    /**
     * 判断用户是否有权编辑该 studio 下的工程
     *
     * 目前仅允许 studio 本人，即用户 uid 与 studio uid 相同。
     *
     * @param  string  $user_uid  用户 uuid
     * @param  string  $studio_uid  studio uuid
     */
    public static function canEdit($user_uid, $studio_uid)
    {
        return $user_uid == $studio_uid;
    }

    /**
     * 新建工程
     *
     * 在指定 studio 下创建工程。必须登录（否则 401），且当前用户必须是该 studio 本人
     * （studio_name 解析出的 id 等于当前用户 uid），否则返回 403。
     * 传入 parent_id 时会读取父工程的 path 并追加父工程 id，写入本工程的 path，
     * 以便按树查询子工程。
     *
     * @bodyParam studio_name string required studio 名称，用于解析归属 studio 并做权限校验
     * @bodyParam id string 工程 uid；为合法 uuid 时用它（已存在则更新该工程），否则服务端生成新 uuid
     * @bodyParam title string required 工程标题
     * @bodyParam description string 工程描述
     * @bodyParam parent_id string 父工程 uid，填写后作为子工程并自动计算 path
     * @bodyParam type string 工程类型。Enum: instance,article Default: instance
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
        $new = Project::firstOrNew(['uid' => $request->input('id')]);
        if (Str::isUuid($request->input('id'))) {
            $new->uid = $request->input('id');
        } else {
            $new->uid = Str::uuid();
        }
        $new->title = $request->input('title');
        $new->description = $request->input('description');
        $new->parent_id = $request->input('parent_id');
        $new->editor_id = $user['user_uid'];
        $new->owner_id = $studioId;
        $new->type = $request->input('type', 'instance');

        if (Str::isUuid($request->input('parent_id'))) {
            $parentPath = Project::where('uid', $request->input('parent_id'))->value('path');
            $parentPath = json_decode($parentPath);
            if (! is_array($parentPath)) {
                $parentPath = [];
            }
            array_push($parentPath, $new->parent_id);
            $new->path = json_encode($parentPath, JSON_UNESCAPED_UNICODE);
        }
        $new->save();

        return $this->ok(new ProjectResource($new));
    }

    /**
     * 查看单个工程
     *
     * 通过路由模型绑定按工程 uid 取出工程并返回详情，工程不存在时返回 404。
     * 该接口不做登录与权限校验，私有工程同样可被读取。
     *
     * @urlParam project string required 工程 uid
     */
    public function show(Project $project)
    {
        // FIXME: 该接口完全不鉴权，privacy=private 的工程也能被任意匿名用户按 uid 读取，
        // 建议补上登录校验并对私有工程限制为拥有者或被分享者可见。
        return $this->ok(new ProjectResource($project));
    }

    /**
     * 修改工程
     *
     * 整体覆盖式更新：title、description、parent_id、privacy 会无条件按请求体写入，
     * 未传的字段会被置空，调用方需回传完整数据。必须登录（否则 401），
     * 且只有工程拥有者本人可以修改，否则返回 403。
     * 传入合法的 parent_id 时会按父工程重新计算 path。
     *
     * @urlParam project string required 工程 uid
     *
     * @bodyParam title string required 工程标题
     * @bodyParam description string 工程描述
     * @bodyParam parent_id string 父工程 uid；为合法 uuid 时重算 path
     * @bodyParam privacy string 隐私性：public 表示社区可见。Enum: private,public Default: private
     */
    public function update(Request $request, Project $project)
    {
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), 401, 401);
        }
        if (! self::canEdit($user['user_uid'], $project->owner_id)) {
            return $this->error(__('auth.failed'), 403, 403);
        }

        // FIXME: 以下字段无条件整体覆盖，前端只想改单个字段时会把其余字段清空，且 privacy 未做白名单校验，
        // 建议改为 $request->has() 逐字段更新并校验 privacy 只能为 private|public。
        $project->title = $request->input('title');
        $project->description = $request->input('description');
        $project->parent_id = $request->input('parent_id');
        $project->editor_id = $user['user_uid'];
        $project->privacy = $request->input('privacy');

        if (Str::isUuid($request->input('parent_id'))) {
            $parentPath = Project::where('uid', $request->input('parent_id'))->value('path');
            $parentPath = json_decode($parentPath);
            if (! is_array($parentPath)) {
                $parentPath = [];
            }
            array_push($parentPath, $project->parent_id);
            $project->path = json_encode($parentPath, JSON_UNESCAPED_UNICODE);
        }
        $project->save();

        return $this->ok(new ProjectResource($project));
    }

    /**
     * 删除工程
     *
     * 尚未实现：方法体为空，不会删除任何数据，也不返回任何内容（响应为 200 空 body）。
     *
     * @urlParam project string required 工程 uid
     */
    public function destroy(Project $project)
    {
        //
    }
}
