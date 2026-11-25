<?php

namespace App\Services\V3;

use App\Exceptions\BusinessException;
use App\Facades\RedisCache;
use App\Models\Channel;
use App\Models\DhammaTerm;
use App\Models\TermCategory;
use App\Services\Markup\Format;
use App\Services\Markup\MarkupService;
use App\Services\Markup\RenderOptions;
use App\Services\OpenSearchService;
use App\Services\TermIndexService;
use App\Services\UserService;
use App\Tools\Tools;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * 术语 / 百科词条（只给 v3 用）：查看、按 channel / studio 列出、按词查、建、改、删。
 *
 * 取代 v2 `DhammaTermController`（show、index 的 channel / studio / word、store、update、destroy）。
 * v2 那份原地保留不动（铁律 2），不共用代码。
 *
 * 权限：
 * - 挂在 channel 下的术语跟 channel 走（{@see ChannelAbilityService}）：能读 channel 就能读，能写 channel 内容就能建、改
 * - studio 级术语（channal 为空，owner 名下所有 channel 共用）：谁都能读；建要 owner 的 create，改要 update
 *   （{@see GroupAbilityService}：团队 owner / manager 能建，editor 也能改）
 * - 删：owner 本人或团队 owner（GroupAbilityService 的 delete）；只给人类
 *
 * 写入方（`$actor`）由 {@see AgentGrantService::actor()} 给出：AI 模型凭人类授权代写时 editor 是授权人、
 * co_editor 是模型，权限按 editor 判，且只能写授权覆盖的 channel（按 book 限定的授权不覆盖术语）。
 *
 * 分类不是入参：写入时从正文的 `{{category|…}}` 抽出存进 term_categories（内容多由 LLM 生成，分类不做限制）。
 * word + tag 建立后不准改（引用方写 `[[word@tag]]`），要改就新建一条、把旧的 redirect 过去。
 * 修改带乐观锁：If-Match 必须等于当前 ETag（{@see DhammaTerm::etag()}），否则 412；不带 428。
 */
class TermService
{
    /** 列表可排序的字段 */
    public const ORDERS = ['updated_at', 'created_at', 'word'];

    /** 改了这些字段算「改内容」：co_editor 跟着这次写入走（人改清空、AI 改换成该模型） */
    private const CONTENT_FIELDS = ['meaning', 'other_meaning', 'note', 'summary', 'quality', 'content_type', 'redirect', 'language'];

    /** @var array<string, array<string, bool>> 请求内缓存：用户|owner → 能力 */
    private array $groupAbilities = [];

    public function __construct(
        private readonly ChannelAbilityService $channels,
        private readonly GroupAbilityService $groups,
        private readonly AgentGrantService $agents,
        private readonly MarkupService $markup,
        private readonly UserService $users,
    ) {}

    /* ------------------------------------------------------------------ 读 */

    /**
     * 单条，带渲染后的正文（摘要 + 正文）。
     *
     * 正文默认按术语所在 channel 解析模板；$render 可以指定按哪些 channel 解析（阅读器右栏选版本）
     * 与 read / edit 模式。指定的 channel 只认查看者看得见的，一个都看不见就退回术语所在 channel。
     * format=html 时正文输出 HTML（挂在 html 上，给用 WebView 显示的客户端），否则输出节点树（body）。
     *
     * @param  array{channels?: list<string>, mode?: string, format?: string}  $render
     *
     * @throws AuthorizationException 看不见
     */
    public function show(DhammaTerm $term, ?string $viewerUid, array $render = []): DhammaTerm
    {
        $abilities = $this->abilitiesMany(collect([$term]), $viewerUid)[$term->guid];
        if (! $abilities['read']) {
            throw new AuthorizationException(__('messages.term_forbidden'));
        }
        $this->loadRelations(new EloquentCollection([$term]));
        $this->decorate(collect([$term]), $viewerUid, withBody: true, render: [
            'channels' => $this->readableChannels($render['channels'] ?? [], $viewerUid),
            'mode' => $render['mode'] ?? 'read',
            'format' => $render['format'] ?? 'tree',
        ]);

        return $term;
    }

