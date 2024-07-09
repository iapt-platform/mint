<?php

namespace App\Http\Controllers;

use App\Models\UserInfo;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
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
        $user = UserInfo::where('reset_password_token',$request->get('token'))
                        ->where('username',$request->get('username'))
                        ->first();
        if(!$user){
        return $this->error('no token',404,404);
        }
        if(mb_strlen($request->get('password'),'UTF-8')<6){
            return $this->error('input is invalid',402,402);
        }
        $user->password = md5($request->get('password'));
        $user->reset_password_token = null;
        $ok = $user->save();
        if($ok){
            return $this->ok($user);
        }else{
            return $this->error('fail to set password',500,500);
        }

    }

    /**
     * 根据token获取用户名.
     *
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function show($token)
    {
        //
        $user = UserInfo::where('reset_password_token',$token)
                        ->select(['username'])->first();
        if(!$user){
            return $this->error('no token',404,404);
        }
        return $this->ok($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserInfo  $userInfo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserInfo $userInfo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserInfo  $userInfo
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserInfo $userInfo)
    {
        //
    }
}
