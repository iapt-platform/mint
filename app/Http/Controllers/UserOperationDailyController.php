<?php

namespace App\Http\Controllers;

use App\Models\UserOperationDaily;
use Illuminate\Http\Request;
use App\Http\Api\AuthApi;
use App\Http\Api\UserApi;

class UserOperationDailyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->get('view')) {
            case "user-all":
                $queryUserUuid = UserApi::getIdByName($request->get('studio_name'));
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                //TODO 判断是否有查看权限
                if($queryUserUuid !== $user["user_uid"]){
                    return $this->error(__('auth.failed'));
                }
                $result = UserOperationDaily::where('user_id',$user["user_id"])
                                  ->select(['date_int','duration','hit'])
                                  ->orderBy("date_int")
                                  ->get();
                break;
            case "user-year":
                $queryUserId = UserApi::getIntIdByName($request->get('studio_name'));
                //TODO 判断是否有查看权限
                $result = UserOperationDaily::where('user_id',$queryUserId)
                                  ->select(['date_int','duration'])
                                  ->orderBy("date_int")
                                  ->get();
                break;
        }
        return $this->ok(["rows"=>$result,"count"=>count($result)]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
     * Display the specified resource.
     *
     * @param  \App\Models\UserOperationDaily  $userOperationDaily
     * @return \Illuminate\Http\Response
     */
    public function show(UserOperationDaily $userOperationDaily)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserOperationDaily  $userOperationDaily
     * @return \Illuminate\Http\Response
     */
    public function edit(UserOperationDaily $userOperationDaily)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserOperationDaily  $userOperationDaily
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserOperationDaily $userOperationDaily)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserOperationDaily  $userOperationDaily
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserOperationDaily $userOperationDaily)
    {
        //
    }
}