    /**
     * 这些 channel 里查看者看得见的，保持传入顺序。
     *
     * @param  list<string>  $uids
     * @return list<string>
     */
    private function readableChannels(array $uids, ?string $viewerUid): array
    {
        if ($uids === []) {
            return [];
        }
        $channels = Channel::whereIn('uid', $uids)->get(['uid', 'owner_uid', 'status', 'is_system']);
        $abilities = $this->channels->abilitiesMany($channels, $viewerUid);

        return array_values(array_filter($uids, fn (string $uid) => $abilities[$uid]['read'] ?? false));
    }

    /**
     * 某个 channel 的术语。
     *
     * @param  array{search?: string|null, quality?: string, category?: string, order?: string, dir?: string}  $filters
     * @return LengthAwarePaginator<int, DhammaTerm>
     *
     * @throws AuthorizationException 看不见这个 channel
     */
    public function listInChannel(Channel $channel, array $filters, ?string $viewerUid, int $perPage): LengthAwarePaginator
    {
        return $this->paginate($this->channelQuery($channel, $viewerUid), $filters, $viewerUid, $perPage);
    }

    /**
     * 某个 channel 的术语查询（列表、导出共用）。
     *
     * @return Builder<DhammaTerm>
     *
     * @throws AuthorizationException 看不见这个 channel
     */
    public function channelQuery(Channel $channel, ?string $viewerUid): Builder
    {
        if (! $this->channels->abilities($channel, $viewerUid)['read']) {
            throw new AuthorizationException(__('messages.channel_forbidden'));
        }

        return DhammaTerm::query()->where('channal', $channel->uid);
    }

    /**
     * 某个 studio（本人或团队）名下的术语：它的 channel 里的与 studio 级的。`general=true` 只要 studio 级的。
     *
     * @param  array{search?: string|null, quality?: string, category?: string, general?: bool, order?: string, dir?: string}  $filters
     * @return LengthAwarePaginator<int, DhammaTerm>
     *
     * @throws AuthorizationException 不是本人，也不是这个团队的成员
     */
    public function listInStudio(string $studioUid, array $filters, string $viewerUid, int $perPage): LengthAwarePaginator
    {
        return $this->paginate($this->studioQuery($studioUid, $filters, $viewerUid), $filters, $viewerUid, $perPage);
    }

    /**
     * 某个 studio 名下术语的查询（列表、导出共用）。`general=true` 只要 studio 级的。
     *
     * @param  array{general?: bool}  $filters
     * @return Builder<DhammaTerm>
     *
     * @throws AuthorizationException 不是本人，也不是这个团队的成员
     */
    public function studioQuery(string $studioUid, array $filters, string $viewerUid): Builder
    {
        if (! $this->groups->can($viewerUid, $studioUid, GroupAbilityService::READ)) {
            throw new AuthorizationException(__('messages.channel_studio_forbidden'));
        }
        $query = DhammaTerm::query()->where('owner', $studioUid);
        if (! empty($filters['general'])) {
            $query->studioLevel();
        }

        return $query;
    }

    /**
     * 列表的过滤与排序（不分页），导出用。
     *
     * @param  Builder<DhammaTerm>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<DhammaTerm>
     */
    public function filtered(Builder $query, array $filters): Builder
    {
        $this->applyFilters($query, $filters);

        return $query->orderBy('word')->orderBy('guid');
    }

