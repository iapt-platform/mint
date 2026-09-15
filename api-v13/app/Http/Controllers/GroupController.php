<?php

namespace App\Http\Controllers;

use App\Http\Api\StudioApi;
use App\Http\Resources\GroupResource;
use App\Models\GroupInfo;
use App\Models\GroupMember;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $result = false;
        $indexCol = ['uid', 'name', 'description', 'owner', 'updated_at', 'created_at'];
        switch ($request->input('view')) {
            case 'studio':
                // 获取studio内所有group
                $user = AuthService::current($request);
                if (! $user) {
                    return $this->error(__('auth.failed'));
                }
                // 判断当前用户是否有指定的studio的权限
                $studioId = StudioApi::getIdByName($request->input('name'));
                if ($user['user_uid'] !== $studioId) {
                    return $this->error(__('auth.failed'));
                }

                $table = GroupInfo::select($indexCol);
                if ($request->input('view2', 'my') === 'my') {
                    $table = $table->where('owner', $studioId);
                } else {
                    // 我参加的group
                    $groupId = GroupMember::where('user_id', $studioId)
                        ->groupBy('group_id')
                        ->select('group_id')
                        ->get();
                    $table = $table->whereIn('uid', $groupId);
                    $table = $table->where('owner', '<>', $studioId);
                }
                break;
            case 'all':
                $table = GroupInfo::select($indexCol);
                break;
        }
        if ($request->has('search')) {
            $table = $table->where('name', 'like', '%'.$request->input('search').'%');
        }
        $count = $table->count();

        if ($request->input('view') === 'studio_list') {
            $table = $table->orderBy('count', 'desc');
        } else {
            $table = $table->orderBy(
                $request->input('order', 'updated_at'),
                $request->input('dir', 'desc')
            );
        }
        $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 1000));

        $result = $table->get();
        if ($result) {
            return $this->ok(['rows' => GroupResource::collection($result), 'count' => $count]);
        } else {
            return $this->error('没有查询到数据');
        }
    }

    /**
     * 获取我的，和协作channel数量
     *
     * @return Response
     */
    public function showMyNumber(Request $request)
    {
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'));
        }
        // 判断当前用户是否有指定的studio的权限
        $studioId = StudioApi::getIdByName($request->input('studio'));
        if ($user['user_uid'] !== $studioId) {
            return $this->error(__('auth.failed'));
        }
        // 我的
        $my = GroupMember::where('user_id', $studioId)->where('power', 0)->count();
        // 协作
        $collaboration = GroupMember::where('user_id', $studioId)->where('power', '<>', 0)->count();

        return $this->ok(['my' => $my, 'collaboration' => $collaboration]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'));
        }
        // 判断当前用户是否有指定的studio的权限
        if ($user['user_uid'] !== StudioApi::getIdByName($request->input('studio_name'))) {
            return $this->error(__('auth.failed'));
        }
        // 查询是否重复
        if (GroupInfo::where('name', $request->input('name'))->where('owner', $user['user_uid'])->exists()) {
            return $this->error(__('validation.exists', ['name']));
        }
        $studioId = StudioApi::getIdByName($request->input('studio_name'));
        $group = new GroupInfo;
        DB::transaction(function () use ($group, $request, $studioId) {
            $group->id = app('snowflake')->id();
            $group->uid = Str::uuid();
            $group->name = $request->input('name');
            $group->owner = $studioId;
            $group->create_time = time() * 1000;
            $group->modify_time = time() * 1000;
            $group->save();

            $newMember = new GroupMember;
            $newMember->id = app('snowflake')->id();
            $newMember->user_id = $studioId;
            $newMember->group_id = $group->uid;
            $newMember->power = 0;
            $newMember->group_name = $request->input('name');
            $newMember->save();
        });

        return $this->ok($group);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return Response
     */
    public function show(Request $request, $id)
    {
        //
        $indexCol = ['uid', 'name', 'description', 'owner', 'updated_at', 'created_at'];

        $result = GroupInfo::select($indexCol)->where('uid', $id)->first();
        if (! $result) {
            return $this->error('没有查询到数据');
        }
        if ($result->status < 30) {
            // 私有，判断权限
            $user = AuthService::current($request);
            if (! $user) {
                return $this->error(__('auth.failed'));
            }
            // 判断当前用户是否有指定的group的权限
            if ($user['user_uid'] !== $result->owner) {
                // 非所有者
                // 判断是否协作
                $power = GroupMember::where('group_id', $id)
                    ->where('user_id', $user['user_uid'])
                    ->value('power');
                if ($power === null) {
                    return $this->error(__('auth.failed'));
                }
            }
        }

        return $this->ok(new GroupResource($result));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, GroupInfo $group)
    {
        //
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'));
        }
        // 判断当前用户是否有修改权限
        if ($user['user_uid'] !== $group->owner) {
            return $this->error(__('auth.failed'));
        }
        $group->name = $request->input('name');
        $group->description = $request->input('description');
        if ($request->has('status')) {
            $group->status = $request->input('status');
        }
        $group->create_time = time() * 1000;
        $group->modify_time = time() * 1000;
        $group->save();

        return $this->ok($group);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Request $request, GroupInfo $group)
    {
        //
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'));
        }
        // 判断当前用户是否有指定的 group 的删除权限
        if ($user['user_uid'] !== $group->owner) {
            return $this->error(__('auth.failed'));
        }
        $delete = 0;
        DB::transaction(function () use ($group, $delete) {
            // 删除group member
            $memberDelete = GroupMember::where('group_id', $group->uid)->delete();
            $delete = $group->delete();
        });

        return $this->ok($delete);
    }
}
