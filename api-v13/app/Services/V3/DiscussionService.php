<?php

namespace App\Services\V3;

use App\Exceptions\BusinessException;
use App\Http\Api\ChannelApi;
use App\Http\Api\MdRender;
use App\Http\Api\Mq;
use App\Http\Api\ShareApi;
use App\Http\Resources\DiscussionResource as MqDiscussionPayload;
use App\Models\Article;
use App\Models\ArticleCollection;
use App\Models\Collection as ArticleCollectionOwner;
use App\Models\DhammaTerm;
use App\Models\Discussion;
use App\Models\Sentence;
use App\Models\Task;
use App\Models\Wbw;
use App\Models\WbwBlock;
use App\Services\UserService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * 评论线程（`/v3/discussions`）的业务逻辑：查询、可见性、权限、写入。
 *
 * 底层是 `discussions` 表，v2 的 `DiscussionController` 另有一套实现，两边不共用——
 * 见 CLAUDE.md 铁律 2。
 *
 * 线程模型固定两层：话题（parent 为空）+ 一层回复。回复的类型、挂靠对象一律跟随话题，
 * 不单独存取；状态、标题、severity 只对话题有意义。
 *
 * 批注（note / commentary，{@see Discussion::INJECTED_TYPES}）也走这里，但它不是评论：
 * 锚定在句子的某个位置、注入该句所在 channel 的阅读页（所有读者可见），所以
 * - 只能挂在句子上，只有该句的管理者（channel 编辑者）能新建；改、删同评论（作者或管理者）；
 * - 其他用户可以回复；没有开关状态，不能与评论线程互转；新建批注不发通知；
 * - 列表与统计不带 type 时只算评论线程，要批注须显式 type=note / commentary。
 *
 * glossary 是评论线程（有回复和开关），但像批注一样只能挂在句子上、只有该句的管理者能发起，
 * 对所有人公开可见。锚点除批注外，issue 与 glossary 也收（{@see Discussion::ANCHORED_TYPES}）。
 *
 * 写入方（`$actor`）由 {@see AgentGrantService::actor()} 给出：人类直接写，或 AI 模型凭人类
 * 授权代写。代写时 editor 是授权人、co_editor 是模型，权限一律按 editor 判，
 * 而且只能写授权覆盖的 channel / book 里的东西。
 *
 * 可见性沿用 v2 `view=question` 的口径，但这里对列表、单条、回复一视同仁（v2 只在
 * 列表上做了这个判断）：
 * - 未登录看不到不公开的话题（discussion）；
 * - basic 角色在自己无权管理的对象上，只能看到自己发起的不公开话题。
 * issue 2026-10-21 起公开：它会注入阅读页（高亮 + 点击看内容），阅读页是公开且共享缓存的。
 *
 * TODO: 以下两项需要加列，2026-10-15 用户决定暂缓：
 * - qa 的「已回答」现在用 status=close 表示；社区通行做法是把某条回复标为答案
 *   （GitHub Discussions 的 isAnswer），需要加一列（如 answer_id）。
 * - 关闭话题不留痕，不知道是谁、什么时候关的。可以学 Google Drive 用带 action 的
 *   回复记录，也可以加 closed_by / closed_at 两列。
 */
class DiscussionService
{
    /** 登录即可发起的话题类型 */
    private const COLLABORATION_TYPES = ['discussion', 'issue'];

    /** 不公开的话题类型：未登录用户看不到，basic 角色在无权管理的对象上只能看自己的 */
    private const PRIVATE_TYPES = ['discussion'];

    /** 批注的锚点字段：定位到句子里的哪一段 */
    private const ANCHOR_FIELDS = ['pos_start', 'pos_end', 'quote_exact', 'quote_prefix', 'quote_suffix'];

    /**
     * 一次调用内的权限缓存。每个公开方法进来先清空：Service 随控制器实例存活，
     * 控制器实例又会被路由缓存（测试里、常驻进程里都会跨请求复用），不清空就会把
     * 上一个请求的权限带进下一个请求。
     *
     * @var array<string, array<int, string>> 用户 uid → 可编辑的 channel uid 列表
     */
    private array $editableChannels = [];

    /** @var array<string, bool> "用户 uid|对象类型|对象 id" → 是否能管理该对象 */
    private array $managers = [];