    /**
     * 按词查：这些词在所有看得见的 channel 与 studio 级术语里的词条（编辑时看同词的其它义项、社区译法）。
     *
     * `with_body` 时每条都带渲染后的正文（同一个词通常只有几条）。
     *
     * @param  array{word: string, tag?: string|null, channel?: string, language?: string, with_body?: bool}  $filters
     * @return LengthAwarePaginator<int, DhammaTerm>
     */
    public function lookup(array $filters, ?string $viewerUid, int $perPage): LengthAwarePaginator
    {
        $words = array_values(array_filter(array_map('trim', explode(',', $filters['word']))));
        $query = DhammaTerm::query()->whereIn('word', $words);
        if (array_key_exists('tag', $filters)) {
            $this->whereTag($query, $filters['tag']);
        }
        if (! empty($filters['channel'])) {
            $query->where('channal', $filters['channel']);
        }
        if (! empty($filters['language'])) {
            $query->where('language', $filters['language']);
        }
        // 看得见的：studio 级术语，或所在 channel 会被列出给查看者
        $query->where(function (Builder $q) use ($viewerUid) {
            $q->studioLevel()->orWhereIn('channal', function ($sub) use ($viewerUid) {
                $sub->selectRaw('uid::text')->from('channels')->whereNull('deleted_at');
                $this->channels->scopeListable($sub, $viewerUid);
            });
        });

        return $this->paginate($query, ['order' => 'updated_at'] + $filters, $viewerUid, $perPage, ! empty($filters['with_body']));
    }

    /* ------------------------------------------------------------------ 写 */

    /**
     * 在 channel 里新建。owner 与语言跟 channel。
     *
     * @param  array{editor: string, co_editor: string|null, scope: array{channel: string, book: int}|null}  $actor
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     * @throws BusinessException 同一 channel 里已有这个 word@tag（409）
     */
    public function createInChannel(Channel $channel, array $actor, array $data): DhammaTerm
    {
        if (! $this->agents->covers($actor['scope'], $channel->uid, null)
            || ! $this->channels->abilities($channel, $actor['editor'])['write']) {
            throw new AuthorizationException(__('messages.term_forbidden'));
        }
        $this->guardUnique(DhammaTerm::query()->where('channal', $channel->uid), $data);

        return $this->create($actor, $data + [
            'channel' => $channel->uid,
            'owner' => $channel->owner_uid,
            'language' => $channel->lang,
        ]);
    }

    /**
     * 在 studio 名下新建 studio 级术语（不属于任何 channel）。只给人类：agent 授权只覆盖 channel。
     *
     * @param  array{editor: string, co_editor: string|null, scope: array{channel: string, book: int}|null}  $actor
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     * @throws BusinessException 同一 studio、同一语言已有这个 word@tag（409）
     */
    public function createInStudio(string $studioUid, array $actor, array $data): DhammaTerm
    {
        if ($actor['scope'] !== null || ! $this->groups->can($actor['editor'], $studioUid, GroupAbilityService::CREATE)) {
            throw new AuthorizationException(__('messages.term_forbidden'));
        }
        $this->guardUnique(
            DhammaTerm::query()->where('owner', $studioUid)->studioLevel()->where('language', $data['language']),
            $data,
        );

        return $this->create($actor, $data + ['channel' => null, 'owner' => $studioUid]);
    }

    /**
     * 改（只改传了的字段）。word / tag 不准改（FormRequest 已拦）。带乐观锁：$ifMatch 必须等于当前 ETag。
     *
     * @param  array{editor: string, co_editor: string|null, scope: array{channel: string, book: int}|null}  $actor
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     * @throws BusinessException 428 没带 If-Match；412 这条在你编辑期间被改过
     * @throws ValidationException
     */
    public function update(DhammaTerm $term, array $actor, array $data, ?string $ifMatch): DhammaTerm
    {
        if (! $this->agents->covers($actor['scope'], $term->channelUid(), null) || ! $this->can($term, $actor['editor'], 'update')) {
            throw new AuthorizationException(__('messages.term_forbidden'));
        }
        if ($ifMatch === null || $ifMatch === '') {
            throw new BusinessException(__('messages.term_if_match_required'), 428, 'precondition-required', [], 'Precondition Required');
        }
        if ($term->channelUid() !== null && array_key_exists('language', $data)) {
            throw ValidationException::withMessages(['language' => __('messages.term_language_follows_channel')]);
        }
        $this->guardRedirect($term->word, $term->tag, $data);

        $changes = array_intersect_key($data, array_flip(self::CONTENT_FIELDS));
        if (array_key_exists('note', $changes) || array_key_exists('content_type', $changes)) {
            $changes['content_type'] ??= $term->effectiveContentType();
        }
        $etag = $term->etag();
        if ($ifMatch !== $etag) {
            throw $this->modified($term);
        }

        if (! $this->applyChanges($term, $actor, $changes)) {
            throw $this->modified($term->fresh() ?? $term);
        }

        return $this->show($term->fresh(), $actor['editor']);
    }

