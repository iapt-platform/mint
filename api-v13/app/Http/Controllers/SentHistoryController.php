<?php

namespace App\Http\Controllers;

use App\Http\Api\UserApi;
use App\Http\Resources\SentHistoryResource;
use App\Models\SentHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SentHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->view) {
            case 'sentence':
                $table = SentHistory::where('sent_uid', $request->input('id'));
                break;
            default:
                return $this->error('known view');
                break;
        }
        if ($request->has('fork')) {
            $table = $table->whereNotNull('fork_from');
        }
        $count = $table->count();
        $table->orderBy(
            $request->input('order', 'created_at'),
            $request->input('dir', 'desc')
        );
        $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 100));

        $result = $table->get();

        return $this->ok(['rows' => SentHistoryResource::collection($result), 'count' => $count]);
    }

    public function contribution(Request $request)
    {
        /**
         *  计算用户贡献度
         *  算法：统计句子历史记录里的用户贡献句子的数量
         *  TODO:
         *  应该祛除重复的句子，一个句子的多次修改只计算一次
         *  只统计一个月内的数值
         */
        $result = SentHistory::select('user_uid')
            ->selectRaw('count(*)')
            ->groupBy('user_uid')
            ->orderBy('count', 'desc')
            ->take(10)
            ->get();

        foreach ($result as $key => $user) {
            $userInfo = UserApi::getByUuid($user->user_uid);
            $user->username = [
                'nickname' => $userInfo['nickName'],
                'username' => $userInfo['userName'],
            ];
        }

        return $this->ok($result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(SentHistory $sentHistory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, SentHistory $sentHistory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(SentHistory $sentHistory)
    {
        //
    }
}
