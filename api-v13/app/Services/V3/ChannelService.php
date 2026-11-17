<?php

namespace App\Services\V3;

use App\Exceptions\BusinessException;
use App\Models\Channel;
use App\Models\DhammaTerm;
use App\Models\Sentence;
use App\Models\WbwBlock;
use App\Services\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * channel 本身：公开目录、某个 studio 的工作区列表、我能写的、详情、建、改、删。
 *
 * 取代 v2 `ChannelController`（index 的 public / system / studio / studio-all / user-edit、
 * show、store、update、patch、destroy、showMyNumber）。v2 那份原地保留不动（铁律 2），不共用代码。
 * 权限一律经 {@see ChannelAbilityService}；channels 表的写入只给人类（{@see AgentGrantService::assertHuman()}）。
 *
 * 列表行与详情都挂上 `abilities`（本人能做什么）、可选的 `progress`（带 book + para 时，该章的完成度），
 * Resource 直接读，不查库。
 */
class ChannelService
{
    /** 列表可排序的字段；progress 只在带 book + para 时有意义 */
    public const ORDERS = ['updated_at', 'created_at', 'name', 'progress'];

    /** 列表要的列（不取 config 等大字段） */
    private const COLUMNS = [
        'channels.uid', 'channels.name', 'channels.summary', 'channels.type', 'channels.lang',
        'channels.status', 'channels.is_system', 'channels.owner_uid', 'channels.source_type',
        'channels.source_id', 'channels.created_at', 'channels.updated_at',
    ];

    public function __construct(
        private readonly ChannelAbilityService $abilities,
        private readonly AgentGrantService $agents,
    ) {}

    /**
     * 公开目录：全网公开的 channel；`system=true` 只要系统 channel（不论 status）。
     *
     * @param  array{search?: string|null, type?: string, lang?: string, system?: bool, book?: int, para?: int, order?: string, dir?: string}  $filters
     * @return LengthAwarePaginator<int, Channel>
     */
    public function listPublic(array $filters, ?string $viewerUid, int $perPage): LengthAwarePaginator
    {
        $query = $this->base($filters);
        if (! empty($filters['system'])) {
            // 系统 channel 不论 status 都列（巴利原文等是 status 10，按名字找它的调用方要能找到）
            $query->where('channels.is_system', true);
        } else {
            $query->where('channels.status', '>=', Channel::STATUS_PUBLIC);
        }

        return $this->page($query, $filters, $viewerUid, $perPage);
    }

    /**
     * 某个 studio（本人或团队）的工作区列表。
     *
     * `relation`：own 它名下的（默认），shared 直接共享给它的（**不含**经团队转来的——团队的东西在团队空间里看），
     * all 两者合起来。`collaborator`：只看某个拥有者共享过来的。
     *
     * @param  array{relation?: string, collaborator?: string, search?: string|null, type?: string, lang?: string, book?: int, para?: int, order?: string, dir?: string}  $filters
     * @return LengthAwarePaginator<int, Channel>
     *
     * @throws AuthorizationException 不是本人，也不是该团队成员
     */
    public function listForStudio(string $studioUid, string $viewerUid, array $filters, int $perPage): LengthAwarePaginator
    {
        if (! $this->abilities->canListStudio($viewerUid, $studioUid)) {
            throw new AuthorizationException(__('messages.channel_studio_forbidden'));
        }

        $relation = $filters['relation'] ?? 'own';
        $shared = $relation === 'own' ? [] : $this->abilities->sharedTo($studioUid);
        $query = $this->base($filters)->where(function (Builder $q) use ($relation, $studioUid, $shared) {
            if ($relation !== 'shared') {
                $q->orWhere('channels.owner_uid', $studioUid);
            }
            if ($relation !== 'own') {
                $q->orWhereIn('channels.uid', $shared);
            }
        });
        if ($relation === 'shared') {
            $query->where('channels.owner_uid', '<>', $studioUid);
        }
        if (! empty($filters['collaborator'])) {
            $query->where('channels.owner_uid', $filters['collaborator']);
        }

        return $this->page($query, $filters, $viewerUid, $perPage);
    }

