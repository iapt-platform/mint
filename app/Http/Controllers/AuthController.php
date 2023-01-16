<?php

namespace App\Http\Controllers;

require_once __DIR__.'/../../../public/app/ucenter/function.php';

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Http\Api;

class AuthController extends Controller
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function signIn(Request $request){
        $userinfo = new \UserInfo();
        $user = $userinfo->signIn($request->get('username'),$request->get('password'));
        if($user){
            $ExpTime = time() + 60 * 60 * 24 * 365;
            $key = env('APP_KEY');
            $payload = [
                'nbf' => time(),
                'exp' => $ExpTime,
                'uid' => $user['userid'],
                'id' => $user['id'],
            ];
            $jwt = JWT::encode($payload,$key,'HS512');
            return $this->ok($jwt);
        }else{
            return $this->error('invalid token');
        }
    }
    public function getUserInfoByToken(Request $request){
        $curr = \App\Http\Api\AuthApi::current($request);
        if($curr){
            $userinfo = new \UserInfo();
		    $username = $userinfo->getName($curr['user_uid']);
            $user = [
                "nickName"=> $username['nickname'],
                "realName"=> $username['username'],
                "avatar"=> "",
                "roles"=> [],
                "token"=>\substr($request->header('Authorization'),7) ,
            ];
            return $this->ok($user);
        }else{
            return $this->error('invalid token');
        }
    }

}


