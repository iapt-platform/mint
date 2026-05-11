<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;
use App\Services\AuthService;
use App\Http\Resources\NotificationResource;

class NotificationController extends Controller
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
            Log::error('notification auth failed {request}', ['request' => $request]);
            return $this->error(__('auth.failed'), 401, 401);
        }
        switch ($request->input('view')) {
            case 'to':
                $table = Notification::where('to', $user['user_uid']);
                $unread = Notification::where('to', $user['user_uid'])
                    ->where('status', 'unread')->count();
                break;
        }

        if ($request->has('status')) {
            $table = $table->whereIn('status', explode(',', $request->input('status')));
        }
        $count = $table->count();

        $table = $table->orderBy($request->input('order', 'created_at'), $request->input('dir', 'desc'));

        $table = $table->skip($request->input("offset", 0))
            ->take($request->input('limit', 10));

        $result = $table->get();

        return $this->ok(
            [
                "rows" => NotificationResource::collection($result),
                "count" => $count,
                'unread' => $unread,
            ]
        );
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
        $user = AuthService::current($request);
        if (!$user) {
            Log::error('notification auth failed {request}', ['request' => $request]);
            return $this->error(__('auth.failed'), 401, 401);
        }
        $new = new Notification;
        $new->id = Str::uuid();
        $new->from = $user['user_uid'];
        $new->to = $request->input('to');
        $new->url = $request->input('url');
        $new->content = $request->input('content');
        $new->res_type = $request->input('res_type');
        $new->res_id = $request->input('res_id');
        $new->channel = $request->input('channel');
        $new->save();

        return $this->ok(new NotificationResource($new));
    }

    public static function insert($from, $to, $res_type, $res_id, $channel)
    {
        foreach ($to as $key => $one) {
            $new = new Notification;
            $new->id = Str::uuid();
            $new->from = $from;
            $new->to = $one;
            $new->url = '';
            $new->content = '';
            $new->res_type = $res_type;
            $new->res_id = $res_id;
            $new->channel = $channel;
            $new->save();
        }
        return count($to);
    }

    /**
     * Display the specified resource.
     *
     * @param  Notification $notification
     * @return \Illuminate\Http\Response
     */
    public function show(Notification $notification)
    {
        //
        return $this->ok(new NotificationResource($notification));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Notification $notification
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Notification $notification)
    {
        //
        $user = AuthService::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'), 401, 401);
        }
        if ($notification->to === $user['user_uid']) {
            $notification->status = $request->input('status', 'read');
            $notification->save();
            $unread = Notification::where('to', $notification->to)
                ->where('status', 'unread')
                ->count();
            return $this->ok(['unread' => $unread]);
        } else {
            return $this->error(__('auth.failed'), 403, 403);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Notification $notification
     * @return \Illuminate\Http\Response
     */
    public function destroy(Notification $notification)
    {
        //
        $notification->delete();
        if ($notification->trashed()) {
            return $this->ok('ok');
        } else {
            return $this->error('fail', 500, 500);
        }
    }
}