    /**
     * 我能写内容的 channel，跨 studio（本人的、团队的、共享的）。写入前挑 channel 用。
     *
     * @param  array{search?: string|null, type?: string, lang?: string, book?: int, para?: int, order?: string, dir?: string}  $filters
     * @return LengthAwarePaginator<int, Channel>
     */
    public function listWritable(string $viewerUid, array $filters, int $perPage): LengthAwarePaginator
    {
        $query = $this->base($filters);
        $this->abilities->scopeWritable($query, $viewerUid);

        return $this->page($query, $filters, $viewerUid, $perPage);
    }

    /**
     * 详情：看不见就 403。
     *
     * @throws AuthorizationException
     */
    public function show(Channel $channel, ?string $viewerUid): Channel
    {
        $abilities = $this->abilities->abilities($channel, $viewerUid);
        if (! $abilities['read']) {
            throw new AuthorizationException(__('messages.channel_forbidden'));
        }

        return $this->decorate($channel, $abilities);
    }

    /**
     * 在 studio（本人或团队）名下建 channel。同一 studio 里不能重名。
     *
     * @param  array{name: string, type: string, lang: string, summary?: string|null, status?: int, source_type?: string|null, source_id?: string|null}  $data
     *
     * @throws AuthorizationException 不能在这个 studio 名下建，或是 AI 模型
     * @throws ValidationException 重名
     */
    public function create(string $studioUid, string $actorUid, int $actorId, array $data): Channel
    {
        $this->agents->assertHuman($actorUid);
        if (! $this->abilities->canCreateIn($actorUid, $studioUid)) {
            throw new AuthorizationException(__('messages.channel_studio_forbidden'));
        }
        $this->guardUniqueName($studioUid, $data['name']);

        $now = time() * 1000;
        $channel = new Channel;
        $channel->forceFill($data + [
            'id' => app('snowflake')->id(),
            'uid' => (string) Str::uuid(),
            'owner_uid' => $studioUid,
            'editor_id' => $actorId,
            'status' => Channel::STATUS_PRIVATE,
            'create_time' => $now,
            'modify_time' => $now,
        ])->save();

        // refresh 而不是 fresh：要保留 wasRecentlyCreated，Laravel 才会回 201
        return $this->decorate($channel->refresh(), $this->abilities->abilities($channel, $actorUid));
    }

    /**
     * 改信息（只改传了的字段）。改名时同一 studio 里不能重名。
     *
     * @param  array{name?: string, type?: string, lang?: string, summary?: string|null, status?: int, source_type?: string|null, source_id?: string|null, config?: string|null}  $data
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function update(Channel $channel, string $actorUid, array $data): Channel
    {
        $this->agents->assertHuman($actorUid);
        $abilities = $this->abilities->abilities($channel, $actorUid);
        if (! $abilities['update']) {
            throw new AuthorizationException(__('messages.channel_forbidden'));
        }
        if (isset($data['name']) && $data['name'] !== $channel->name) {
            $this->guardUniqueName($channel->owner_uid, $data['name'], $channel->uid);
        }

        if (array_key_exists('config', $data)) {
            // json 列，模型上没有 cast（加 cast 会改变 v2 读到的类型），这里自己编码
            $data['config'] = $data['config'] === null ? null : json_encode($data['config'], JSON_UNESCAPED_UNICODE);
        }
        $channel->forceFill($data + ['modify_time' => time() * 1000])->save();

        return $this->decorate($channel->fresh(), $abilities);
    }

    /**
     * 软删除。channel 下已有译文、术语或逐词时拒绝（与 v2 同口径）。返回被删的 channel（AIP-164）。
     *
     * @throws AuthorizationException
     * @throws BusinessException 下面还有内容（409）
     */
    public function delete(Channel $channel, string $actorUid): Channel
    {
        $this->agents->assertHuman($actorUid);
        $abilities = $this->abilities->abilities($channel, $actorUid);
        if (! $abilities['delete']) {
            throw new AuthorizationException(__('messages.channel_forbidden'));
        }

        $content = array_filter([
            'sentence' => Sentence::where('channel_uid', $channel->uid)->exists(),
            'term' => DhammaTerm::where('channal', $channel->uid)->exists(),
            'wbw' => WbwBlock::where('channel_uid', $channel->uid)->exists(),
        ]);
        if ($content !== []) {
            throw new BusinessException(
                __('messages.channel_has_content'),
                409,
                'channel-has-content',
                ['content' => array_keys($content)],
            );
        }

        DB::transaction(fn () => $channel->delete());

        return $this->decorate($channel, $abilities);
    }