    public function __construct(
        private readonly UserService $users,
        private readonly AgentGrantService $grants,
    ) {}

    /**
     * 某个对象上的话题列表。删除了但还有回复的话题以墓碑形式出现。
     *
     * @param  array{target_type: string, target_id: string, type?: string, status?: string, severity?: string, sort?: string}  $filters
     * @return LengthAwarePaginator<int, Discussion>
     */
    public function listTopics(array $filters, ?string $viewerUid, int $perPage): LengthAwarePaginator
    {
        $this->resetCache();
        $query = $this->topicQuery()
            ->where('res_type', $filters['target_type'])
            ->where('res_id', $filters['target_id'])
            ->tap(fn ($q) => $this->applyTypeFilter($q, $filters['type'] ?? null))
            ->when($filters['status'] ?? null, fn ($q, $value) => $q->where('status', $value))
            ->when($filters['severity'] ?? null, fn ($q, $value) => $q->whereIn('severity', explode(',', $value)))
            ->withCount('replies as reply_count');
        $this->applyVisibility($query, $viewerUid, $filters['target_type'], $filters['target_id']);

        $sort = $filters['sort'] ?? '-created_at';
        $query->orderBy(ltrim($sort, '-'), str_starts_with($sort, '-') ? 'desc' : 'asc');

        $page = $query->paginate($perPage);
        $this->decorate($page->getCollection(), $viewerUid);

        return $page;
    }

