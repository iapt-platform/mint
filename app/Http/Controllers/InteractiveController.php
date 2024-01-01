<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use Illuminate\Http\Request;
use App\Http\Api\AuthApi;

class InteractiveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * 获取某个资源，某个用户的权限
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $res_id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, string $res_id)
    {
        //
        $user = AuthApi::current($request);
        $data = [];
        switch ($request->get('res_type')) {
            case 'article':
                /* qa */
                $data['qa'] = [
                    'can_create' => false,
                    'can_reply' => false,
                ];
                if($user && ArticleController::userCanEditId($user['user_uid'],$res_id)){
                    $data['qa']['can_create'] = true;
                    $data['qa']['can_reply'] = true;
                }
                $data['qa']['count'] = Discussion::where('res_id',$res_id)
                                                ->where('type','qa')
                                                ->where('status','close')
                                                ->count();
                /* help */
                $data['help'] = [
                    'can_create' => false,
                    'can_reply' => false,
                ];
                if($user){
                    $data['help']['can_reply'] = true;
                    if(ArticleController::userCanEditId($user['user_uid'],$res_id)){
                        $data['help']['can_create'] = true;
                    }
                }
                $data['help']['count'] = Discussion::where('res_id',$res_id)
                                                ->where('type','help')
                                                ->where('status','active')
                                                ->count();



                /* discussion */
                $data['discussion'] = [
                    'can_create' => false,
                    'can_reply' => false,
                ];
                if($user){
                    $data['discussion']['can_reply'] = true;
                    $data['discussion']['can_create'] = true;
                }
                $data['discussion']['count'] = Discussion::where('res_id',$res_id)
                                                ->where('type','discussion')
                                                ->where('status','active')
                                                ->count();
                break;
        }

        return $this->ok($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Discussion  $discussion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Discussion $discussion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Discussion  $discussion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Discussion $discussion)
    {
        //
    }
}