    /**
     * 写入修改（比较并交换：版本与 modify_time 都没变才写），改了 note 就重抽分类，写完清缓存。
     * 不查权限、不渲染——由调用方负责（单条修改、导入共用）。$index=false 时不立即重建搜索索引。
     *
     * @param  array{editor: string, co_editor: string|null, scope: array{channel: string, book: int}|null}  $actor
     * @param  array<string, mixed>  $changes  只含要改的内容字段
     * @return bool 在别人之后写的（期间被改过）返回 false，什么都没写
     */
    public function applyChanges(DhammaTerm $term, array $actor, array $changes, bool $index = true): bool
    {
        $changed = DB::transaction(function () use ($term, $actor, $changes) {
            $updates = $changes + [
                'editor_uid' => $actor['editor'],
                'editor_id' => $this->users->intIdByUuid($actor['editor']) ?? 0,
                'co_editor_uid' => $actor['co_editor'],
                'modify_time' => time() * 1000,
                'version' => (int) $term->version + 1,
                'updated_at' => now(),
            ];
            // 比较并交换：版本与 modify_time 都没变才写（v2 的修改只动 modify_time）
            $affected = DhammaTerm::query()
                ->where('guid', $term->guid)
                ->where('version', (int) $term->version)
                ->where('modify_time', (int) $term->modify_time)
                ->update($updates);
            if ($affected === 0) {
                return false;
            }
            if (array_key_exists('note', $changes)) {
                $this->syncCategories($term->guid, $changes['note']);
            }

            return true;
        });
        if ($changed) {
            $this->afterWrite($term->fresh(), $index);
        }

        return $changed;
    }

    /**
     * 软删除，返回被删的术语（AIP-164）。分类留着，恢复时还在。
     *
     * @throws AuthorizationException
     */
    public function delete(DhammaTerm $term, string $actorUid): DhammaTerm
    {
        $this->agents->assertHuman($actorUid);
        if (! $this->can($term, $actorUid, 'delete')) {
            throw new AuthorizationException(__('messages.term_forbidden'));
        }
        $term->delete();
        $this->forgetCache($term);
        try {
            app(OpenSearchService::class)->delete("term_{$term->guid}");
        } catch (\Throwable $e) {
            Log::warning('Failed to remove deleted term from index', ['guid' => $term->guid, 'error' => $e->getMessage()]);
        }

        $this->loadRelations(new EloquentCollection([$term]));
        $this->decorate(collect([$term]), $actorUid, withBody: false);

        return $term;
    }

    /* ------------------------------------------------------------------ 权限 */

    /**
     * 本人对这批术语能做什么。channel 的能力整批算一次；studio 级与删除按 owner 算，同一 owner 只查一次。
     *
     * @param  Collection<int, DhammaTerm>  $terms
     * @return array<string, array{read: bool, update: bool, delete: bool}>
     */
    public function abilitiesMany(Collection $terms, ?string $userUid): array
    {
        $channelUids = $terms->pluck('channal')->filter()->unique()->values();
        $channelModels = $channelUids->isEmpty()
            ? collect()
            : Channel::whereIn('uid', $channelUids)->get(['uid', 'owner_uid', 'status', 'is_system']);
        $channelAbilities = $this->channels->abilitiesMany($channelModels, $userUid);

        $result = [];
        foreach ($terms as $term) {
            if ($term->channelUid() !== null) {
                $ch = $channelAbilities[$term->channelUid()] ?? ['read' => false, 'write' => false];
                $read = $ch['read'];
                $update = $userUid !== null && $ch['write'];
            } else {
                $read = true;
                $update = $userUid !== null && $this->groupCan($userUid, $term->owner, GroupAbilityService::UPDATE);
            }
            $delete = $userUid !== null && $this->groupCan($userUid, $term->owner, GroupAbilityService::DELETE);
            $result[$term->guid] = ['read' => $read, 'update' => $update, 'delete' => $delete];
        }

        return $result;
    }

