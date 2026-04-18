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
use Illuminate\Database\Eloquent\Builder;

class CollectionController extends Controller
{
    public function __construct(protected CollectionService $service) {}

    public function index(Request $request)
    {
        try {
            $table = match ($request->input('view')) {
                'studio_list' => $this->service->buildStudioListQuery(),
                'studio'      => $this->buildStudioIndex($request),
                'public'      => $this->service->buildPublicQuery(
                    $request->has('studio')
                        ? StudioApi::getIdByName($request->input('studio'))
                        : null
                ),
                default       => throw new \InvalidArgumentException('无法识别的view参数'),
            };
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            return $this->error($e->getMessage(), 403, 403);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 200, 200);
        }

        if ($request->filled('search')) {
            $table = $table->where('title', 'like', '%' . $request->input('search') . '%');
        }

        $count = $table->count();

        if ($request->has('order') && $request->has('dir')) {
            $table = $table->orderBy($request->input('order'), $request->input('dir'));
        } else {
            $orderCol = $request->input('view') === 'studio_list' ? 'count' : 'updated_at';
            $table = $table->orderBy($orderCol, 'desc');
        }

        $result = $table
            ->skip($request->input('offset', 0))
            ->take($request->input('limit', 1000))
            ->get();

        return $this->ok([
            'rows'  => CollectionResource::collection($result),
            'count' => $count,
        ]);
    }

    // studio 分支的鉴权逻辑留在 controller
    private function buildStudioIndex(Request $request): Builder
    {
        $user = AuthApi::current($request);
        if (!$user) {
            throw new \Illuminate\Auth\AuthenticationException(__('auth.failed'));
        }

        $studioId = StudioApi::getIdByName($request->input('name'));
        if ($user['user_uid'] !== $studioId) {
            throw new \Illuminate\Auth\AuthenticationException(__('auth.failed'));
        }

        return $this->service->buildStudioQuery(
            $user['user_uid'],
            $studioId,
            $request->input('view2', 'my')
        );
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

        if ($user['user_uid'] !== StudioApi::getIdByName($request->input('studio'))) {
            return $this->error(__('auth.failed'), 403, 403);
        }

        if (Collection::where('title', $request->input('title'))->where('owner', $user['user_uid'])->exists()) {
            return $this->error(__('validation.exists'), 200, 200);
        }

        $newOne = new Collection;
        $newOne->id           = app('snowflake')->id();
        $newOne->uid          = Str::uuid();
        $newOne->title        = $request->input('title');
        $newOne->lang         = $request->input('lang');
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

        $collection->title           = $request->input('title');
        $collection->subtitle        = $request->input('subtitle');
        $collection->summary         = $request->input('summary');
        $collection->lang            = $request->input('lang');
        $collection->status          = $request->input('status');
        $collection->default_channel = $request->input('default_channel');
        $collection->modify_time     = time() * 1000;

        if ($request->has('aritcle_list')) {
            $collection->article_list = json_encode($request->input('aritcle_list'));
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
