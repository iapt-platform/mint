<?php

namespace App\Http\Controllers;

require_once __DIR__.'/../../../public/app/ucenter/function.php';

use App\Models\SentHistory;
use Illuminate\Http\Request;
use App\Http\Resources\SentHistoryResource;

class SentHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->view) {
            case 'sentence':
                $table = SentHistory::where('sent_uid',$request->get('id'));
                break;
            default:
                return $this->error('known view');
                break;
        }

        $table->orderBy($request->get('order','created_at'),$request->get('dir','desc'));
        $count = $table->count();
        $table->skip($request->get("offset",0))
              ->take($request->get('limit',1000));

        $result = $table->get();
		return $this->ok(["rows"=>SentHistoryResource::collection($result),"count"=>$count]);

    }

    public function contribution(Request $request){
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
                            ->orderBy('count','desc')
                            ->take(10)
                            ->get();

        $userinfo = new \UserInfo();
        foreach ($result as $key => $user) {
            # code...
            $user->username = $userinfo->getName($user->user_uid);
        }
        return $this->ok($result);
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
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SentHistory  $sentHistory
     * @return \Illuminate\Http\Response
     */
    public function show(SentHistory $sentHistory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SentHistory  $sentHistory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SentHistory $sentHistory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SentHistory  $sentHistory
     * @return \Illuminate\Http\Response
     */
    public function destroy(SentHistory $sentHistory)
    {
        //
    }
}
