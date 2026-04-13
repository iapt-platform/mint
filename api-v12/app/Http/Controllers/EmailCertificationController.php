<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invite;
use App\Http\Resources\InviteResource;
use Illuminate\Support\Str;
use App\Mail\EmailCertif;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Models\UserInfo;

class EmailCertificationController extends Controller
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
        //查询是否重复
        if (UserInfo::where('email', $request->input('email'))->exists()) {
            return $this->error('email.exists', 'err.email.exists', 200);
        }
        $sender = config("mint.admin.root_uuid");

        $uuid = Str::uuid();
        $invite = Invite::firstOrNew(
            ['email' => $request->input('email')],
            ['id' => $uuid]
        );
        $invite->user_uid = $sender;
        $invite->status = 'invited';
        $invite->save();

        Mail::to($request->input('email'))
            ->send(new EmailCertif(
                $invite->id,
                $request->input('subject', 'sign up wikipali'),
                $request->input('lang'),
            ));
        if (Mail::failures()) {
            return $this->error('send email fail', '', 200);
        }

        return $this->ok(new InviteResource($invite));
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show(string $id)
    {
        //
        $code = Cache::get("/email/certification/" . $id);
        if (empty($code)) {
            return $this->error('Certification is avalide', 200, 200);
        }
        return $this->ok($code);
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
}
