<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAiModelRequest;
use App\Http\Requests\UpdateAiModelRequest;
use App\Models\AiModel;
use Illuminate\Http\Request;
use App\Services\AuthService;
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
        $user = AuthService::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'), 401, 401);
        }
        switch ($request->input('view')) {
            case 'all':
                $table = AiModel::whereNotNull('owner_id');
                break;
            case 'studio':
                $studioId = StudioApi::getIdByName($request->input('name'));
                $table = AiModel::where('owner_id', $studioId);
                break;
            case 'usable':
                $table = AiModel::where('owner_id', $request->input('user_id'))
                    ->orWhere('privacy', 'public');
                break;
            case 'chat':
                $table = AiModel::where('owner_id', config("mint.admin.root_uuid"));
                break;
        }
        if ($request->has('keyword')) {
            $table = $table->where('name', 'like', '%' . $request->input('keyword') . '%');
        }
        $count = $table->count();

        $table = $table->orderBy(
            $request->input('order', 'created_at'),
            $request->input('dir', 'asc')
        );

        $table = $table->skip($request->input("offset", 0))
            ->take($request->input('limit', 1000));

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
        $user = AuthService::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'), 401, 401);
        }
        $studioId = StudioApi::getIdByName($request->input('studio_name'));
        Log::debug('store', ['studioId' => $studioId, 'user' => $user]);
        if (!self::canEdit($user['user_uid'], $studioId)) {
            return $this->error(__('auth.failed'), 403, 403);
        }
        $new = new AiModel();
        $new->name = $request->input('name');
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
        $user = AuthService::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'), 401, 401);
        }
        if (!self::canEdit($user['user_uid'], $aiModel->owner_id)) {
            return $this->error(__('auth.failed'), 403, 403);
        }
        $aiModel->name = $request->input('name');
        $aiModel->description = $request->input('description');
        $aiModel->system_prompt = $request->input('system_prompt');
        $aiModel->url = $request->input('url');
        $aiModel->model = $request->input('model');
        $aiModel->key = $request->input('key');
        $aiModel->privacy = $request->input('privacy');
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
        $user = AuthService::current($request);
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
