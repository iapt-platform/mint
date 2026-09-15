<?php

namespace App\Http\Controllers;

use App\Http\Resources\InviteResource;
use App\Mail\EmailCertif;
use App\Models\Invite;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailCertificationController extends Controller
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
        // 查询是否重复
        if (UserInfo::where('email', $request->input('email'))->exists()) {
            return $this->error('email.exists', 'err.email.exists', 200);
        }
        $sender = config('mint.admin.root_uuid');

        $uuid = Str::uuid();
        $invite = Invite::firstOrNew(
            ['email' => $request->input('email')],
            ['id' => $uuid]
        );
        $invite->user_uid = $sender;
        $invite->status = 'invited';
        $invite->save();

        try {
            Mail::to($request->input('email'))
                ->send(new EmailCertif(
                    $invite->id,
                    $request->input('subject', 'sign up wikipali'),
                    $request->input('lang'),
                ));
        } catch (\Exception $e) {
            Log::error('send email fail', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error('send email fail', $e->getMessage(), 200);
        }

        return $this->ok(new InviteResource($invite));
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(string $id)
    {
        //
        $code = Cache::get('/email/certification/'.$id);
        if (empty($code)) {
            return $this->error('Certification is avalide', 200, 200);
        }

        return $this->ok($code);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
