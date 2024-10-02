<?php

namespace App\Http\Controllers;

use App\Models\UserInfo;
use App\Models\Invite;
use App\Models\Channel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SignUpController extends Controller
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
        //先查询invite核对uuid
        if(!Invite::where('id',$request->get('token'))
                  ->where('email',$request->get('email'))->exists()){
            $this->error('error token','',200);
        }
        if(UserInfo::where('username',$request->get('name'))->exists()){
            $this->error('avoid user name','',200);
        }

        try {
            DB::transaction(function() use($request){
                $user = new UserInfo;
                $user->userid = Str::Uuid();
                $user->username = $request->get('username');
                $user->nickname = $request->get('nickname');
                $user->email = $request->get('email');
                $user->password = md5($request->get('password'));
                $user->role = json_encode(['basic']);
                $user->create_time = time()*1000;
                $user->modify_time = time()*1000;
                $user->save();

                //标记invite
                Invite::where('id',$request->get('token'))
                        ->where('email',$request->get('email'))
                        ->update(['status'=>'sign-up']);
                //建立channel

                $channel_draft = new Channel;
                $channel_draft->id = app('snowflake')->id();
                $channel_draft->name = 'draft';
                $channel_draft->owner_uid = $user->userid;
                $channel_draft->type = "translation";
                $channel_draft->lang = $request->get('lang');
                $channel_draft->status = 5;
                $channel_draft->editor_id = $user->id;
                $channel_draft->create_time = time()*1000;
                $channel_draft->modify_time = time()*1000;
                $channel_draft->save();
            });
        }catch(\Exception $e) {
            Log::error('user create fail',['data'=>$e]);
            return $this->error('user create fail',500,500);
        }
        return $this->ok('ok');
    }

    /**
     * Display the specified resource.
     *
     * @param  string $username
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,string $username)
    {
        //
        $email = UserInfo::where('email',$request->get('email'))->exists();
        $user = UserInfo::where('username',$username)->exists();
        if($email && $user){
            //send email
            return $this->ok('ok');
        }else{
            return $this->error(['email'=>$email,'username'=>$user],[200],200);
        }
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
