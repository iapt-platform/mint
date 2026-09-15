<?php

namespace App\Http\Controllers;

use App\Http\Api\StudioApi;
use App\Http\Requests\StoreAiModelRequest;
use App\Http\Requests\UpdateAiModelRequest;
use App\Http\Resources\AiModelResource;
use App\Models\AiModel;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class AiModelController extends Controller
{
    /**
     * 客户端可写的字段（name / privacy 另行处理：前者参与重名校验，后者建档时有默认值）。
     */
    private const EDITABLE_FIELDS = ['description', 'system_prompt', 'url', 'model', 'key'];

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), 401, 401);
        }

        switch ($request->input('view')) {
            case 'all':
                $table = AiModel::whereNotNull('owner_id');
                break;
            case 'studio':
                // 指定用户名下的记录
                $studioId = StudioApi::getIdByName($request->input('name'));
                $table = AiModel::where('owner_id', $studioId);
                break;
            case 'usable':
                $table = AiModel::where('owner_id', $request->input('user_id'))
                    ->orWhere('privacy', 'public');
                break;
            case 'chat':
                $table = AiModel::where('owner_id', config('mint.admin.root_uuid'));
                break;
        }
        if ($request->has('keyword')) {
            $table = $table->where('name', 'like', '%'.$request->input('keyword').'%');
        }
        $count = $table->count();

        $table = $table->orderBy(
            $request->input('order', 'created_at'),
            $request->input('dir', 'asc')
        );

        $table = $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 1000));

        $result = $table->get();

        return $this->ok(
            [
                'rows' => AiModelResource::collection(resource: $result),
                'count' => $count,
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(StoreAiModelRequest $request)
    {
        //
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), 401, 401);
        }
        $studioId = StudioApi::getIdByName($request->input('studio_name'));
        if (! self::canEdit($user['user_uid'], $studioId)) {
            return $this->error(__('auth.failed'), 403, 403);
        }
        // 同一 studio 内 name 必须唯一：客户端（wikipali-write Skill）靠 name 做幂等匹配，
        // 重名会让「查不到就创建」的流程反复建出同名记录
        $duplicated = AiModel::where('owner_id', $studioId)
            ->where('name', $request->input('name'))
            ->exists();
        if ($duplicated) {
            return $this->error(__('validation.unique', ['attribute' => 'name']), null, 409);
        }

        $new = new AiModel;
        $new->uid = Str::uuid();
        $new->real_name = Str::uuid();
        $new->owner_id = $studioId;
        $new->editor_id = $user['user_uid'];
        $new->name = $request->input('name');
        $new->privacy = $request->input('privacy', 'private');
        foreach (self::EDITABLE_FIELDS as $field) {
            if ($request->has($field)) {
                $new->{$field} = $request->input($field);
            }
        }
        $new->save();

        return $this->ok(new AiModelResource($new));
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Request $request, AiModel $aiModel)
    {
        //
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), 401, 401);
        }
        if (! self::canEdit($user['user_uid'], $aiModel->owner_id)) {
            return $this->error(__('auth.failed'), 403, 403);
        }

        return $this->ok(new AiModelResource($aiModel));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(UpdateAiModelRequest $request, AiModel $aiModel)
    {
        //
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), 401, 401);
        }
        if (! self::canEdit($user['user_uid'], $aiModel->owner_id)) {
            return $this->error(__('auth.failed'), 403, 403);
        }
        if ($request->has('name')) {
            $duplicated = AiModel::where('owner_id', $aiModel->owner_id)
                ->where('name', $request->input('name'))
                ->where('uid', '<>', $aiModel->uid)
                ->exists();
            if ($duplicated) {
                return $this->error(__('validation.unique', ['attribute' => 'name']), null, 409);
            }
        }
        // 增量更新：只改请求里出现的字段。
        // 用 has() 而非 filled()，好让前端能把 description 之类的字段显式清空；
        // 但未提交的字段必须原样保留——否则客户端的局部 PUT 会把其余字段全置 null。
        foreach (array_merge(['name', 'privacy'], self::EDITABLE_FIELDS) as $field) {
            if ($request->has($field)) {
                $aiModel->{$field} = $request->input($field);
            }
        }
        $aiModel->editor_id = $user['user_uid'];
        $aiModel->save();

        return $this->ok(new AiModelResource($aiModel));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Request $request, AiModel $aiModel)
    {
        //
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), 401, 401);
        }
        if (! self::canEdit($user['user_uid'], $aiModel->owner_id)) {
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
