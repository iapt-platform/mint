<?php

namespace App\Http\Controllers;

use App\Http\Resources\GroupMemberResource;
use App\Models\GroupInfo;
use App\Models\GroupMember;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GroupMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'));
        }
        $result = false;
        $indexCol = ['id', 'user_id', 'group_id', 'power', 'level', 'status', 'updated_at', 'created_at'];
        switch ($request->input('view')) {
            case 'group':
                // 获取 group 内所有 成员
                // 判断当前用户是否有指定的 group 的权限
                if (GroupMember::where('group_id', $request->input('id'))
                    ->where('user_id', $user['user_uid'])
                    ->exists()
                ) {
                    $table = GroupMember::where('group_id', $request->input('id'));
                    // 当前用户角色
                    $power = GroupMember::where('group_id', $request->input('id'))
                        ->where('user_id', $user['user_uid'])
                        ->value('power');
                    $roles = ['owner', 'manager', 'member'];
                } else {
                    return $this->error(__('auth.failed'));
                }
                break;
            case 'user':
                // 获取当前用户参与的group列表
                $table = GroupMember::where('user_id', $user['user_uid']);
                break;
        }
        if (isset($_GET['search'])) {
            $table = $table->where('title', 'like', $_GET['search'].'%');
        }
        $count = $table->count();
        if (isset($_GET['order']) && isset($_GET['dir'])) {
            $table = $table->orderBy($_GET['order'], $_GET['dir']);
        } else {
            $table = $table->orderBy('created_at');
        }

        $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 1000));

        $result = $table->get();

        $output = [
            'rows' => GroupMemberResource::collection($result),
            'count' => $count,
        ];
        if (isset($power) && isset($roles[$power])) {
            $output['role'] = $roles[$power];
        }

        return $this->ok($output);
    }

    /**
     * 往团队里加一名普通成员。
     *
     * 只有该团队的 owner / manager 能调用（与 destroy 同口径）。
     *
     * @bodyParam user_id string required 要加入的用户 uid
     * @bodyParam group_id string required 团队 uid
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $currUser = AuthService::current($request);
        if (! $currUser) {
            return $this->error(__('auth.failed'), null, 401);
        }
        $validated = $request->validate([
            'user_id' => 'required',
            'group_id' => 'required',
        ]);
        $callerPower = GroupMember::where('group_id', $validated['group_id'])
            ->where('user_id', $currUser['user_uid'])
            ->value('power');
        if ($callerPower === null || $callerPower >= 2) {
            return $this->error(__('auth.failed'), null, 403);
        }
        // 查找重复的项目
        if (GroupMember::where('group_id', $validated['group_id'])->where('user_id', $validated['user_id'])->exists()) {
            return $this->error('member exists');
        }
        $newMember = new GroupMember;
        $newMember->id = app('snowflake')->id();
        $newMember->user_id = $validated['user_id'];
        $newMember->group_id = $validated['group_id'];
        $newMember->power = 2;
        $newMember->group_name = GroupInfo::find($validated['group_id'])->name;
        $newMember->save();

        return $this->ok(new GroupMemberResource($newMember));
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(GroupMember $groupMember)
    {
        //

    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, GroupMember $groupMember)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Request $request, GroupMember $groupMember)
    {
        //
        // 查看删除者有没有删除权限
        // 查询删除者的权限
        $currUser = AuthService::current($request);
        if (! $currUser) {
            return $this->error(__('auth.failed'));
        }

        $power = GroupMember::where('group_id', $groupMember->group_id)
            ->where('user_id', $currUser['user_uid'])
            ->select('power')->first();
        if (! $power || $power->power >= 2) {
            // 普通成员没有删除权限
            return $this->error(__('auth.failed'));
        }

        $delete = $groupMember->delete();

        return $this->ok($delete);
    }
}