    private function can(DhammaTerm $term, string $userUid, string $ability): bool
    {
        return $this->abilitiesMany(collect([$term]), $userUid)[$term->guid][$ability];
    }

    private function groupCan(string $userUid, string $ownerUid, string $ability): bool
    {
        return $this->groupAbilities["{$userUid}|{$ownerUid}"][$ability]
            ??= $this->groups->can($userUid, $ownerUid, $ability);
    }

    /* ------------------------------------------------------------------ 内部 */

    /**
     * @param  Builder<DhammaTerm>  $query
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, DhammaTerm>
     */
    private function paginate(Builder $query, array $filters, ?string $viewerUid, int $perPage, bool $withBody = false): LengthAwarePaginator
    {
        $this->applyFilters($query, $filters);
        $order = $filters['order'] ?? 'updated_at';
        $dir = $filters['dir'] ?? ($order === 'word' ? 'asc' : 'desc');

        $page = $query
            ->orderBy($order, $dir)
            ->orderBy('guid')
            ->paginate($perPage);
        $this->loadRelations($page->getCollection());
        $this->decorate($page->getCollection(), $viewerUid, withBody: $withBody);

        return $page;
    }

    /**
     * @param  Builder<DhammaTerm>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['search'])) {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $filters['search']).'%';
            $query->where(fn (Builder $q) => $q->where('word', 'ilike', $like)
                ->orWhere('word_en', 'ilike', $like)
                ->orWhere('meaning', 'ilike', $like)
                ->orWhere('other_meaning', 'ilike', $like));
        }
        if (! empty($filters['quality'])) {
            $filters['quality'] === 'pending'
                ? $query->where(fn (Builder $q) => $q->where('quality', 'pending')->orWhereNull('quality'))
                : $query->where('quality', $filters['quality']);
        }
        if (! empty($filters['category'])) {
            // term_categories.term_guid 是 uuid，dhamma_terms.guid 是 varchar
            $query->whereIn('guid', TermCategory::query()->selectRaw('term_guid::text')->where('category', $filters['category']));
        }
    }

    /**
     * @return list<string>
     */
    private function relations(): array
    {
        return [
            ...UserService::eagerLoadActor(),
            UserService::eagerLoadAssistant('coEditor'),
            // 只取分类名（与外键、排序）；term_categories.id 不对外，也用不到
            'categories:term_guid,category,position',
        ];
    }

    /**
     * 预加载 {@see self::relations()} 与所属 channel。
     *
     * channel 只给真属于某个 channel 的加载：studio 级的老数据 channal 是空串，
     * 统一 `with('channel')` 会拿空串去比 uuid 列，PostgreSQL 直接报错。
     *
     * @param  EloquentCollection<int, DhammaTerm>  $terms
     */
    private function loadRelations(EloquentCollection $terms): void
    {
        $terms->load($this->relations());
        $terms->filter(fn (DhammaTerm $term) => $term->channelUid() !== null)->load('channel:uid,name');
        $terms->each(function (DhammaTerm $term) {
            if ($term->channelUid() === null) {
                $term->setRelation('channel', null);
            }
        });
    }

