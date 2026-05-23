<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Models\Invite;
use App\Models\UserInfo;
use App\Services\AuthService;
use App\Http\Api\UserApi;
use App\Http\Api\StudioApi;
use App\Http\Resources\InviteResource;
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
        $user = AuthService::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'));
        }
        $table = Invite::select([
            'id',
            'user_uid',
            'email',
            'status',
            'created_at',
            'updated_at'
        ]);
        switch ($request->input('view')) {
            case 'studio':
                if (empty($request->input('studio'))) {
                    return $this->error(__('auth.failed'));
                }
                //判断当前用户是否有指定的studio的权限
                if ($user['user_uid'] !== StudioApi::getIdByName($request->input('studio'))) {
                    return $this->error(__('auth.failed'));
                }
                $table = $table->where('user_uid', $user["user_uid"]);
                break;
            case 'all':
                $user = UserApi::getByUuid($user['user_uid']);
                if (!$user || !isset($user['roles']) || !in_array('administrator', $user['roles'])) {
                    return $this->error(__('auth.failed'));
                }
                break;
        }
        if ($request->has('search')) {
            $table = $table->where('email', 'like', '%' . $request->input('search') . "%");
        }
        $count = $table->count();
        $table = $table->orderBy(
            $request->input('order', 'updated_at'),
            $request->input('dir', 'desc')
        );

        $table = $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 1000));

        $result = $table->get();
        return $this->ok(["rows" => InviteResource::collection($result), "count" => $count]);
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
        if (!empty($request->input('studio'))) {
            $user = AuthService::current($request);
            if (!$user) {
                return $this->error(__('auth.failed'), 401, 401);
            }
            //判断当前用户是否有指定的studio的权限
            $studio_id = StudioApi::getIdByName($request->input('studio'));
            if ($user['user_uid'] !== $studio_id) {
                return $this->error(__('auth.failed'));
            }
            $sender = $studio_id;
        } else {
            $sender = config("mint.admin.root_uuid");
        }

        //查询是否重复
        if (
            Invite::where('email', $request->input('email'))->exists() ||
            UserInfo::where('email', $request->input('email'))->exists()
        ) {
            return $this->error('email.exists', __('validation.exists', ['email']), 200);
        }

        $uuid = Str::uuid();
        try {
            Mail::to($request->input('email'))
                ->send(new InviteMail(
                    $uuid,
                    $request->input('subject', 'sign up wikipali'),
                    $request->input('lang'),
                    $request->input('dashboard')
                ));
        } catch (\Exception $e) {
            Log::error('send invite email fail', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return $this->error('send email fail', '', 200);
        }

        $invite = new Invite;
        $invite->id = $uuid;
        $invite->email = $request->input('email');
        $invite->user_uid = $sender;
        $invite->status = 'invited';
        $invite->save();
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
