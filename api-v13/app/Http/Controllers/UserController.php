<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\UserInfo;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * 列出用户
     *
     * 按 view 指定的口径返回用户列表，支持按昵称搜索、按角色过滤、分页与排序。
     * 注意：view 只识别 key 和 all 两个值，传其他值或不传会因为查询对象未初始化而报错。
     *
     * @queryParam view string required 查询口径。key=按关键字匹配用户名或昵称；all=全部用户。Enum: key,all
     * @queryParam key string view=key 时的关键字，同时模糊匹配 username 与 nickname
     * @queryParam search string 按昵称再做一次模糊过滤（nickname like %search%）
     * @queryParam role string 按角色过滤，匹配 role 这个 JSON 数组中是否包含该值。Example: admin
     * @queryParam order string 排序字段。Default: username
     * @queryParam dir string 排序方向。Enum: asc,desc Default: desc
     * @queryParam offset integer 分页起始位置。Default: 0
     * @queryParam limit integer 每页条数。Default: 20
     */
    public function index(Request $request)
    {
        // FIXME: switch 没有 default 分支，view 不是 key/all（含不传）时 $table 从未初始化，下面直接致命错误 500；
        // 建议补 default 返回 400 错误，或先给 $table 一个基础查询再按 view 叠加条件。
        switch ($request->input('view')) {
            case 'key':
                // FIXME: where(...)->orWhere(...) 没有分组括号，后面追加的 search / role 过滤会因 AND/OR 优先级
                // 对 orWhere 那半边失效；建议把这两个条件包进 where(function ($q) { ... }) 里。
                $table = UserInfo::where('username', 'like', '%'.$request->input('key').'%')
                    ->orWhere('nickname', 'like', '%'.$request->input('key').'%');

                break;

            case 'all':
                $table = UserInfo::where('id', '>', 0);
                break;
        }
        if ($request->has('search')) {
            $table = $table->where('nickname', 'like', '%'.$request->input('search').'%');
        }
        if ($request->has('role')) {
            $table = $table->whereJsonContains('role', $request->input('role'));
        }
        $count = $table->count();
        $table = $table->orderBy(
            $request->input('order', 'username'),
            $request->input('dir', 'desc')
        );
        $table = $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 20));
        $result = $table->get();

        return $this->ok(['rows' => UserResource::collection($result), 'count' => $count]);
    }

    /**
     * 新建用户（未实现）
     *
     * 路由已注册 POST /api/v2/user，但方法体为空，不做任何事也不返回内容。
     * 用户注册走认证相关接口，不走这里。
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * 查看单个用户
     *
     * 按 userid 查询用户资料并返回（id、用户名、昵称、邮箱、角色、头像等）。
     * 方法内没有任何鉴权，任何人都可以读取；查不到用户时会以空资源返回。
     *
     * @urlParam user integer required 用户的 userid（不是表自增主键 id）
     */
    public function show($id)
    {
        // FIXME: 没有任何鉴权，任何人可读取任意用户资料（含邮箱）；且 userid 不存在时会以空资源返回。
        // 建议按产品侧确认的可见性策略补鉴权，并对查不到的情况返回 404。
        $user = UserInfo::where('userid', $id)->first();

        return $this->ok(new UserResource($user));
    }

    /**
     * 更新用户资料或角色
     *
     * 两种互斥的用法：请求体里带 roles 时只更新角色（整体覆盖，以 JSON 存入 role 字段）；
     * 不带 roles 时更新昵称、头像、邮箱三个字段（未传的会被置空）。
     * 方法内没有任何鉴权，也没有校验，调用方需自行保证权限；userid 不存在时会报错。
     *
     * @urlParam user integer required 用户的 userid（不是表自增主键 id）
     *
     * @bodyParam roles array 角色列表，传了就只更新角色，整体覆盖原有角色
     * @bodyParam nickName string 昵称，未传 roles 时生效
     * @bodyParam avatar string 头像文件名，未传 roles 时生效
     * @bodyParam email string 邮箱，未传 roles 时生效
     */
    public function update(Request $request, $id)
    {
        // FIXME: 安全问题——本方法没有任何鉴权和参数校验，任意调用者都能改他人的 roles（提权）、邮箱、昵称、头像；
        // 需先由产品侧确认权限策略（本人仅可改资料、roles 仅管理员可改）后再补鉴权与 validate，同时处理 userid 不存在报错。
        $user = UserInfo::where('userid', $id)->first();
        if ($request->has('roles')) {
            $user->role = json_encode($request->input('roles'));
        } else {
            $user->nickname = $request->input('nickName');
            $user->avatar = $request->input('avatar');
            $user->email = $request->input('email');
        }
        $user->save();

        return $this->ok(new UserResource($user));
    }

    /**
     * 删除用户（未实现）
     *
     * 路由已注册 DELETE /api/v2/user/{user}，但方法体为空，不会删除任何数据也不返回内容。
     *
     * @urlParam user integer required 用户的 userid
     */
    public function destroy($id)
    {
        // FIXME: 方法体为空但路由已暴露 DELETE，调用方会以为删除成功；且同样没有鉴权。
        // 建议要么实现并加上鉴权，要么下掉该路由。
        //
    }
}