    /**
     * 挂上 abilities、摘要纯文本，详情另挂渲染后的正文。整批渲染一次。
     *
     * 正文 = 摘要 + 正文（摘要放在前面，不加分隔）。摘要纯文本：有摘要用摘要，没有取正文第一段。
     * $render 只用于正文：channels 非空时代替术语所在 channel，mode 为 read / edit，
     * format 为 tree（挂 body）/ html（挂 html）。
     *
     * @param  Collection<int, DhammaTerm>  $terms
     * @param  array{channels?: list<string>, mode?: string, format?: string}  $render
     */
    private function decorate(Collection $terms, ?string $viewerUid, bool $withBody, array $render = []): void
    {
        if ($terms->isEmpty()) {
            return;
        }
        $abilities = $this->abilitiesMany($terms, $viewerUid);

        $items = [];
        foreach ($terms as $term) {
            $base = [
                'channels' => $term->channal ? [$term->channal] : [],
                'contentType' => $term->effectiveContentType(),
            ];
            $summary = trim((string) $term->summary);
            $items["{$term->guid}|summary"] = $base + ['source' => $summary !== '' ? $summary : (string) $term->note];
            if ($withBody) {
                $bodyChannels = ! empty($render['channels']) ? $render['channels'] : $base['channels'];
                $items["{$term->guid}|body"] = ['channels' => $bodyChannels] + $base + ['source' => trim($summary."\n\n".(string) $term->note)];
            }
        }
        $bodyFormat = ($render['format'] ?? 'tree') === 'html' ? Format::Html : Format::Tree;
        $rendered = $this->markup->renderManyAs(
            $items,
            new RenderOptions(mode: $render['mode'] ?? 'read'),
            $withBody ? [Format::Text, $bodyFormat] : [Format::Text],
        );

        foreach ($terms as $term) {
            $term->abilities = $abilities[$term->guid];
            $text = (string) $rendered[Format::Text->value]["{$term->guid}|summary"];
            $term->summary_text = trim((string) $term->summary) !== '' ? trim($text) : $this->firstParagraph($text);
            if ($withBody && $bodyFormat === Format::Html) {
                $term->html = (string) $rendered[Format::Html->value]["{$term->guid}|body"];
            } elseif ($withBody) {
                $term->body = $rendered[Format::Tree->value]["{$term->guid}|body"];
            }
        }
    }

    /**
     * 纯文本渲染（{@see Format::Text}）里段落之间只隔一个换行，第一个非空行就是第一段。
     */
    private function firstParagraph(string $text): string
    {
        foreach (preg_split('/\n/u', $text) ?: [] as $paragraph) {
            $paragraph = trim($paragraph);
            if ($paragraph !== '') {
                return $paragraph;
            }
        }

        return '';
    }

    /**
     * @param  array{editor: string, co_editor: string|null, scope: array{channel: string, book: int}|null}  $actor
     * @param  array<string, mixed>  $data  已含 channel / owner / language
     */
    private function create(array $actor, array $data): DhammaTerm
    {
        $this->guardRedirect($data['word'], $data['tag'] ?? null, $data);

        return $this->show($this->insert($actor, $data), $actor['editor']);
    }

    /**
     * 写入一条新术语并抽分类、清缓存。不查权限、不查重、不渲染——由调用方负责（单条新建、导入共用）。
     * $index=false 时不立即重建搜索索引。
     *
     * @param  array{editor: string, co_editor: string|null, scope: array{channel: string, book: int}|null}  $actor
     * @param  array<string, mixed>  $data  已含 channel / owner / language
     */
    public function insert(array $actor, array $data, bool $index = true): DhammaTerm
    {
        $now = time() * 1000;
        $term = new DhammaTerm;
        $term->forceFill([
            'id' => app('snowflake')->id(),
            'guid' => (string) Str::uuid(),
            'word' => $data['word'],
            'word_en' => Tools::getWordEn($data['word']),
            'tag' => $this->normalizeTag($data['tag'] ?? null),
            'meaning' => $data['meaning'],
            'other_meaning' => $data['other_meaning'] ?? null,
            'note' => $data['note'] ?? null,
            'summary' => $data['summary'] ?? null,
            'quality' => $data['quality'] ?? null,
            'content_type' => $data['content_type'] ?? DhammaTerm::CONTENT_TYPE_V3,
            'redirect' => $data['redirect'] ?? null,
            'channal' => $data['channel'],
            'language' => $data['language'],
            'owner' => $data['owner'],
            'editor_uid' => $actor['editor'],
            'editor_id' => $this->users->intIdByUuid($actor['editor']) ?? 0,
            'co_editor_uid' => $actor['co_editor'],
            'version' => 1,
            'create_time' => $now,
            'modify_time' => $now,
        ]);
        DB::transaction(function () use ($term) {
            $term->save();
            $this->syncCategories($term->guid, $term->note);
        });
        $this->afterWrite($term, $index);

        return $term->fresh();
    }

