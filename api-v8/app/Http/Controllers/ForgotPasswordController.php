<?php

namespace App\Http\Controllers;

use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mail;
use App\Mail\ForgotPassword;

class ForgotPasswordController extends Controller
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
        $user = UserInfo::where('email',$request->get('email'))->first();
        if(!$user){
            return $this->error('no user',404,404);
        }
        $resetToken = Str::uuid();
        $user->reset_password_token = $resetToken;
        $ok = $user->save();
        if(!$ok){
            return $this->error('fail on update reset_password_token',500,500);
        }

        Mail::to($request->get('email'))
            ->send(new ForgotPassword($resetToken,$request->get('lang'),$request->get('dashboard')));
        if(Mail::failures()){
            return $this->error('send email fail',[],200);
        }
        return $this->ok('');
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
