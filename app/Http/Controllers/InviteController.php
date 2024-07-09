<?php

namespace App\Http\Controllers;

use App\Models\Invite;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use App\Http\Api\AuthApi;
use App\Http\Api\UserApi;
use App\Http\Api\StudioApi;
use App\Http\Resources\InviteResource;
use Illuminate\Support\Str;
use Mail;
use App\Mail\InviteMail;

class InviteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        $table = Invite::select(['id','user_uid','email',
                                 'status','created_at','updated_at']);
        switch ($request->get('view')) {
            case 'studio':
                if(empty($request->get('studio'))){
                    return $this->error(__('auth.failed'));
                }
                //判断当前用户是否有指定的studio的权限
                if($user['user_uid'] !== StudioApi::getIdByName($request->get('studio'))){
                    return $this->error(__('auth.failed'));
                }
                $table = $table->where('user_uid', $user["user_uid"]);
                break;
            case 'all':
                $user = UserApi::getByUuid($user['user_uid']);
                if(!$user || !isset($user['roles']) || !in_array('administrator',$user['roles']) ){
                    return $this->error(__('auth.failed'));
                }
                break;
        }
        if($request->has('search')){
            $table = $table->where('email', 'like', '%'.$request->get('search')."%");
        }
        $count = $table->count();
        $table = $table->orderBy($request->get('order','updated_at'),
                                 $request->get('dir','desc'));

        $table = $table->skip($request->get('offset',0))
                       ->take($request->get('limit',1000));

        $result = $table->get();
        return $this->ok(["rows"=>InviteResource::collection($result),"count"=>$count]);
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
        $sender = '';
        if(!empty($request->get('studio'))){
            $user = AuthApi::current($request);
            if(!$user){
                return $this->error(__('auth.failed'),401,401);
            }
            //判断当前用户是否有指定的studio的权限
            $studio_id = StudioApi::getIdByName($request->get('studio'));
            if($user['user_uid'] !== $studio_id){
                return $this->error(__('auth.failed'));
            }
            $sender = $studio_id;
        }else{
            $sender = config("mint.admin.root_uuid");
        }

        //查询是否重复
        if(Invite::where('email',$request->get('email'))->exists() ||
            UserInfo::where('email',$request->get('email'))->exists()){
            return $this->error('email.exists',__('validation.exists',['email']),200);
        }

        $uuid = Str::uuid();
        Mail::to($request->get('email'))
            ->send(new InviteMail($uuid,
                                $request->get('subject','sign up wikipali'),
                                $request->get('lang'),
                                $request->get('dashboard')));
        if(Mail::failures()){
            return $this->error('send email fail', '',200);
        }else{
            $invite = new Invite;
            $invite->id = $uuid;
            $invite->email = $request->get('email');
            $invite->user_uid = $sender;
            $invite->status = 'invited';
            $invite->save();
        }
        return $this->ok(new InviteResource($invite));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Invite  $invite
     * @return \Illuminate\Http\Response
     */
    public function show(Invite $invite)
    {
        //
        return $this->ok(new InviteResource($invite));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invite  $invite
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Invite $invite)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Invite  $invite
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invite $invite)
    {
        //
    }
}