    /**
     * 同一范围里 word + tag 唯一（tag 空串与 null 视为相同）。
     *
     * @param  Builder<DhammaTerm>  $scope
     * @param  array<string, mixed>  $data
     *
     * @throws BusinessException
     */
    private function guardUnique(Builder $scope, array $data): void
    {
        $existing = $this->findExisting($scope, $data['word'], $data['tag'] ?? null)?->guid;
        if ($existing !== null) {
            throw new BusinessException(__('messages.term_exists'), 409, 'term-exists', ['existing' => $existing]);
        }
    }

    /**
     * 范围内同 word + tag 的那一条（tag 空串与 null 视为相同）。
     *
     * @param  Builder<DhammaTerm>  $scope
     */
    public function findExisting(Builder $scope, string $word, ?string $tag): ?DhammaTerm
    {
        $query = $scope->where('word', $word);
        $this->whereTag($query, $tag);

        return $query->first();
    }

    /**
     * redirect 写成 word 或 word@tag，不能指向自己。目标是否存在不查（内容多由 LLM 生成，批量写入时目标可能还没建）。
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function guardRedirect(string $word, ?string $tag, array $data): void
    {
        $redirect = $data['redirect'] ?? null;
        if ($redirect === null || $redirect === '') {
            return;
        }
        [$targetWord, $targetTag] = array_pad(explode('@', $redirect, 2), 2, '');
        if ($targetWord === $word && $targetTag === (string) $this->normalizeTag($tag)) {
            throw ValidationException::withMessages(['redirect' => __('messages.term_redirect_self')]);
        }
    }

    /**
     * @param  Builder<DhammaTerm>  $query
     */
    private function whereTag(Builder $query, ?string $tag): void
    {
        $tag = $this->normalizeTag($tag);
        $tag === null
            ? $query->where(fn (Builder $q) => $q->whereNull('tag')->orWhere('tag', ''))
            : $query->where('tag', $tag);
    }

    public function normalizeTag(?string $tag): ?string
    {
        $tag = trim((string) $tag);

        return $tag === '' ? null : $tag;
    }

    /**
     * 从正文抽出 {{category|…}}，整体替换这条术语的分类。同名的只留第一个。
     */
    private function syncCategories(string $guid, ?string $note): void
    {
        preg_match_all('/\{\{category\|([^}|]+)\}\}/u', (string) $note, $matches);
        $names = array_values(array_unique(array_filter(array_map(
            fn (string $name) => mb_substr(trim($name), 0, 128),
            $matches[1] ?? [],
        ))));

        TermCategory::where('term_guid', $guid)->delete();
        foreach ($names as $position => $name) {
            TermCategory::create(['term_guid' => $guid, 'category' => $name, 'position' => $position]);
        }
    }

    public function modified(DhammaTerm $term): BusinessException
    {
        return new BusinessException(
            __('messages.term_modified'),
            412,
            'term-modified',
            ['etag' => $term->etag()],
            'Precondition Failed',
        );
    }

    /**
     * 写入后：清 v2 的术语缓存（v4 / v6 的旧渲染还在读），重建 OpenSearch 索引（全站搜索用；失败不阻断写入）。
     * $index=false 时只清缓存，索引由每周的 `opensearch:index-term` 补上（大批导入用）。
     */
    public function afterWrite(DhammaTerm $term, bool $index = true): void
    {
        $this->forgetCache($term);
        if (! $index) {
            return;
        }
        try {
            app(TermIndexService::class)->index($term->guid);
        } catch (\Throwable $e) {
            Log::warning('Failed to index term after write', ['guid' => $term->guid, 'error' => $e->getMessage()]);
        }
    }

    /**
     * v2 的缓存键是 `/term/{channel uid}/{word}`；studio 级术语要清 owner 名下每个 channel 的
     * （v2 自己那份清不掉：拿模型而不是 uid 拼键，见 DhammaTermController::deleteCache 的 FIXME）。
     */
    private function forgetCache(DhammaTerm $term): void
    {
        $channels = $term->channelUid() !== null
            ? [$term->channelUid()]
            : Channel::where('owner_uid', $term->owner)->pluck('uid')->all();
        foreach ($channels as $uid) {
            RedisCache::forget("/term/{$uid}/{$term->word}");
        }
    }
}