    /**
     * 某个对象上的话题概况：各状态的话题数（只数没删的、当前用户看得见的），
     * 以及当前用户能在这里发起哪些类型的话题。
     *
     * @param  array{target_type: string, target_id: string, type?: string}  $filters
     * @return array{status_counts: array{active: int, close: int}, creatable_types: list<string>}
     */
    public function summary(array $filters, ?string $viewerUid): array
    {
        $this->resetCache();
        $query = Discussion::query()
            ->whereNull('parent')
            ->whereIn('type', Discussion::COMMENT_TYPES)
            ->where('res_type', $filters['target_type'])
            ->where('res_id', $filters['target_id'])
            ->tap(fn ($q) => $this->applyTypeFilter($q, $filters['type'] ?? null));
        $this->applyVisibility($query, $viewerUid, $filters['target_type'], $filters['target_id']);

        $counts = $query->toBase()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'status_counts' => [
                'active' => (int) ($counts['active'] ?? 0),
                'close' => (int) ($counts['close'] ?? 0),
            ],
            'creatable_types' => $this->creatableTypes($viewerUid, $filters['target_type'], $filters['target_id']),
        ];
    }

    /**
     * 一批对象（句子 / 逐词）的讨论角标：未关闭的评论线程数，以及其中查看者发起的数目。
     *
     * 一条 group by 查完；可见性口径同列表（{@see self::applyVisibility()}），basic 角色能管理
     * 哪些对象批量算出，不逐个查。按请求顺序返回，没有讨论的给 0。
     *
     * TODO: 课程迁 v3 之后，补上 v2 课程模式的「学生的讨论」：逐词按坐标（book / paragraph / wid）
     * 匹配学员 channel 上同一个词的讨论（v2 `DiscussionCountController@store` 的 course_id 分支，
     * 前端 `WbwPaliDiscussionIcon` 的 wbw 参数）。现在只数这个对象本身上的。
     *
     * @param  list<array{type: string, id: string}>  $targets
     * @return list<array{target_type: string, target_id: string, total: int, mine: int}>
     */
    public function batchCount(array $targets, ?string $viewerUid): array
    {
        $this->resetCache();
        $idsByType = collect($targets)->groupBy('type')->map(fn ($items) => $items->pluck('id')->unique()->values()->all());

        $query = Discussion::query()
            ->whereNull('parent')
            ->where('status', 'active')
            ->whereIn('type', Discussion::THREAD_TYPES)
            ->where(function ($q) use ($idsByType) {
                foreach ($idsByType as $type => $ids) {
                    $q->orWhere(fn ($q) => $q->where('res_type', $type)->whereIn('res_id', $ids));
                }
            });
        if ($viewerUid === null) {
            $query->whereNotIn('type', self::PRIVATE_TYPES);
        } elseif ($this->isBasic($viewerUid)) {
            $managed = $this->managedTargets($viewerUid, $idsByType->all());
            $query->where(fn ($q) => $q->whereNotIn('type', self::PRIVATE_TYPES)
                ->orWhere('editor_uid', $viewerUid)
                ->orWhereIn('res_id', $managed));
        }

        $rows = $query->toBase()
            ->select('res_type', 'res_id', DB::raw('count(*) as total'))
            ->selectRaw('sum(case when editor_uid = ? then 1 else 0 end) as mine', [$viewerUid])
            ->groupBy('res_type', 'res_id')
            ->get()
            ->keyBy(fn ($row) => $row->res_type.'/'.$row->res_id);

        return array_map(function (array $target) use ($rows) {
            $row = $rows[$target['type'].'/'.$target['id']] ?? null;

            return [
                'target_type' => $target['type'],
                'target_id' => $target['id'],
                'total' => (int) ($row->total ?? 0),
                'mine' => (int) ($row->mine ?? 0),
            ];
        }, array_values($targets));
    }

    /**
     * 单条（话题或回复）。删除了的回复、删除了且没有回复的话题都当作不存在。
     */
    public function show(Discussion $discussion, ?string $viewerUid): Discussion
    {
        $this->resetCache();
        $this->assertComment($discussion);
        if ($discussion->trashed() && ($discussion->parent !== null || ! $discussion->replies()->exists())) {
            abort(404);
        }
        $topic = $discussion->parent === null ? $discussion : Discussion::withTrashed()->findOrFail($discussion->parent);
        $this->assertVisible($topic, $viewerUid);

        $discussion->load(self::relations());
        if ($discussion->parent === null) {
            $discussion->loadCount('replies as reply_count');
        } else {
            $discussion->type = $topic->type;
        }
        $this->decorate(collect([$discussion]), $viewerUid);

        return $discussion;
    }

    /**
     * 话题下的回复，按时间正序。墓碑话题的回复照样可读。
     *
     * @return LengthAwarePaginator<int, Discussion>
     */
    public function listReplies(Discussion $topic, ?string $viewerUid, int $perPage): LengthAwarePaginator
    {
        $this->resetCache();
        $this->assertComment($topic);
        if ($topic->parent !== null) {
            abort(404);
        }
        $this->assertVisible($topic, $viewerUid);

        $page = Discussion::query()
            ->where('parent', $topic->id)
            ->with(self::relations())
            ->orderBy('created_at')
            ->paginate($perPage);
        // 回复的类型一律跟随话题：历史数据里有 qa 话题下 type=discussion 的回复
        $page->getCollection()->each(fn (Discussion $reply) => $reply->type = $topic->type);
        $this->decorate($page->getCollection(), $viewerUid);

        return $page;
    }

    /**
     * 发起话题，或新建一条批注（批注不发通知）。
     *
     * @param  array{editor: string, co_editor: string|null, scope: array{channel: string, book: int}|null}  $actor
     * @param  array{target_type: string, target_id: string, type?: string, title?: string|null, content?: string|null, severity?: string|null, pos_start?: int|null, pos_end?: int|null, quote_exact?: string|null, quote_prefix?: string|null, quote_suffix?: string|null, tpl_id?: string|null, notify?: bool}  $data
     */
    public function createTopic(array $actor, array $data): Discussion
    {
        $this->resetCache();
        $userUid = $actor['editor'];
        if (! $this->targetExists($data['target_type'], $data['target_id'])) {
            throw ValidationException::withMessages(['target_id' => __('messages.discussion_target_not_found')]);
        }
        $this->assertGrantCovers($actor, $data['target_type'], $data['target_id']);
        $type = $data['type'] ?? 'discussion';
        if (! in_array($type, $this->creatableTypes($userUid, $data['target_type'], $data['target_id']), true)) {
            abort(403, __('messages.discussion_forbidden'));
        }

        $topic = new Discussion;
        $topic->res_type = $data['target_type'];
        $topic->res_id = $data['target_id'];
        $topic->type = $type;
        $topic->title = $data['title'] ?? null;
        $topic->content = $data['content'] ?? null;
        $topic->content_type = 'markdown';
        $topic->severity = $data['severity'] ?? null;
        $topic->tpl_id = $data['tpl_id'] ?? null;
        $topic->fill(array_intersect_key($data, array_flip(self::ANCHOR_FIELDS)));
        $topic->editor_uid = $userUid;
        $topic->co_editor_uid = $actor['co_editor'];
        $topic->save();

        if (! $this->isAnnotation($topic) && ($data['notify'] ?? true)) {
            $this->notify($topic);
        }

        return $this->fresh($topic, $userUid);
    }

    /**
     * 回复话题。话题必须没关闭；不能回复一条回复。
     *
     * @param  array{editor: string, co_editor: string|null, scope: array{channel: string, book: int}|null}  $actor
     * @param  array{content: string, notify?: bool}  $data
     */
    public function createReply(Discussion $topic, array $actor, array $data): Discussion
    {
        $this->resetCache();
        $userUid = $actor['editor'];
        $this->assertComment($topic);
        if ($topic->parent !== null) {
            throw ValidationException::withMessages(['discussion' => __('messages.discussion_reply_to_reply')]);
        }
        $this->assertVisible($topic, $userUid);
        $this->assertGrantCovers($actor, $topic->res_type, $topic->res_id);
        if ($topic->status === 'close') {
            throw new BusinessException(__('messages.discussion_closed'), 409, 'discussion-closed');
        }
        if (! $this->canReply($userUid, $topic)) {
            abort(403, __('messages.discussion_forbidden'));
        }

        $reply = DB::transaction(function () use ($topic, $userUid, $actor, $data) {
            $reply = new Discussion;
            $reply->res_type = $topic->res_type;
            $reply->res_id = $topic->res_id;
            $reply->type = $topic->type;
            $reply->parent = $topic->id;
            $reply->content = $data['content'];
            $reply->content_type = 'markdown';
            $reply->editor_uid = $userUid;
            $reply->co_editor_uid = $actor['co_editor'];
            $reply->save();
            // children_count 列只有 v2 的 sent-discussion-tree 在读，v3 现算；这里只是让它别漂移
            $topic->increment('children_count');

            return $reply;
        });

        if ($data['notify'] ?? true) {
            $this->notify($reply);
        }

        return $this->fresh($reply, $userUid);
    }

    /**
     * 局部更新。title / status / type / severity 只对话题有效。
     *
     * 改 type 时回复跟着改；新类型不收原来的 severity 时，severity 清空。锚点不清：
     * 留着不显示也不影响校验，改回可带锚点的类型时位置自动恢复。
     * 改成 glossary 须挂在句子上、且当前用户是该句的管理者（同新建）。
     * 批注：锚点可改，没有 status，type 只能在 note / commentary 之间改，
     * 正文不能清空，commentary 的正文必须是义注句子模板（按改后的 type 与正文判）。
     *
     * 改了内容（标题、正文、锚点）时 co_editor 改成这次写入的协作者：人亲自改则清空，
     * AI 代改则换成该模型。只改 status / type / severity 不动 co_editor。
     *
     * @param  array{editor: string, co_editor: string|null, scope: array{channel: string, book: int}|null}  $actor
     * @param  array{title?: string, content?: string|null, status?: string, type?: string, severity?: string|null, pos_start?: int|null, pos_end?: int|null, quote_exact?: string|null, quote_prefix?: string|null, quote_suffix?: string|null}  $data
     */
    public function update(Discussion $discussion, array $actor, array $data): Discussion
    {
        $this->resetCache();
        $userUid = $actor['editor'];
        $this->assertComment($discussion);
        $this->assertGrantCovers($actor, $discussion->res_type, $discussion->res_id);
        $abilities = $this->abilities($discussion, $userUid);
        if (! $abilities['update']) {
            abort(403, __('messages.discussion_forbidden'));
        }
        $isTopic = $discussion->parent === null;
        $topicOnly = array_intersect_key($data, array_flip(['title', 'status', 'type', 'severity']));
        if (! $isTopic && $topicOnly !== []) {
            throw ValidationException::withMessages(
                array_fill_keys(array_keys($topicOnly), __('messages.discussion_topic_only'))
            );
        }
        $this->assertAnnotationUpdate($discussion, $data);
        if (array_key_exists('status', $data) && ! $abilities['close']) {
            abort(403, __('messages.discussion_forbidden'));
        }

        $type = $data['type'] ?? $discussion->type;
        if ($type === Discussion::TYPE_GLOSSARY && $discussion->type !== Discussion::TYPE_GLOSSARY) {
            $this->assertCanBecomeGlossary($discussion, $userUid);
        }
        if (array_key_exists('severity', $data)) {
            if ($data['severity'] !== null && ! in_array($data['severity'], Discussion::SEVERITIES[$type] ?? [], true)) {
                throw ValidationException::withMessages(['severity' => __('messages.discussion_severity_not_allowed')]);
            }
        } elseif ($discussion->severity !== null && ! in_array($discussion->severity, Discussion::SEVERITIES[$type] ?? [], true)) {
            $data['severity'] = null;
        }

        DB::transaction(function () use ($discussion, $data, $actor) {
            $typeChanged = array_key_exists('type', $data) && $data['type'] !== $discussion->type;
            $authored = array_intersect_key($data, array_flip(['title', 'content', ...self::ANCHOR_FIELDS]));
            $discussion->fill($authored);
            // 改了内容，co_editor 就跟着这次写的人走：人亲自改 → 清空，AI 代改 → 换成这个模型。
            // 只改状态 / 类型 / severity 是管理动作，不动内容的署名。
            if ($authored !== []) {
                $discussion->co_editor_uid = $actor['co_editor'];
            }
            foreach (['status', 'type', 'severity'] as $field) {
                if (array_key_exists($field, $data)) {
                    $discussion->{$field} = $data[$field];
                }
            }
            $discussion->save();
            if ($typeChanged) {
                Discussion::where('parent', $discussion->id)->update(['type' => $discussion->type]);
            }
        });

        return $this->fresh($discussion, $userUid);
    }

    /**
     * 软删除。作者本人或挂靠对象的管理者能删（v2 只允许作者）。
     *
     * 删话题不动回复：有回复的话题以墓碑形式继续出现在列表里；删回复会让话题的
     * children_count 减一。返回删除后的资源（AIP-164：软删的资源没有消失）。
     *
     * @param  array{editor: string, co_editor: string|null, scope: array{channel: string, book: int}|null}  $actor
     */
    public function delete(Discussion $discussion, array $actor): Discussion
    {
        $this->resetCache();
        $userUid = $actor['editor'];
        $this->assertComment($discussion);
        $this->assertGrantCovers($actor, $discussion->res_type, $discussion->res_id);
        if (! $this->abilities($discussion, $userUid)['delete']) {
            abort(403, __('messages.discussion_forbidden'));
        }

        DB::transaction(function () use ($discussion) {
            $discussion->delete();
            if ($discussion->parent !== null) {
                Discussion::where('id', $discussion->parent)
                    ->where('children_count', '>', 0)
                    ->decrement('children_count');
            }
        });

        return $this->fresh($discussion, $userUid);
    }

    /**
     * 编辑者与协作者的预加载声明。
     *
     * @return array<int, string>
     */
    private static function relations(): array
    {
        return [...UserService::eagerLoadActor(), UserService::eagerLoadAssistant('coEditor')];
    }

    /**
     * AI 代写时，授权必须覆盖这次写入落到的 channel / book；只有句子能定位到 channel，
     * 挂在别的对象上的一律不覆盖。人类直接写不受限。
     *
     * @param  array{editor: string, co_editor: string|null, scope: array{channel: string, book: int}|null}  $actor
     */
    private function assertGrantCovers(array $actor, string $targetType, string $targetId): void
    {
        if ($actor['scope'] === null) {
            return;
        }
        $sentence = $targetType === 'sentence'
            ? Sentence::where('uid', $targetId)->first(['channel_uid', 'book_id'])
            : null;
        if (! $this->grants->covers($actor['scope'], $sentence?->channel_uid, $sentence ? (int) $sentence->book_id : null)) {
            abort(403, __('messages.agent_grant_scope'));
        }
    }

    /**
     * 这批对象里查看者能管理的（所在 channel 可编辑），一次查出，供 basic 角色的可见性用。
     *
     * @param  array<string, list<string>>  $idsByType
     * @return list<string>
     */
    private function managedTargets(string $userUid, array $idsByType): array
    {
        $editable = $this->editableChannels($userUid);
        $managed = [];
        if (! empty($idsByType['sentence'])) {
            array_push($managed, ...Sentence::whereIn('uid', $idsByType['sentence'])
                ->whereIn('channel_uid', $editable)->pluck('uid')->all());
        }
        if (! empty($idsByType['wbw'])) {
            $blocks = WbwBlock::whereIn('channel_uid', $editable)->select('uid');
            array_push($managed, ...Wbw::whereIn('uid', $idsByType['wbw'])
                ->whereIn('block_uid', $blocks)->pluck('uid')->all());
        }

        return array_map('strval', $managed);
    }

    private function isAnnotation(Discussion $discussion): bool
    {
        return in_array($discussion->type, Discussion::INJECTED_TYPES, true);
    }

    /**
     * 列表 / 统计的类型过滤：传了就按传的，没传只算评论线程（批注要显式点名）。
     *
     * @param  Builder<Discussion>  $query
     */
    private function applyTypeFilter(Builder $query, ?string $type): void
    {
        $type === null
            ? $query->whereIn('type', Discussion::THREAD_TYPES)
            : $query->where('type', $type);
    }

    /**
     * 批注与评论线程在 PATCH 上的差异，见 {@see self::update()}。
     *
     * @param  array<string, mixed>  $data
     */
    private function assertAnnotationUpdate(Discussion $discussion, array $data): void
    {
        $isAnnotation = $this->isAnnotation($discussion);
        $errors = [];
        if (array_key_exists('type', $data)
            && in_array($data['type'], Discussion::INJECTED_TYPES, true) !== $isAnnotation) {
            $errors['type'] = __('messages.discussion_type_group');
        }
        if (! in_array($data['type'] ?? $discussion->type, Discussion::ANCHORED_TYPES, true)) {
            foreach (array_intersect(self::ANCHOR_FIELDS, array_keys($data)) as $field) {
                $errors[$field] = __('messages.discussion_anchor_thread');
            }
        }
        if ($isAnnotation) {
            if (array_key_exists('status', $data)) {
                $errors['status'] = __('messages.discussion_annotation_no_status');
            }
            $type = $data['type'] ?? $discussion->type;
            $content = trim((string) (array_key_exists('content', $data) ? $data['content'] : $discussion->content));
            if ($content === '') {
                $errors['content'] = __('validation.required', ['attribute' => 'content']);
            } elseif ($type === Discussion::TYPE_COMMENTARY && ! preg_match(Discussion::COMMENTARY_PATTERN, $content)) {
                $errors['content'] = __('messages.discussion_commentary_format');
            }
        }
        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * 话题改成 glossary：与新建同口径，只能挂在句子上、只有该句的管理者能改。
     */
    private function assertCanBecomeGlossary(Discussion $discussion, string $userUid): void
    {
        if ($discussion->res_type !== 'sentence') {
            throw ValidationException::withMessages(['type' => __('messages.discussion_glossary_target')]);
        }
        if (! $this->canManageTarget($userUid, $discussion->res_type, $discussion->res_id)) {
            abort(403, __('messages.discussion_forbidden'));
        }
    }

    private function resetCache(): void
    {
        $this->editableChannels = [];
        $this->managers = [];
    }

    /**
     * 话题的基础查询：只要评论类型、只要顶级；删了的话题只有还挂着回复时才留下（墓碑）。
     *
     * @return Builder<Discussion>
     */
    private function topicQuery(): Builder
    {
        return Discussion::withTrashed()
            ->whereNull('parent')
            ->whereIn('type', Discussion::COMMENT_TYPES)
            ->where(fn ($q) => $q->whereNull('deleted_at')->orWhereHas('replies'))
            ->with(self::relations());
    }

    /**
     * 列表上的可见性过滤。口径见类注释。
     *
     * @param  Builder<Discussion>  $query
     */
    private function applyVisibility(Builder $query, ?string $viewerUid, string $targetType, string $targetId): void
    {
        if ($viewerUid === null) {
            $query->whereNotIn('type', self::PRIVATE_TYPES);

            return;
        }
        if ($this->isBasic($viewerUid) && ! $this->canManageTarget($viewerUid, $targetType, $targetId)) {
            $query->where(fn ($q) => $q->whereNotIn('type', self::PRIVATE_TYPES)
                ->orWhere('editor_uid', $viewerUid));
        }
    }

    /**
     * 单条话题的可见性。看不见就当作不存在（404），不暴露它的存在。
     */
    private function assertVisible(Discussion $topic, ?string $viewerUid): void
    {
        if (! in_array($topic->type, self::PRIVATE_TYPES, true)) {
            return;
        }
        if ($viewerUid === null) {
            abort(404);
        }
        if ($topic->editor_uid !== $viewerUid
            && $this->isBasic($viewerUid)
            && ! $this->canManageTarget($viewerUid, $topic->res_type, $topic->res_id)) {
            abort(404);
        }
    }

    /**
     * 评论线程与批注以外的记录（如任务状态日志）不归 v3 discussions 管。
     */
    private function assertComment(Discussion $discussion): void
    {
        if (! in_array($discussion->type, Discussion::COMMENT_TYPES, true)) {
            abort(404);
        }
    }

    /**
     * 当前用户能在这个对象上发起哪些类型的话题。
     *
     * 协作类（discussion / issue）登录即可；qa / help 沿用 v2 `can_create` 的口径：
     * 只能发在文章上，而且只有文章的编辑者能发；批注（note / commentary）与 glossary 只能加在
     * 句子上，而且只有该句的管理者能加（v2 登录即可，v3 收紧）。
     *
     * @return list<string>
     */
    private function creatableTypes(?string $userUid, string $targetType, string $targetId): array
    {
        if ($userUid === null) {
            return [];
        }
        $types = self::COLLABORATION_TYPES;
        if ($targetType === 'article' && $this->canManageTarget($userUid, $targetType, $targetId)) {
            array_push($types, 'qa', 'help');
        }
        // 批注注入阅读页、所有读者可见，只有该句的管理者能加
        if ($targetType === 'sentence' && $this->canManageTarget($userUid, $targetType, $targetId)) {
            array_push($types, Discussion::TYPE_GLOSSARY, Discussion::TYPE_NOTE, Discussion::TYPE_COMMENTARY);
        }

        return $types;
    }

    /**
     * 当前用户对一条记录能做的动作。前端按它决定显示哪些菜单，后端按它放行。
     *
     * @return array{update: bool, delete: bool, close: bool, reply: bool}
     */
    private function abilities(Discussion $discussion, ?string $viewerUid): array
    {
        $none = ['update' => false, 'delete' => false, 'close' => false, 'reply' => false];
        if ($viewerUid === null || $discussion->trashed()) {
            return $none;
        }
        $isAuthor = $discussion->editor_uid === $viewerUid;
        $canManage = $isAuthor || $this->canManageTarget($viewerUid, $discussion->res_type, $discussion->res_id);
        $isTopic = $discussion->parent === null;
        if ($this->isAnnotation($discussion)) {
            // 批注没有开关；回复与评论线程一样，登录即可
            return [
                'update' => $canManage,
                'delete' => $canManage,
                'close' => false,
                'reply' => $isTopic && $this->canReply($viewerUid, $discussion),
            ];
        }

        return [
            'update' => $canManage,
            // 作者，或挂靠对象的管理者（审核别人的评论）。v2 只允许作者删，v3 放开：
            // 删除是软删，删错了可以恢复
            'delete' => $canManage,
            'close' => $isTopic && $canManage,
            'reply' => $isTopic && $discussion->status === 'active' && $this->canReply($viewerUid, $discussion),
        ];
    }

    /**
     * 能否回复：登录即可；文章上的 qa 只有文章编辑者能答（沿用 v2 `can_reply` 的口径）。
     */
    private function canReply(?string $userUid, Discussion $topic): bool
    {
        if ($userUid === null) {
            return false;
        }
        if ($topic->type === 'qa' && $topic->res_type === 'article') {
            return $this->canManageTarget($userUid, 'article', $topic->res_id);
        }

        return true;
    }

    /**
     * 能否管理挂靠的对象：句子与逐词看所在 channel 的编辑权，文章看文章的编辑权，
     * 其它对象（术语、任务）没有「管理者」这一说，只有作者本人。
     */
    private function canManageTarget(string $userUid, string $targetType, string $targetId): bool
    {
        $key = "{$userUid}|{$targetType}|{$targetId}";
        if (array_key_exists($key, $this->managers)) {
            return $this->managers[$key];
        }

        $result = match ($targetType) {
            'sentence', 'wbw' => ($channel = $this->channelOf($targetType, $targetId)) !== null
                && in_array($channel, $this->editableChannels($userUid), true),
            'article' => $this->canEditArticle($userUid, $targetId),
            default => false,
        };

        return $this->managers[$key] = $result;
    }

    /**
     * 句子 / 逐词所在的 channel。
     */
    private function channelOf(string $targetType, string $targetId): ?string
    {
        if ($targetType === 'sentence') {
            return Sentence::where('uid', $targetId)->value('channel_uid');
        }
        $blockUid = Wbw::where('uid', $targetId)->value('block_uid');

        return $blockUid ? WbwBlock::where('uid', $blockUid)->value('channel_uid') : null;
    }

    /**
     * @return array<int, string>
     */
    private function editableChannels(string $userUid): array
    {
        return $this->editableChannels[$userUid] ??= ChannelApi::getCanEditByUser($userUid);
    }

    /**
     * 文章编辑权：所有者、文章协作者（power ≥ 20），或文章所在的同主人文集的协作者。
     * 与 v2 `ArticleController::userCanEdit()` 同口径，v3 不去依赖 v2 的控制器。
     */
    private function canEditArticle(string $userUid, string $articleUid): bool
    {
        $article = Article::where('uid', $articleUid)->first(['uid', 'owner']);
        if (! $article) {
            return false;
        }
        if ($article->owner === $userUid || ShareApi::getResPower($userUid, $article->uid) >= 20) {
            return true;
        }
        $collections = ArticleCollectionOwner::whereIn(
            'uid',
            ArticleCollection::where('article_id', $article->uid)->select('collect_id')
        )->where('owner', $article->owner)->pluck('uid');

        return $collections->contains(fn ($uid) => ShareApi::getResPower($userUid, $uid) >= 20);
    }

    private function isBasic(string $userUid): bool
    {
        return in_array('basic', (array) ($this->users->byUuid($userUid)['roles'] ?? []), true);
    }

    private function targetExists(string $targetType, string $targetId): bool
    {
        return match ($targetType) {
            'sentence' => Sentence::where('uid', $targetId)->exists(),
            'wbw' => Wbw::where('uid', $targetId)->exists(),
            'article' => Article::where('uid', $targetId)->exists(),
            'term' => DhammaTerm::where('guid', $targetId)->exists(),
            'task' => Task::where('id', $targetId)->exists(),
            default => false,
        };
    }

    /**
     * 写操作之后重新读出完整资源（含墓碑），挂好关系与权限。
     *
     * 保留 wasRecentlyCreated：Laravel 的 ResourceResponse 据此把新建的响应设为 201，
     * 控制器不用手写状态码。
     */
    private function fresh(Discussion $discussion, string $userUid): Discussion
    {
        $fresh = Discussion::withTrashed()->with(self::relations())->findOrFail($discussion->id);
        $fresh->wasRecentlyCreated = $discussion->wasRecentlyCreated;
        if ($fresh->parent === null) {
            $fresh->loadCount('replies as reply_count');
        }
        $this->decorate(collect([$fresh]), $userUid);

        return $fresh;
    }

    /**
     * 给一批记录挂上权限与正文渲染结果。
     *
     * 只有挂在句子上的才渲染：正文里的模板要用句子所在的 channel 去解析。
     * channel 一次批量查出，不逐行查。
     *
     * @param  Collection<int, Discussion>  $items
     */
    private function decorate(Collection $items, ?string $viewerUid): void
    {
        $sentenceIds = $items->where('res_type', 'sentence')->pluck('res_id')->unique()->values();
        $channels = $sentenceIds->isEmpty()
            ? collect()
            : Sentence::whereIn('uid', $sentenceIds)->pluck('channel_uid', 'uid');

        foreach ($items as $item) {
            $item->abilities = $this->abilities($item, $viewerUid);
            $channel = $item->res_type === 'sentence' ? ($channels[$item->res_id] ?? null) : null;
            if ($channel && ! $item->trashed() && $item->content) {
                $item->rendered = [
                    'html' => MdRender::render($item->content, [$channel], null, 'read'),
                    'summary' => MdRender::render($item->content, [$channel], null, 'read', 'translation', 'markdown', 'text'),
                ];
            }
        }
    }

    /**
     * 推给 mq:discussion 工作进程（站内信 + webhook）。
     *
     * 载荷沿用 v2 `DiscussionResource` 的形状：那是工作进程读的消息契约
     * （读 editor.nickName、parent、res_type…），不是 v2 的 HTTP 契约。
     */
    private function notify(Discussion $discussion): void
    {
        Mq::publish('discussion', new MqDiscussionPayload($discussion));
    }
}
