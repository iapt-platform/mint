<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAiModelRequest;
use App\Http\Requests\UpdateAiModelRequest;
use App\Models\AiModel;
use Illuminate\Http\Request;
use App\Http\Api\AuthApi;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Api\StudioApi;
use App\Http\Resources\AiModelResource;


class AiModelController extends Controller
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
        if (!$user) {
            Log::error('notification auth failed {request}', ['request' => $request]);
            return $this->error(__('auth.failed'), 401, 401);
        }
        switch ($request->get('view')) {
            case 'all':
                $table = AiModel::whereNotNull('owner_id');
                break;
            case 'studio':
                $studioId = StudioApi::getIdByName($request->get('name'));
                $table = AiModel::where('owner_id', $studioId);
                break;
            case 'usable':
                $table = AiModel::where('owner_id', $request->get('user_id'))
                    ->orWhere('privacy', 'public');
                break;
            case 'chat':
                $table = AiModel::where('owner_id', config("mint.admin.root_uuid"));
                break;
        }
        if ($request->has('keyword')) {
            $table = $table->where('name', 'like', '%' . $request->get('keyword') . '%');
        }
        $count = $table->count();

        $table = $table->orderBy(
            $request->get('order', 'created_at'),
            $request->get('dir', 'asc')
        );

        $table = $table->skip($request->get("offset", 0))
            ->take($request->get('limit', 1000));

        $result = $table->get();

        return $this->ok(
            [
                "rows" => AiModelResource::collection(resource: $result),
                "count" => $count,
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAiModelRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAiModelRequest $request)
    {
        //
        $user = AuthApi::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'), 401, 401);
        }
        $studioId = StudioApi::getIdByName($request->get('studio_name'));
        Log::debug('store', ['studioId' => $studioId, 'user' => $user]);
        if (!self::canEdit($user['user_uid'], $studioId)) {
            return $this->error(__('auth.failed'), 403, 403);
        }
        $new = new AiModel();
        $new->name = $request->get('name');
        $new->uid = Str::uuid();
        $new->real_name = Str::uuid();
        $new->owner_id = $studioId;
        $new->editor_id = $user['user_uid'];
        $new->save();
        return $this->ok(new AiModelResource($new));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AiModel  $aiModel
     * @return \Illuminate\Http\Response
     */
    public function show(AiModel $aiModel)
    {
        //
        return $this->ok(new AiModelResource($aiModel));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAiModelRequest  $request
     * @param  \App\Models\AiModel  $aiModel
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAiModelRequest $request, AiModel $aiModel)
    {
        //
        $user = AuthApi::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'), 401, 401);
        }
        if (!self::canEdit($user['user_uid'], $aiModel->owner_id)) {
            return $this->error(__('auth.failed'), 403, 403);
        }
        $aiModel->name = $request->get('name');
        $aiModel->description = $request->get('description');
        $aiModel->system_prompt = $request->get('system_prompt');
        $aiModel->url = $request->get('url');
        $aiModel->model = $request->get('model');
        $aiModel->key = $request->get('key');
        $aiModel->privacy = $request->get('privacy');
        $aiModel->editor_id = $user['user_uid'];
        $aiModel->save();
        return $this->ok(new AiModelResource($aiModel));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AiModel  $aiModel
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, AiModel $aiModel)
    {
        //
        $user = AuthApi::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'), 401, 401);
        }
        if (!self::canEdit($user['user_uid'], $aiModel->owner_id)) {
            return $this->error(__('auth.failed'), 403, 403);
        }
        $del = $aiModel->delete();
        return $this->ok($del);
    }

    public static function canEdit($user_uid, $owner_uid)
    {
        return $user_uid === $owner_uid;
    }
}
