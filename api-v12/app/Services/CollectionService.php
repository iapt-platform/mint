<?php

namespace App\Services;

use App\Models\Collection;
use App\Http\Api\AuthApi;
use App\Http\Api\StudioApi;
use App\Http\Api\ShareApi;
use Illuminate\Http\Request;
use App\Http\Resources\CollectionResource;
use Illuminate\Support\Facades\Log;

class CollectionService
{
    protected $indexCol = [
        'uid',
        'title',
        'subtitle',
        'summary',
        'article_list',
        'owner',
        'status',
        'default_channel',
        'lang',
        'updated_at',
        'created_at',
    ];
    /**
     * 判断用户是否有编辑权限
     */
    public function userCanEdit(string $user_uid, Collection $collection): bool
    {
        if ($collection->owner === $user_uid) {
            return true;
        }

        return ShareApi::getResPower($user_uid, $collection->uid) >= 20;
    }

    /**
     * 判断用户是否有读取权限
     */
    public function userCanRead(string $user_uid, Collection $collection): bool
    {
        if ($collection->owner === $user_uid) {
            return true;
        }

        return ShareApi::getResPower($user_uid, $collection->uid) >= 10;
    }

    /**
     * 获取当前 studio 下我的数量与协作数量
     */
    public function getMyNumber(Request $request): array
    {
        $user = AuthApi::current($request);
        if (!$user) {
            return ['error' => __('auth.failed'), 'code' => 403];
        }

        $studioId = StudioApi::getIdByName($request->get('studio'));
        if ($user['user_uid'] !== $studioId) {
            return ['error' => __('auth.failed'), 'code' => 403];
        }

        $my = Collection::where('owner', $studioId)->count();

        $resList = ShareApi::getResList($studioId, 4);
        $resId = array_column($resList, 'res_id');

        $collaboration = Collection::whereIn('uid', $resId)
            ->where('owner', '<>', $studioId)
            ->count();

        return ['data' => ['my' => $my, 'collaboration' => $collaboration]];
    }

    /**
     * 构建 index 查询，根据 view 参数分发不同逻辑
     */
    public function buildIndexQuery(Request $request): array
    {

        $table = match ($request->get('view')) {
            'studio_list' => $this->buildStudioListQuery($this->indexCol),
            'studio'      => $this->buildStudioQuery($request, $this->indexCol),
            'public'      => $this->buildPublicQuery($request, $this->indexCol),
            default       => null,
        };

        if ($table === null) {
            return ['error' => '无法识别的view参数'];
        }

        // 如果是鉴权失败从 studio 分支返回的错误
        if (isset($table['error'])) {
            return $table;
        }

        // 搜索
        if ($request->filled('search')) {
            $table = $table->where('title', 'like', '%' . $request->get('search') . '%');
        }

        $count = $table->count();

        // 排序
        if ($request->has('order') && $request->has('dir')) {
            $table = $table->orderBy($request->get('order'), $request->get('dir'));
        } else {
            $orderCol = $request->get('view') === 'studio_list' ? 'count' : 'updated_at';
            $table = $table->orderBy($orderCol, 'desc');
        }

        $result = $table
            ->skip($request->get('offset', 0))
            ->take($request->get('limit', 1000))
            ->get();

        return ['data' => $result, 'count' => $count];
    }

    // -------------------------------------------------------------------------
    // 私有：各 view 的查询构建
    // -------------------------------------------------------------------------

    private function buildStudioListQuery(array $indexCol)
    {
        return Collection::select(['owner'])
            ->selectRaw('count(*) as count')
            ->where('status', 30)
            ->groupBy('owner');
    }

    private function buildStudioQuery(Request $request, array $indexCol): array|\Illuminate\Database\Eloquent\Builder
    {
        $user = AuthApi::current($request);
        if (!$user) {
            return ['error' => __('auth.failed'), 'code' => 403];
        }

        $studioId = StudioApi::getIdByName($request->get('name'));
        if ($user['user_uid'] !== $studioId) {
            return ['error' => __('auth.failed'), 'code' => 403];
        }

        $table = Collection::select($indexCol);

        if ($request->get('view2', 'my') === 'my') {
            return $table->where('owner', $studioId);
        }

        // 协作
        $resList = ShareApi::getResList($studioId, 4);
        $resId = array_column($resList, 'res_id');

        return $table->whereIn('uid', $resId)->where('owner', '<>', $studioId);
    }

    private function buildPublicQuery(Request $request, array $indexCol)
    {
        $table = Collection::select($indexCol)->where('status', 30);

        if ($request->has('studio')) {
            $studioId = StudioApi::getIdByName($request->get('studio'));
            $table = $table->where('owner', $studioId);
        }

        return $table;
    }

    public function getPublicList(int $pageSize, int $currPage): array
    {


        $table = Collection::select($this->indexCol)->where('status', 30);

        $count = $table->count();

        $result = $table
            ->orderBy('updated_at', 'desc')
            ->skip(($currPage - 1) * $pageSize)
            ->take($pageSize)
            ->get();
        return ['data' => CollectionResource::collection($result), 'total' => $count];
    }

    public function getCollection(string $id): array
    {
        $result = Collection::where('uid', $id)->first();
        if (!$result) {
            Log::warning("没有查询到数据 id={$id}");
            return ['error' => "没有查询到数据 id={$id}", 'code' => 404];
        }


        $result->fullArticleList = true;

        return ['data' => new CollectionResource($result)];
    }
}
