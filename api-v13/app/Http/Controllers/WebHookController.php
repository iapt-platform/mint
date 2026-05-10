<?php

namespace App\Http\Controllers;

use App\Models\WebHook;
use Illuminate\Http\Request;
use App\Http\Resources\WebHookResource;
use App\Services\AuthService;
use Illuminate\Support\Str;

class WebHookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        switch ($request->input('view')) {
            case 'channel':
                $table = WebHook::where('res_type', 'channel')->where('res_id', $request->input('id'));
                break;
            default:
                return $this->error("no view");
                break;
        }
        if (!empty($search)) {
            $table->where('url', 'like', $search . "%");
        }
        $table->orderBy($request->input('order', 'updated_at'), $request->input('dir', 'desc'));
        $count = $table->count();
        $table->skip($request->input("offset", 0))
            ->take($request->input('limit', 1000));

        $result = $table->get();
        return $this->ok(["rows" => WebHookResource::collection($result), "count" => $count]);
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
            return $this->error(__('auth.failed'), [], 401);
        }

        $validated = $request->validate([
            'res_type' => 'required',
            'res_id' => 'required',
            'url' => 'required',
            'receiver' => 'required',
        ]);
        //TODO 判断权限
        $new = new WebHook;
        $new->id = Str::uuid();
        $new->res_type = $validated['res_type'];
        $new->res_id = $validated['res_id'];
        $new->url = $validated['url'];
        $new->receiver = $validated['receiver'];
        if ($request->has('event')) {
            $new->event = json_encode($request->input('event'), JSON_UNESCAPED_UNICODE);
        } else {
            $new->event = null;
        }
        $new->status = $request->input('status', 'active');
        $new->editor_uid = $user['user_uid'];
        $new->save();
        return $this->ok(new WebHookResource($new));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WebHook  $webHook
     * @return \Illuminate\Http\Response
     */
    public function show(string $id)
    {
        //
        $webHook = WebHook::find($id);
        if (!$webHook) {
            return $this->error('no id');
        }
        return $this->ok(new WebHookResource($webHook));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WebHook  $webHook
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $id)
    {
        //
        $user = AuthService::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'), [], 401);
        }

        $validated = $request->validate([
            'res_type' => 'required',
            'res_id' => 'required',
            'url' => 'required',
            'receiver' => 'required',
        ]);
        //TODO 判断权限
        $webHook = WebHook::find($id);
        if (!$webHook) {
            return $this->error('no id');
        }
        $webHook->res_type = $validated['res_type'];
        $webHook->res_id = $validated['res_id'];
        $webHook->url = $validated['url'];
        $webHook->receiver = $validated['receiver'];
        if ($request->has('event')) {
            $webHook->event = json_encode($request->input('event'), JSON_UNESCAPED_UNICODE);
        } else {
            $webHook->event = null;
        }
        $webHook->status = $request->input('status');
        $webHook->editor_uid = $user['user_uid'];
        $webHook->save();
        return $this->ok(new WebHookResource($webHook));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WebHook  $webHook
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, string $id)
    {
        //
        $user = AuthService::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'));
        }
        $webHook = WebHook::find($id);
        if (!$webHook) {
            return $this->error('no id');
        }
        //TODO 判断当前用户是否有权限
        $delete = 0;
        $delete = $webHook->delete();

        return $this->ok($delete);
    }
}