    /**
     * 所有列表共用的查询：列、拥有者预加载、筛选、可选的章节进度。
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<Channel>
     */
    private function base(array $filters): Builder
    {
        $query = Channel::query()
            ->select(self::COLUMNS)
            ->with([UserService::eagerLoadActor('owner')[0], 'ownerGroup:uid,name'])
            ->when($filters['search'] ?? null, fn (Builder $q, string $v) => $q->where('channels.name', 'ilike', '%'.$v.'%'))
            ->when($filters['type'] ?? null, fn (Builder $q, string $v) => $q->where('channels.type', $v))
            ->when($filters['lang'] ?? null, fn (Builder $q, string $v) => $q->where('channels.lang', $v));

        if (isset($filters['book'], $filters['para'])) {
            $query->addSelect('progress_chapters.progress')
                ->leftJoin('progress_chapters', function ($join) use ($filters) {
                    $join->on('progress_chapters.channel_id', '=', 'channels.uid')
                        ->where('progress_chapters.book', $filters['book'])
                        ->where('progress_chapters.para', $filters['para']);
                });
        }

        return $query;
    }

    /**
     * 排序、分页，整页挂上 abilities。
     *
     * @param  Builder<Channel>  $query
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Channel>
     */
    private function page(Builder $query, array $filters, ?string $viewerUid, int $perPage): LengthAwarePaginator
    {
        $order = $filters['order'] ?? 'updated_at';
        if (! in_array($order, self::ORDERS, true) || ($order === 'progress' && ! isset($filters['book'], $filters['para']))) {
            $order = 'updated_at';
        }
        $column = $order === 'progress' ? 'progress_chapters.progress' : "channels.{$order}";
        $dir = ($filters['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $page = $query->orderByRaw("{$column} {$dir} nulls last")
            ->orderBy('channels.uid')
            ->paginate($perPage);

        $abilities = $this->abilities->abilitiesMany($page->getCollection(), $viewerUid);
        foreach ($page->getCollection() as $channel) {
            $this->decorate($channel, $abilities[$channel->uid]);
        }

        return $page;
    }

    /**
     * @param  array{read: bool, write: bool, update: bool, delete: bool}  $abilities
     */
    private function decorate(Channel $channel, array $abilities): Channel
    {
        $channel->loadMissing([UserService::eagerLoadActor('owner')[0], 'ownerGroup:uid,name']);
        $channel->setAttribute('abilities', $abilities);

        return $channel;
    }

    /**
     * @throws ValidationException
     */
    private function guardUniqueName(string $studioUid, string $name, ?string $exceptUid = null): void
    {
        $exists = Channel::where('owner_uid', $studioUid)
            ->where('name', $name)
            ->when($exceptUid, fn (Builder $q, string $uid) => $q->where('uid', '<>', $uid))
            ->exists();
        if ($exists) {
            throw ValidationException::withMessages(['name' => __('messages.channel_name_exists')]);
        }
    }
}
