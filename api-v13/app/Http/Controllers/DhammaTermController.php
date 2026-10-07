<?php

namespace App\Http\Controllers;

use App\Http\Api\ChannelApi;
use App\Http\Api\ShareApi;
use App\Http\Api\StudioApi;
use App\Http\Controllers\Concerns\ChecksChannelEditPower;
use App\Http\Resources\TermResource;
use App\Models\AiModel;
use App\Models\Channel;
use App\Models\DhammaTerm;
use App\Services\AuthService;
use App\Services\TermIndexService;
use App\Tools\Tools;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DhammaTermController extends Controller
{
    use ChecksChannelEditPower;

    public function __construct(
        private TermIndexService $termIndexService,
    ) {}

    /**
     * 列出术语（dhamma term）
     *
     * 按 view 指定的口径返回术语列表。studio、channel、user 三个口径需要登录；
     * studio 要求 name 指向的 studio 属于当前用户，否则 403；channel 要求当前用户是
     * channel owner 或协作者（ShareApi 权限值非 0），否则 403。
     * create-by-channel、show、hot-meaning 三个口径直接返回各自的数据结构，
     * 不走后面的搜索/排序/分页；其余口径统一返回 {rows, count}。
     * hot-meaning 结果会按 config('mint.cache.expire') 缓存在 term/hot_meaning 键下。
     * 未指定 view（或给了无法识别的值）时没有任何基础查询，会直接报错，调用方必须传 view。
     *
     * @queryParam view string required 查询口径。Enum: create-by-channel,studio,channel,show,user,word,tag,hot-meaning
     * @queryParam channel string view=create-by-channel 时必填，目标 channel 的 uid，
     *                            用于确定语言、同 studio 的 channel 列表与 studio 信息
     * @queryParam word string view=create-by-channel 时为要新建的术语词形；
     *                         view=word 时为逗号分隔的词或义项列表，按 word 或 meaning 命中
     * @queryParam name string view=studio 时必填，studio 名称，须与当前用户的 studio 一致
     * @queryParam id string view=channel 时为 channel id（uid）；view=show 时为术语主键 id
     * @queryParam tag string view=tag 时必填，逗号分隔的 tag 列表
     * @queryParam language string view=hot-meaning 时必填，统计该语言下每个词出现次数最多的义项
     * @queryParam search string 关键字，按 word、word_en 前缀匹配或 meaning 模糊匹配
     * @queryParam order string 排序字段。Default: updated_at
     * @queryParam dir string 排序方向。Enum: asc,desc Default: desc
     * @queryParam offset integer 分页偏移量。Default: 0
     * @queryParam limit integer 每页条数。Default: 1000
     * @queryParam mode string 渲染 note/summary 的模式，透传给 Markdown 渲染器。Default: read
     * @queryParam format string note 的渲染输出格式。Enum: react,html,text Default: react
     * @queryParam community_summary boolean 传入该参数时，自身没有 note 的术语会回落到社区
     *                                       channel 的解释作为 summary
     * @queryParam exp boolean 传入该参数时附带编辑者截至本条更新时间的累计经验值（秒）
     */
    public function index(Request $request)
    {
        $result = false;
        $indexCol = [
            'id',
            'guid',
            'word',
            'meaning',
            'other_meaning',
            'tag',
            'language',
            'channal',
            'owner',
            'editor_id',
            'editor_uid',
            'created_at',
            'updated_at',
        ];

        switch ($request->input('view')) {
            // FIXME: create-by-channel 口径完全不做登录/权限校验，任何人都能枚举任意
            // channel 所属 studio 的全部 channel 列表；应补上与 channel 口径一致的鉴权。
            case 'create-by-channel':
                // 新建术语时。根据术语所在channel 给出新建术语所需数据。如语言，备选意思等。
                // 获取channel信息
                $currChannel = Channel::where('uid', $request->input('channel'))->first();
                if (! $currChannel) {
                    return $this->error(__('auth.failed'));
                }
                // TODO 查询studio信息
                // 获取同studio的channel列表
                $studioChannels = Channel::where('owner_uid', $currChannel->owner_uid)
                    ->select(['name', 'uid'])
                    ->get();
                // 获取全网意思列表
                $meanings = DhammaTerm::where('word', $request->input('word'))
                    ->where('language', $currChannel->lang)
                    ->select(['meaning', 'other_meaning'])
                    ->get();
                $meaningList = [];
                foreach ($meanings as $key => $value) {
                    // code...
                    $meaning1 = [$value->meaning];

                    if (! empty($value->other_meaning)) {
                        $meaning2 = \explode(',', $value->other_meaning);
                        $meaning1 = array_merge($meaning1, $meaning2);
                    }
                    foreach ($meaning1 as $key => $value) {
                        // code...
                        if (isset($meaningList[$value])) {
                            $meaningList[$value]++;
                        } else {
                            $meaningList[$value] = 1;
                        }
                    }
                }
                $meaningCount = [];
                foreach ($meaningList as $key => $value) {
                    // code...
                    $meaningCount[] = ['meaning' => $key, 'count' => $value];
                }

                return $this->ok([
                    'word' => $request->input('word'),
                    'meaningCount' => $meaningCount,
                    'studioChannels' => $studioChannels,
                    'language' => $currChannel->lang,
                    'studio' => StudioApi::getById($currChannel->owner_uid),
                ]);
                break;
            case 'studio':
                // 获取 studio 内所有 term
                $user = AuthService::current($request);
                if (! $user) {
                    return $this->error(__('auth.failed'), [], 401);
                }
                // 判断当前用户是否有指定的studio的权限
                if ($user['user_uid'] !== StudioApi::getIdByName($request->input('name'))) {
                    return $this->error(__('auth.failed'), [], 403);
                }
                $table = DhammaTerm::select($indexCol)
                    ->where('owner', $user['user_uid']);
                break;
            case 'channel':
                // 获取 studio 内所有 term
                $user = AuthService::current($request);
                if (! $user) {
                    return $this->error(__('auth.failed'));
                }
                // 判断当前用户是否有指定的 channel 的权限
                // FIXME: find() 按主键查（其余地方 channel 都按 uid 查），且结果未判空就访问
                // ->owner_uid，id 不存在时直接 500；应改为 where('uid', ...)->first() 并判空返回 404。
                $channel = Channel::find($request->input('id'));
                if ($user['user_uid'] !== $channel->owner_uid) {
                    // 看是否为协作
                    $power = ShareApi::getResPower($user['user_uid'], $request->input('id'));
                    if ($power === 0) {
                        return $this->error(__('auth.failed'), [], 403);
                    }
                }
                $table = DhammaTerm::select($indexCol)
                    ->where('channal', $request->input('id'));
                break;
            case 'show':
                // FIXME: 不做登录校验且直接返回原始模型（未过 TermResource），会把 owner、
                // editor_id 等非公开字段暴露出去；应改用 TermResource 并补权限判定。
                return $this->ok(DhammaTerm::find($request->input('id')));
                break;
            case 'user':
                // code...
                $user = AuthService::current($request);
                if (! $user) {
                    return $this->error(__('auth.failed'));
                }
                $userUid = $user['user_uid'];
                // FIXME: 这里的 $search 是死代码，会被方法末尾同名变量重新赋值覆盖；应直接删除。
                $search = $request->input('search');
                $table = DhammaTerm::select($indexCol)
                    ->where('owner', $userUid);
                break;
            case 'word':
                // FIXME: whereIn / orWhereIn 未用闭包分组，后面追加的 search 条件会与这个 or
                // 错误结合导致越界结果；应包成 ->where(fn ($q) => $q->whereIn(...)->orWhereIn(...))。
                $table = DhammaTerm::select($indexCol)
                    ->whereIn('word', explode(',', $request->input('word')))
                    ->orWhereIn('meaning', explode(',', $request->input('word')));
                break;
            case 'tag':
                $table = DhammaTerm::select($indexCol)
                    ->whereIn('tag', explode(',', $request->input('tag')));
                break;
            case 'hot-meaning':
                $key = 'term/hot_meaning';
                $value = Cache::remember($key, config('mint.cache.expire'), function () use ($request) {
                    $hotMeaning = [];
                    $words = DhammaTerm::select('word')
                        ->where('language', $request->input('language'))
                        ->groupby('word')
                        ->get();

                    // FIXME: 典型 N+1，每个 word 单独发一次聚合查询；应改成一次
                    // group by word, meaning 的查询再在 PHP 里取每个词的 top 1。
                    foreach ($words as $key => $word) {
                        // code...
                        $result = DhammaTerm::select(DB::raw('count(*) as word_count, meaning'))
                            ->where('language', $request->input('language'))
                            ->where('word', $word['word'])
                            ->groupby('meaning')
                            ->orderby('word_count', 'desc')
                            ->first();
                        if ($result) {
                            $hotMeaning[] = [
                                'word' => $word['word'],
                                'meaning' => $result['meaning'],
                                'language' => $request->input('language'),
                                'owner' => '',
                            ];
                        }
                    }
                    // FIXME: 外层 Cache::remember 已负责写缓存，这里重复写入且 TTL（3600）
                    // 与 config('mint.cache.expire') 不一致；应删除这一行。
                    Cache::put($key, $hotMeaning, 3600);

                    return $hotMeaning;
                });

                return $this->ok(['rows' => $value, 'count' => count($value)]);
                break;
                // FIXME: 未传 view 或传了无法识别的 view 时 $table 未定义，会一路走到下面的
                // $table->count() 直接抛错；应在 default 里返回明确的参数错误。
            default:
                // code...
                break;
        }

        $search = $request->input('search');
        if (! empty($search)) {
            $table = $table->where(function ($query) use ($search) {
                $query->where('word', 'like', $search.'%')
                    ->orWhere('word_en', 'like', $search.'%')
                    ->orWhere('meaning', 'like', '%'.$search.'%');
            });
        }
        $count = $table->count();
        $table = $table->orderBy($request->input('order', 'updated_at'), $request->input('dir', 'desc'));
        $table = $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 1000));
        $result = $table->get();

        return $this->ok(['rows' => TermResource::collection($result), 'count' => $count]);
    }

    /**
     * 新建术语
     *
     * 需要登录。传了 channel 则建 channel 级术语，owner 取该 channel 所属 studio、
     * language 取该 channel 的语言（请求里的 language 被覆盖），并要求当前身份对该
     * channel 有编辑权（owner / 协作者 / channel access token），否则 403。
     * 不传 channel 则建 studio 级术语，只允许 studio 本人创建（studioId 必须等于当前
     * 用户 uid，否则 403）；AI 模型身份一律禁止（403），因为 access token 只能代持
     * channel 级权限。
     * 唯一性：channel 级按 channel+word+tag 查重，studio 级按 owner+word+tag+language
     * 且 channal 为空查重；重复时返回 'word existed'，HTTP 状态仍是 200。
     * 写入成功后清除该词的 term 缓存并重建 OpenSearch 索引（索引失败只记日志）。
     *
     * @bodyParam word string required 术语词形，同时用于生成 word_en
     * @bodyParam meaning string required 主要义项
     * @bodyParam other_meaning string 其他义项，逗号分隔
     * @bodyParam note string 术语解释，Markdown
     * @bodyParam tag string 标签，参与查重
     * @bodyParam channel string channel uid。给出则建 channel 级术语，否则建 studio 级术语
     * @bodyParam language string 语言。仅 studio 级术语生效，channel 级会被 channel 语言覆盖
     * @bodyParam studioId string studio 的 uuid，不传 channel 时必填（或改用 studioName）
     * @bodyParam studioName string studio 名称，未给 studioId 时用它换取 studioId
     * @bodyParam access_token string channel access token，供 AI 模型等非 owner 身份验证 channel 编辑权
     */
    public function store(Request $request)
    {
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'));
        }
        $validated = $request->validate([
            'word' => 'required',
            'meaning' => 'required',
        ]);

        /**
         * 查询重复的
         * 一个channel下面word+tag+language 唯一
         */
        $table = DhammaTerm::where('word', $request->input('word'))
            ->where('tag', $request->input('tag'));
        if (! empty($request->input('channel'))) {
            // channel 内的唯一性只看 channel。此前这里还按 owner 过滤，而
            // owner 取的是当前身份——AI 模型的 uid 与落库的 owner（channel
            // 所属 studio）永远不等，查重必然落空，同一个词会被反复插入。
            $isDoesntExist = $table->where('channal', $request->input('channel'))
                ->doesntExist();
        } else {
            $isDoesntExist = $table->where('owner', $user['user_uid'])
                ->whereNull('channal')->where('language', $request->input('language'))
                ->doesntExist();
        }

        if ($isDoesntExist) {
            // 没有重复的 插入数据
            $term = new DhammaTerm;
            $term->id = app('snowflake')->id();
            $term->guid = Str::uuid();
            $term->word = $request->input('word');
            $term->word_en = Tools::getWordEn($request->input('word'));
            $term->meaning = $request->input('meaning');
            $term->other_meaning = $request->input('other_meaning');
            $term->note = $request->input('note');
            $term->tag = $request->input('tag');
            $term->channal = $request->input('channel');
            $term->language = $request->input('language');
            if (! empty($request->input('channel'))) {
                $channelInfo = ChannelApi::getById($request->input('channel'));
                if (! $channelInfo) {
                    return $this->error('channel id failed');
                } else {
                    // 查看有没有channel权限。术语没有 book 概念，access token 的
                    // book 一律按 0（不限）判定。
                    if (! $this->userCanEditChannel(
                        $user['user_uid'],
                        $request->input('channel'),
                        0,
                        $request->input('access_token')
                    )) {
                        return $this->error(__('auth.failed'), [], 403);
                    }
                    $term->owner = $channelInfo['studio_id'];
                    $term->language = $channelInfo['lang'];
                }
            } else {
                // AI 模型只能在 channel 里建术语。它的权限全部由人类签出的
                // channel access token 代持，而 access token 是 channel 级的，
                // 代持不了 studio 权限——没有 channel 就没有任何可核验的授权。
                if ($this->isAiModel($user['user_uid'])) {
                    return $this->error('ai model must specify a channel', [], 403);
                }
                if ($request->has('studioId')) {
                    $studioId = $request->input('studioId');
                } elseif ($request->has('studioName')) {
                    $studioId = StudioApi::getIdByName($request->input('studioName'));
                }
                if (! isset($studioId) || ! Str::isUuid($studioId)) {
                    return $this->error('not valid studioId');
                }
                // studio 级术语（不属于任何 channel）只能由 studio 本人建。
                // 此前这里不校验归属，任何登录用户都能往别人 studio 名下写。
                // access token 是 channel 级的，代持不了 studio 权限，所以
                // AI 模型建 studio 级术语必然走到这里被拒——这是有意的。
                if ($studioId !== $user['user_uid']) {
                    return $this->error(__('auth.failed'), [], 403);
                }
                $term->owner = $studioId;
            }
            $term->editor_id = $this->editorId($user);
            $term->editor_uid = $user['user_uid'];
            $term->create_time = time() * 1000;
            $term->modify_time = time() * 1000;
            $term->save();
            // 删除cache
            $this->deleteCache($term);
            // 重建 OpenSearch 索引
            $this->reindex($term);

            return $this->ok(new TermResource($term));
        } else {
            return $this->error('word existed', [], 200);
        }
    }

    /**
     * editor_id 存的是人类用户的自增 sn，模型没有。模型 token 里的 id 恒为 0，
     * 而 0 同时也是「缺省/未知」的值，落库后分不清是模型写的还是数据有问题，
     * 故模型一律记 -1；模型的真实身份看 editor_uid。
     *
     * @param  array<string, mixed>  $user
     */
    private function editorId(array $user): int
    {
        return $this->isAiModel($user['user_uid']) ? -1 : (int) $user['user_id'];
    }

    /**
     * 当前身份是不是 AI 模型。模型 token 的 user_id 恒为 0，但人类的旧 cookie
     * 鉴权也可能给出奇怪的值，故直接查表判定，不靠 id。
     */
    private function isAiModel(string $userUid): bool
    {
        return AiModel::where('uid', $userUid)->exists();
    }

    private function deleteCache($term)
    {
        if (empty($term->channal)) {
            // 通用 查询studio所有channel
            $channels = Channel::where('owner_uid', $term->owner)->select('uid')->get();
            foreach ($channels as $channel) {
                // FIXME: $channel 是 Eloquent 模型对象，插值后得到的是 JSON 而非 uid，
                // 缓存键与写入时的键对不上，studio 级术语的缓存实际没被清掉；应用 $channel->uid。
                Cache::forget("/term/{$channel}/{$term->word}");
            }
        } else {
            Cache::forget("/term/{$term->channal}/{$term->word}");
        }
    }

    /**
     * 术语新建/修改后重建 OpenSearch 索引。
     *
     * 索引是检索/维基展示的副作用数据，重建失败不应阻断本次写入，
     * 因此只记录日志、不向上抛异常。
     */
    private function reindex(DhammaTerm $term): void
    {
        try {
            $this->termIndexService->index($term->guid);
        } catch (\Throwable $e) {
            Log::error('Failed to index term after write', [
                'guid' => $term->guid,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 查询单条术语
     *
     * 按 guid（不是自增 id）查找，公开接口，不需要登录。查不到返回错误 '没有查询到数据'。
     *
     * @urlParam id string required 术语的 guid
     *
     * @queryParam channel string 渲染 note 时使用的 channel 列表，多个用下划线分隔；
     *                            不传则按术语自身的 channel 或对应语言的社区 channel 渲染
     * @queryParam mode string 渲染模式。Default: read
     * @queryParam format string note 的渲染输出格式。Enum: react,html,text Default: react
     * @queryParam community_summary boolean 传入该参数时，没有 note 的术语回落到社区解释作为 summary
     * @queryParam exp boolean 传入该参数时附带编辑者累计经验值（秒）
     */
    public function show(Request $request, $id)
    {
        $result = DhammaTerm::where('guid', $id)->first();
        if ($result) {
            return $this->ok(new TermResource($result));
        } else {
            return $this->error('没有查询到数据');
        }
    }

    /**
     * 修改术语
     *
     * 需要登录（未登录 401），按主键 id 查找，不存在返回 '404'。
     * 权限：studio 级术语（channal 为空）只有 owner 本人能改，AI 模型一律 403；
     * channel 级术语要求对该 channel 有编辑权（owner / 协作者 / access token），否则 403。
     * 增量更新——只有请求里出现的字段才会被写入，未提交的字段保持原值；
     * 提交 word 时会同步重算 word_en。create_time 不变，只刷新 modify_time。
     * 保存后清除该词的 term 缓存并重建 OpenSearch 索引（索引失败只记日志）。
     *
     * @urlParam id string required 术语主键 id
     *
     * @bodyParam word string 术语词形，提交时同步重算 word_en
     * @bodyParam meaning string 主要义项
     * @bodyParam other_meaning string 其他义项，逗号分隔
     * @bodyParam note string 术语解释，Markdown
     * @bodyParam tag string 标签
     * @bodyParam language string 语言
     * @bodyParam access_token string channel access token，供非 owner 身份验证 channel 编辑权
     */
    public function update(Request $request, string $id)
    {
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), [], 401);
        }
        $dhammaTerm = DhammaTerm::find($id);
        if (! $dhammaTerm) {
            return $this->error('404');
        }

        if (empty($dhammaTerm->channal)) {
            // studio 级术语（不属于任何 channel）只有 owner 本人能改：
            // access token 是 channel 级的，代持不了 studio 权限。
            if ($this->isAiModel($user['user_uid'])) {
                return $this->error('ai model cannot edit a term outside a channel', [], 403);
            }
            if ($user['user_uid'] !== $dhammaTerm->owner) {
                return $this->error(__('auth.failed'), [], 403);
            }
        } else {
            // 查看有没有channel权限（owner / 协作者 / access token）
            if (! $this->userCanEditChannel(
                $user['user_uid'],
                $dhammaTerm->channal,
                0,
                $request->input('access_token')
            )) {
                return $this->error(__('auth.failed'), [], 403);
            }
        }

        // 增量更新：只改提交上来的字段。此前这里无条件赋值，客户端漏提一个
        // 字段就会把库里的 note/tag 等清成 null。
        if ($request->has('word')) {
            $dhammaTerm->word = $request->input('word');
            $dhammaTerm->word_en = Tools::getWordEn($request->input('word'));
        }
        foreach (['meaning', 'other_meaning', 'note', 'tag', 'language'] as $field) {
            if ($request->has($field)) {
                $dhammaTerm->$field = $request->input($field);
            }
        }
        $dhammaTerm->editor_id = $this->editorId($user);
        $dhammaTerm->editor_uid = $user['user_uid'];
        // create_time 是创建时刻，改动时不该被刷新
        $dhammaTerm->modify_time = time() * 1000;
        $dhammaTerm->save();
        // 删除cache
        $this->deleteCache($dhammaTerm);
        // 重建 OpenSearch 索引
        $this->reindex($dhammaTerm);

        return $this->ok(new TermResource($dhammaTerm));
    }

    /**
     * 批量删除术语
     *
     * 需要登录。一次可以删除多条，返回实际删除的条数；无权限的条目被跳过而不报错。
     * 传了 uuid 参数时，id 按数组处理：owner 本人可删，非 owner 且术语属于某个 channel 时
     * 需要该 channel 的协作权限达到 20（编辑）才可删，studio 级术语非 owner 一律跳过。
     * 未传 uuid 时，id 按 JSON 字符串数组处理，且只能删除 owner 是当前用户的术语。
     * 每删一条都会清除该词对应的 term 缓存。
     *
     * @urlParam dhammaTerm string required 路由模型绑定占位的术语 id，实际删除对象由 id 参数决定
     *
     * @queryParam uuid string 存在该参数即表示 id 以数组形式提交，并启用 channel 协作者删除权限判定
     * @queryParam id array required 要删除的术语 id 列表。带 uuid 时为数组，否则为 JSON 数组字符串
     */
    public function destroy(DhammaTerm $dhammaTerm, Request $request)
    {
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'));
        }
        $count = 0;
        // FIXME: 判断用的是 uuid 参数、实际读的却是 id 参数，uuid 纯当开关用，语义混乱
        // 极易误用；应改为按 id 的实际类型分支，或统一成一个明确的参数格式。
        if ($request->has('uuid')) {
            // 查看是否有删除权限
            // FIXME: 未校验 input('id') 是数组，客户端传字符串会导致 foreach 直接 fatal；
            // 应先 validate(['id' => 'required|array'])。
            foreach ($request->input('id') as $key => $uuid) {
                $term = DhammaTerm::find($uuid);
                if (! $term) {
                    continue;
                }
                if ($term->owner !== $user['user_uid']) {
                    if (! empty($term->channal)) {
                        // 看是否为协作
                        $power = ShareApi::getResPower($user['user_uid'], $term->channal);
                        if ($power < 20) {
                            continue;
                        }
                    } else {
                        continue;
                    }
                }
                $count += $term->delete();
                // 删除cache
                $this->deleteCache($term);
            }
        } else {
            // FIXME: json_decode 结果未判空，非法 JSON 时得到 null 会让下面的 foreach 报错；
            // 应判断解析结果是数组，否则返回参数错误。
            $arrId = json_decode($request->input('id'), true);
            foreach ($arrId as $key => $id) {
                // code...
                $term = DhammaTerm::where('id', $id)
                    ->where('owner', $user['user_uid'])
                    ->first();
                if (! $term) {
                    continue;
                }
                // 先取到模型再删：此前这里把 query builder 传给 deleteCache，
                // 拿不到 word/channal，缓存根本没被清掉。
                $result = $term->delete();
                if ($result) {
                    $this->deleteCache($term);
                    $count++;
                }
            }
        }

        return $this->ok($count);
    }
}
