<?php

namespace App\Http\Controllers;

use App\Models\Like;
use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Http\Api\UserApi;
use App\Http\Resources\LikeResource;
use Illuminate\Support\Str;

class LikeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->input("view")) {
            case 'count':
                # code...
                $result = Like::where("target_id", $request->input("target_id"))
                    ->groupBy("type")
                    ->select("type")
                    ->selectRaw("count(*)")
                    ->get();
                $user = AuthService::current($request);
                if ($user) {
                    foreach ($result as $key => $value) {
                        $curr = Like::where([
                            "target_id" => $request->input("target_id"),
                            'type' => $value->type,
                            'user_id' => $user["user_uid"]
                        ])->first();
                        if ($curr) {
                            $result[$key]->selected = true;
                            $result[$key]->my_id = $curr->id;
                        }
                    }
                }
                return $this->ok($result);
                break;
            case 'target':
                $table = Like::where("target_id", $request->input("target_id"));
                break;
            default:
                # code...
                break;
        }
        if ($request->has("type")) {
            $table = $table->where('type', $request->input("type"));
        }
        $count = $table->count();
        $result = $table->get();
        return $this->ok([
            "rows" => LikeResource::collection($result),
            "count" => $count
        ]);
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
        $user = AuthService::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'));
        }
        $param = $request->all();
        $user_id = $request->input('user_id', $user["user_uid"]);
        $like = Like::firstOrNew(
            [
                'type' => $param['type'],
                'target_id' => $param['target_id'],
                'target_type' => $param['target_type'],
                'user_id' =>  $user_id,
            ],
            [
                'id' => Str::uuid(),
            ]
        );
        $like->save();
        $output = [
            'id' => $like->id,
            'type' => $param['type'],
            'target_id' => $param['target_id'],
            'target_type' => $param['target_type'],
            'user_id' => $user_id,
            'count' => Like::where('target_id', $param['target_id'])
                ->where('type', $param['type'])->count(),
            'selected' => true,
            'my_id' => $like->id,
            'user' => UserApi::getByUuid($user_id),
        ];
        return $this->ok($output);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Like  $like
     * @return \Illuminate\Http\Response
     */
    public function show(Like $like)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Like  $like
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Like $like)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Like  $like
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Like $like)
    {
        //
        $user = AuthService::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'));
        }
        if ($like->user_id === $user["user_uid"]) {
            //移除自己
            $delete = $like->delete();
            if ($delete) {
                $output = [
                    'type' => $like['type'],
                    'count' => Like::where('target_id', $like['target_id'])
                        ->where('type', $like['type'])->count(),
                    'selected' => false,
                ];
                return $this->ok($output);
            } else {
                $this->error('未知错误', 200, 200);
            }
        } else {
            return $this->error(_('auth.failed'), 403, 403);
        }
    }
    public function delete(Request $request)
    {
        if (!isset($_COOKIE["user_uid"])) {
            return $this->error("no login");
        }
        $param = [
            "id" => $request->input('id'),
            'user_id' => $_COOKIE["user_uid"]
        ];
        $del = Like::where($param)->delete();
        $count = Like::where('target_id', $request->input('target_id'))
            ->where('type', $request->input('type'))
            ->count();
        return $this->ok([
            'deleted' => $del,
            'count' => $count
        ]);
    }
}
