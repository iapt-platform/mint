<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Notification;
use App\Http\Api\AuthApi;
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
        $user = AuthApi::current($request);
        if(!$user){
            Log::error('notification auth failed {request}',['request'=>$request]);
            return $this->error(__('auth.failed'),401,401);
        }
        switch ($request->get('view')) {
            case 'to':
                $table = Notification::where('to',$user['user_uid']);
                $unread = Notification::where('to',$user['user_uid'])
                                    ->where('status','unread')->count();
                break;
        }

        if($request->has('status')){
            $table = $table->whereIn('status',explode(',',$request->get('status')) );
        }
        $count = $table->count();

        $table = $table->orderBy($request->get('order','created_at'),$request->get('dir','desc'));

        $table = $table->skip($request->get("offset",0))
                    ->take($request->get('limit',10));

        $result = $table->get();

        return $this->ok(
            [
            "rows"=>NotificationResource::collection($result),
            "count"=>$count,
            'unread'=>$unread,
            ]);
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
        $user = AuthApi::current($request);
        if(!$user){
            Log::error('notification auth failed {request}',['request'=>$request]);
            return $this->error(__('auth.failed'),401,401);
        }
        $new = new Notification;
        $new->id = Str::uuid();
        $new->from = $user['user_uid'];
        $new->to = $request->get('to');
        $new->url = $request->get('url');
        $new->content = $request->get('content');
        $new->res_type = $request->get('res_type');
        $new->res_id = $request->get('res_id');
        $new->channel = $request->get('channel');
        $new->save();

        return $this->ok(new NotificationResource($new));
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
        $notification->status = $request->get('status','read');
        $notification->save();
        $unread = Notification::where('to',$notification->to)
                            ->where('status','unread')
                            ->count();
        return $this->ok(['unread'=>$unread]);
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
        if($notification->trashed()){
            return $this->ok('ok');
        }else{
            return $this->error('fail',500,500);
        }
    }
}
