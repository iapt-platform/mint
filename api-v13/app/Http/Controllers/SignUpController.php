<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Invite;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SignUpController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        // 先查询invite核对uuid
        if (! Invite::where('id', $request->input('token'))
            ->where('email', $request->input('email'))->exists()) {
            $this->error('error token', '', 200);
        }
        if (UserInfo::where('username', $request->input('name'))->exists()) {
            $this->error('avoid user name', '', 200);
        }

        try {
            DB::transaction(function () use ($request) {
                $user = new UserInfo;
                $user->userid = Str::Uuid();
                $user->username = $request->input('username');
                $user->nickname = $request->input('nickname');
                $user->email = $request->input('email');
                $user->password = md5($request->input('password'));
                $user->role = json_encode(['basic']);
                $user->create_time = time() * 1000;
                $user->modify_time = time() * 1000;
                $user->save();

                // 标记invite
                Invite::where('id', $request->input('token'))
                    ->where('email', $request->input('email'))
                    ->update(['status' => 'sign-up']);
                // 建立channel

                $channel_draft = new Channel;
                $channel_draft->id = app('snowflake')->id();
                $channel_draft->name = 'draft';
                $channel_draft->owner_uid = $user->userid;
                $channel_draft->type = 'translation';
                $channel_draft->lang = $request->input('lang');
                $channel_draft->status = 5;
                $channel_draft->editor_id = $user->id;
                $channel_draft->create_time = time() * 1000;
                $channel_draft->modify_time = time() * 1000;
                $channel_draft->save();
            });
        } catch (\Exception $e) {
            Log::error('user create fail', ['data' => $e]);

            return $this->error('user create fail', 500, 500);
        }

        return $this->ok('ok');
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Request $request, string $username)
    {
        //
        $email = UserInfo::where('email', $request->input('email'))->exists();
        $user = UserInfo::where('username', $username)->exists();
        if ($email && $user) {
            // send email
            return $this->ok('ok');
        } else {
            return $this->error(['email' => $email, 'username' => $user], [200], 200);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, UserInfo $userInfo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(UserInfo $userInfo)
    {
        //
    }
}
