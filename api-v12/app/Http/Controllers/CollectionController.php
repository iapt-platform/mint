<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Api\AuthApi;
use App\Http\Api\StudioApi;
use App\Http\Resources\CollectionResource;
use App\Services\CollectionService;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
    public function __construct(protected CollectionService $service) {}

    public function index(Request $request)
    {
        $result = $this->service->buildIndexQuery($request);

        if (isset($result['error'])) {
            return $this->error($result['error'], $result['code'] ?? 200, $result['code'] ?? 200);
        }

        return $this->ok([
            'rows'  => CollectionResource::collection($result['data']),
            'count' => $result['count'],
        ]);
    }

    public function showMyNumber(Request $request)
    {
        $result = $this->service->getMyNumber($request);

        if (isset($result['error'])) {
            return $this->error($result['error'], $result['code'], $result['code']);
        }

        return $this->ok($result['data']);
    }

    public function store(Request $request)
    {
        $user = AuthApi::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'), 401, 401);
        }

        if ($user['user_uid'] !== StudioApi::getIdByName($request->get('studio'))) {
            return $this->error(__('auth.failed'), 403, 403);
        }

        if (Collection::where('title', $request->get('title'))->where('owner', $user['user_uid'])->exists()) {
            return $this->error(__('validation.exists'), 200, 200);
        }

        $newOne = new Collection;
        $newOne->id           = app('snowflake')->id();
        $newOne->uid          = Str::uuid();
        $newOne->title        = $request->get('title');
        $newOne->lang         = $request->get('lang');
        $newOne->article_list = '[]';
        $newOne->owner        = $user['user_uid'];
        $newOne->owner_id     = $user['user_id'];
        $newOne->editor_id    = $user['user_id'];
        $newOne->create_time  = time() * 1000;
        $newOne->modify_time  = time() * 1000;
        $newOne->save();

        return $this->ok(new CollectionResource($newOne));
    }

    public function show(Request $request, $id)
    {
        $result = Collection::where('uid', $id)->first();
        if (!$result) {
            Log::warning("没有查询到数据 id={$id}");
            return $this->error("没有查询到数据 id={$id}");
        }

        if ($result->status < 30) {
            Log::info('私有文章，判断权限' . $id);
            $user = AuthApi::current($request);
            if (!$user) {
                Log::warning('未登录');
                return $this->error(__('auth.failed'), 403, 403);
            }

            if ($user['user_uid'] !== $result->owner) {
                Log::info($user['user_uid'] . '私有文章，判断权限' . $id);
                if (!$this->service->userCanRead($user['user_uid'], $result)) {
                    Log::warning($user['user_uid'] . '没有读取权限');
                    return $this->error(__('auth.failed'), 403, 403);
                }
            }
        }

        $result->fullArticleList = true;
        return $this->ok(new CollectionResource($result));
    }

    public function update(Request $request, string $id)
    {
        $collection = Collection::find($id);
        if (!$collection) {
            return $this->error('no recorder');
        }

        $user = AuthApi::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'), 401, 401);
        }

        if (!$this->service->userCanEdit($user['user_uid'], $collection)) {
            return $this->error(__('auth.failed'), 403, 403);
        }

        $collection->title           = $request->get('title');
        $collection->subtitle        = $request->get('subtitle');
        $collection->summary         = $request->get('summary');
        $collection->lang            = $request->get('lang');
        $collection->status          = $request->get('status');
        $collection->default_channel = $request->get('default_channel');
        $collection->modify_time     = time() * 1000;

        if ($request->has('aritcle_list')) {
            $collection->article_list = json_encode($request->get('aritcle_list'));
        }

        $collection->save();
        return $this->ok(new CollectionResource($collection));
    }

    public function destroy(Request $request, string $id)
    {
        $user = AuthApi::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'));
        }

        $collection = Collection::find($id);
        if ($user['user_uid'] !== $collection['owner']) {
            return $this->error(__('auth.failed'));
        }

        DB::transaction(function () use ($collection) {
            // TODO: 删除文集中的文章
            $collection->delete();
        });

        return $this->ok(true);
    }
}
