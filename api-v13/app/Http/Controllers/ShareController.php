<?php

namespace App\Http\Controllers;

use App\Http\Api\ShareApi;
use App\Http\Resources\ShareResource;
use App\Models\GroupInfo;
use App\Models\Share;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ShareController extends Controller
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
        $result = false;
        $role = 'member';
        $indexCol = ['id', 'res_id', 'res_type', 'power', 'updated_at', 'created_at'];
        switch ($request->input('view')) {
            case 'res':
                if (! $user) {
                    return $this->error(__('auth.failed'));
                }
                $table = Share::where('res_id', $request->input('id'));
                $power = ShareApi::getResPower($user['user_uid'], $request->input('id'), $table->value('res_type'));
                switch ($power) {
                    case 10:
                        $role = 'member';
                        break;
                    case 20:
                        $role = 'editor';
                        break;
                    case 30:
                        $role = 'owner';
                        break;
                }
                break;
            case 'group':
                if (! $user) {
                    return $this->error(__('auth.failed'));
                }
                // TODO 判断当前用户是否有指定的 group 的权限
                if (GroupInfo::where('uid', $request->input('id'))->where('owner', $user['user_uid'])->exists()) {
                    $role = 'owner';
                }
                $table = Share::where('cooperator_id', $request->input('id'));
                break;
        }
        if (isset($_GET['search'])) {
            // TODO 搜索资源标题
            $table = $table->where('title', 'like', $_GET['search'].'%');
        }
        $count = $table->count();
        if (isset($_GET['order']) && isset($_GET['dir'])) {
            $table = $table->orderBy($_GET['order'], $_GET['dir']);
        } else {
            $table = $table->orderBy('updated_at', 'desc');
        }

        $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 1000));

        $result = $table->get();
        // TODO 获取当前用户的身份

        if ($result) {
            return $this->ok(['rows' => ShareResource::collection($result), 'count' => $count, 'role' => $role]);
        } else {
            return $this->error('没有查询到数据');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
        foreach ($request->input('user_id') as $key => $value) {
            if (! Str::isUuid($value)) {
                continue;
            }
            $row = Share::where('cooperator_id', $value)
                ->where('res_id', $request->input('res_id'))->first();
            if (! $row) {
                $row = new Share;
                $row->id = app('snowflake')->id();
                $row->cooperator_id = $value;
                $row->res_id = $request->input('res_id');
                $row->res_type = $request->input('res_type');
                $row->create_time = time() * 1000;
            }
            $c_type = ['user' => 0, 'group' => 1];
            $row->cooperator_type = $c_type[$request->input('user_type')];
            switch ($request->input('role')) {
                case 'manager':
                case 'editor':
                    $row->power = 20;
                    break;
                case 'reader':
                    $row->power = 10;
                    break;
            }
            $row->modify_time = time() * 1000;
            $row->save();
        }

        return $this->ok(count($request->input('user_id')));
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Share $share)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, Share $share)
    {
        // 查询权限
        $currUser = AuthService::current($request);
        if (! $currUser) {
            return $this->error(__('auth.failed'));
        }

        $power = ShareApi::getResPower($currUser['user_uid'], $share->res_id, $share->res_type);
        if (! $power || $power <= 20) {
            // 普通成员没有删除权限
            return $this->error(__('auth.failed'));
        }
        switch ($request->input('role')) {
            case 'manager':
            case 'editor':
                $share->power = 20;
                break;
            case 'reader':
                $share->power = 10;
                break;
        }
        $share->modify_time = time() * 1000;
        $share->save();

        return $this->ok($share);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Request $request, Share $share)
    {
        // 查询权限
        $currUser = AuthService::current($request);
        if (! $currUser) {
            return $this->error(__('auth.failed'));
        }

        $power = ShareApi::getResPower($currUser['user_uid'], $share->res_id, $share->res_type);
        if (! $power || $power <= 20) {
            // 普通成员没有删除权限
            return $this->error(__('auth.failed'));
        }

        $delete = $share->delete();

        return $this->ok($delete);
    }
}
