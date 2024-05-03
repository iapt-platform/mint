<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserInfo;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Http\Api\AuthApi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;

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

        $query = UserInfo::where(function ($query) use($request) {
                            $query->where('username',$request->get('username'))
                                  ->where('password',md5($request->get('password')));
                        })
                        ->orWhere(function ($query) use($request) {
                            $query->where('email',$request->get('username'))
                                  ->where('password',md5($request->get('password')));
                        });
        //Log::info($query->toSql());
        $user = $query->first();
        if($user){
            $ExpTime = time() + 60 * 60 * 24 * 365;
            $key = config('app.key');
            $payload = [
                'nbf' => time(),
                'exp' => $ExpTime,
                'uid' => $user->userid,
                'id' => $user->id,
            ];
            $jwt = JWT::encode($payload,$key,'HS512');
            return $this->ok($jwt);
        }else{
            return $this->error('invalid token');
        }
    }
    public function getUserInfoByToken(Request $request){
        $curr = AuthApi::current($request);
        if(!$curr){
            return $this->error('invalid token',401,401);
        }
        $userInfo = UserInfo::where('userid',$curr['user_uid'])
                        ->first();
        $user = [
            "id"=>$curr['user_uid'],
            "nickName"=> $userInfo->nickname,
            "realName"=> $userInfo->username,
            "avatar"=> "",
            "token"=>\substr($request->header('Authorization'),7) ,
        ];

        //role为空 返回[]
        $user['roles'] = [];
        if(!empty($userInfo->role)){
            $roles = json_decode($userInfo->role);
            if(is_array($roles)){
                $user['roles'] = $roles;
            }
        }

        if($curr['user_uid'] === config('mint.admin.root_uuid')){
            $user['roles'] = ['root'];
        }
        if($userInfo->avatar){
            $img = str_replace('.jpg','_s.jpg',$userInfo->avatar);
            if (App::environment('local')) {
                $user['avatar'] = Storage::url($img);
            }else{
                $user['avatar'] = Storage::temporaryUrl($img, now()->addDays(6));
            }
        }
        return $this->ok($user);
    }

}


