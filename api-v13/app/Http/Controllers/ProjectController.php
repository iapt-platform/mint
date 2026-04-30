<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Api\AuthApi;
use App\Http\Api\StudioApi;
use App\Http\Api\ShareApi;

use App\Http\Resources\ProjectResource;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = AuthApi::current($request);
        if (!$user) {
            Log::error('notification auth failed {request}', ['request' => $request]);
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
                return $this->error('view', 200, 200);
                break;
        }

        if ($request->has('keyword')) {
            $table = $table->where('title', 'like', '%' . $request->input('keyword') . '%');
        }
        if ($request->has('status')) {
            $table = $table->whereIn('status', explode(',', $request->input('status')));
        }
        $count = $table->count();

        $sql = $table->toSql();
        Log::debug('sql', ['sql' => $sql]);

        $table = $table->orderBy($request->input('order', 'id'), $request->input('dir', 'asc'));

        $table = $table->skip($request->input("offset", 0))
            ->take($request->input('limit', 10000));

        $result = $table->get();

        return $this->ok(
            [
                "rows" => ProjectResource::collection($result),
                "count" => $count,
            ]
        );
    }

    public static function canEdit($user_uid, $studio_uid)
    {
        return $user_uid == $studio_uid;
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
        $studioId = StudioApi::getIdByName($request->input('studio_name'));
        if (!self::canEdit($user['user_uid'], $studioId)) {
            return $this->error(__('auth.failed'), 403, 403);
        }
        $new = Project::firstOrNew(['uid' => $request->input('id')]);
        if (Str::isUuid($request->input('id'))) {
            $new->uid = $request->input('id');
        } else {
            $new->uid =  Str::uuid();
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
            if (!is_array($parentPath)) {
                $parentPath = array();
            }
            array_push($parentPath, $new->parent_id);
            $new->path = json_encode($parentPath, JSON_UNESCAPED_UNICODE);
        }
        $new->save();

        return $this->ok(new ProjectResource($new));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        //
        return $this->ok(new ProjectResource($project));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project)
    {
        //
        $user = AuthApi::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'), 401, 401);
        }
        if (!self::canEdit($user['user_uid'], $project->owner_id)) {
            return $this->error(__('auth.failed'), 403, 403);
        }

        $project->title = $request->input('title');
        $project->description = $request->input('description');
        $project->parent_id = $request->input('parent_id');
        $project->editor_id = $user['user_uid'];
        $project->privacy = $request->input('privacy');


        if (Str::isUuid($request->input('parent_id'))) {
            $parentPath = Project::where('uid', $request->input('parent_id'))->value('path');
            $parentPath = json_decode($parentPath);
            if (!is_array($parentPath)) {
                $parentPath = array();
            }
            array_push($parentPath, $project->parent_id);
            $project->path = json_encode($parentPath, JSON_UNESCAPED_UNICODE);
        }
        $project->save();

        return $this->ok(new ProjectResource($project));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        //
    }
}
