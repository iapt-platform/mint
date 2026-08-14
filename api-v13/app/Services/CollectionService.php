<?php

namespace App\Services;

use App\Http\Api\ShareApi;
use App\Http\Api\StudioApi;
use App\Http\Resources\CollectionResource;
use App\Models\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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
        $user = AuthService::current($request);
        if (! $user) {
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

    public function buildStudioListQuery(): Builder
    {
        return Collection::select(['owner'])
            ->selectRaw('count(*) as count')
            ->where('status', 30)
            ->groupBy('owner');
    }

    public function buildStudioQuery(string $userUid, string $studioId, string $view2 = 'my'): Builder
    {
        $table = Collection::select($this->indexCol);

        if ($view2 === 'my') {
            return $table->where('owner', $studioId);
        }

        $resList = ShareApi::getResList($studioId, 4);
        $resId = array_column($resList, 'res_id');

        return $table->whereIn('uid', $resId)->where('owner', '<>', $studioId);
    }

    public function buildPublicQuery(?string $studioId = null): Builder
    {
        $table = Collection::select($this->indexCol)->where('status', 30);

        if ($studioId) {
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
        if (! $result) {
            Log::warning("没有查询到数据 id={$id}");

            return ['error' => "没有查询到数据 id={$id}", 'code' => 404];
        }

        $result->fullArticleList = true;

        return ['data' => new CollectionResource($result)];
    }
}
