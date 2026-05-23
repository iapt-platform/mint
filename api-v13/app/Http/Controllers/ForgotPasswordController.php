<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;


use App\Models\UserInfo;
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
        $user = UserInfo::where('email', $request->input('email'))->first();
        if (!$user) {
            return $this->error('no user', 404, 404);
        }
        $resetToken = Str::uuid();
        $user->reset_password_token = $resetToken;
        $ok = $user->save();
        if (!$ok) {
            return $this->error('fail on update reset_password_token', 500, 500);
        }

        try {
            Mail::to($request->input('email'))
                ->send(new ForgotPassword($resetToken, $request->input('lang'), $request->input('dashboard')));
        } catch (\Exception $e) {
            Log::error('send forgot password email fail', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return $this->error('send email fail', [], 200);
        }
        return $this->ok('successful');
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
