<?php

namespace App\Http\Controllers;

use App\Models\UserInfo;
use App\Models\Invite;
use App\Models\Channel;
use Illuminate\Support\Str;

use Illuminate\Http\Request;

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
            $this->error('error token',[],200);
        }
        if(UserInfo::where('username',$request->get('name'))->exists()){
            $this->error('avoid user name',[],200);
        }
        $user = new UserInfo;
        $user->userid = Str::Uuid();
        $user->username = $request->get('username');
        $user->nickname = $request->get('nickname');
        $user->email = $request->get('email');
        $user->password = md5($request->get('password'));
        $user->create_time = time()*1000;
        $user->modify_time = time()*1000;
        $user->save();

        //标记invite
        Invite::where('id',$request->get('token'))
                  ->where('email',$request->get('email'))
                  ->update(['status'=>'sign-up']);
        //建立channel
        $channel = new Channel;
        $channel->id = app('snowflake')->id();
        $channel->name = $request->get('username');
        $channel->owner_uid = $user->userid;
        $channel->type = "translation";
        $channel->lang = $request->get('lang');
        $channel->editor_id = $user->id;
        $channel->status = 30;
        $channel->create_time = time()*1000;
        $channel->modify_time = time()*1000;
        $channel->save();

        $channel_draft = new Channel;
        $channel_draft->id = app('snowflake')->id();
        $channel_draft->name = 'draft';
        $channel_draft->owner_uid = $user->userid;
        $channel_draft->type = "translation";
        $channel_draft->lang = $request->get('lang');
        $channel_draft->editor_id = $user->id;
        $channel_draft->create_time = time()*1000;
        $channel_draft->modify_time = time()*1000;
        $channel_draft->save();
        return $this->ok('ok');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserInfo  $userInfo
     * @return \Illuminate\Http\Response
     */
    public function show(UserInfo $userInfo)
    {
        //
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
