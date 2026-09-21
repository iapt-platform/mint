<?php

namespace App\Http\Controllers;

use App\Http\Api\ChannelApi;
use App\Http\Api\CourseApi;
use App\Http\Api\MdRender;
use App\Http\Api\Mq;
use App\Http\Api\UserApi;
use App\Http\Resources\DiscussionResource;
use App\Models\Channel;
use App\Models\Discussion;
use App\Models\PaliSentence;
use App\Models\Sentence;
use App\Models\Wbw;
use App\Models\WbwBlock;
use App\Services\AuthService;
use Illuminate\Http\Request;

class DiscussionController extends Controller
{
    /**
     * 列出讨论 / 批注
     *
     * 按 view 指定的口径返回 discussion 列表，支持状态过滤、分页与排序。
     * 权限：未登录用户请求 type=discussion 时直接返回空结果；basic 角色用户在自己无编辑权限的
     * channel 上只能看到自己发表的 discussion；view=topic-by-user 必须登录，否则返回 403。
     * 返回 rows、count（当前口径总数）、active/close（顶级节点的活跃与关闭数）、
     * can_create/can_reply（当前用户能否发起与回复，按 type 与 res_type 计算）。
     *
     * @queryParam view string required 查询口径。question=按资源查顶级讨论；question-by-topic=按某条讨论所属资源查同资源讨论；answer=查某讨论的回复；res_id=某资源的顶级节点及其直接子节点；topic-by-user=当前用户发表的全部顶级讨论；all=全部顶级讨论。Enum: question,question-by-topic,answer,res_id,topic-by-user,all
     * @queryParam id string required 资源 uid（question/res_id）、讨论 id（question-by-topic/answer）
     * @queryParam type string 讨论类型。Enum: discussion,qa,help,note Default: discussion
     * @queryParam res_type string 资源类型，影响权限判断与学员提问聚合。Enum: sentence,wbw,article
     * @queryParam status string 状态过滤；res_id 与 topic-by-user 口径支持逗号分隔多选。Enum: active,close Default: active
     * @queryParam course string 课程 uid，配合 show_student 聚合学员提问
     * @queryParam show_student string 是否把学员 channel 上的同位置提问一并返回。Enum: true,false
     * @queryParam order string 排序字段。Default: created_at
     * @queryParam dir string 排序方向。Enum: asc,desc Default: desc
     * @queryParam offset integer 跳过的记录数。Default: 0
     * @queryParam limit integer 返回条数上限。Default: 100
     */
    public function index(Request $request)
    {
        //
        $user = AuthService::current($request);
        if ($user) {
            $userInfo = UserApi::getByUuid($user['user_uid']);
        }
        switch ($request->input('view')) {
            case 'question-by-topic':
                // FIXME: $topic 拿到的是 Builder 而不是模型，->first() 的结果被丢弃，
                // 因此下面的 if (! $topic) 永远为 false，$topic->res_id 走的是 Builder 魔术属性。
                // 应写成 $topic = Discussion::where('id', ...)->where('status', ...)->select('res_id')->first();
                $topic = Discussion::where('id', $request->input('id'));
                $topic->where('status', $request->input('status', 'active'))
                    ->select('res_id')->first();
                if (! $topic) {
                    return $this->error('无效的id');
                }
                $table = Discussion::where('res_id', $topic->res_id);
                $activeNumber = Discussion::where('res_id', $topic->res_id)
                    ->where('status', 'active')->count();
                $closeNumber = Discussion::where('res_id', $topic->res_id)
                    ->where('status', 'close')->count();
                $table->where('status', $request->input('status', 'active'))
                    ->where('parent', null);
                break;
            case 'question':
                /**
                 * 禁止：
                 * 未注册用户看到任何人发表的discussion
                 * basic用户看到别人在别人channel发表的discussion
                 */
                if (! $user && $request->input('type') === 'discussion') {
                    return $this->ok([
                        'rows' => [],
                        'count' => 0,
                        'active' => 0,
                        'close' => 0,
                        'can_create' => false,
                        'can_reply' => false,
                    ]);
                }
                $resType = $request->input('res_type');
                if ($user) {
                    switch ($resType) {
                        case 'sentence':
                            // code...
                            break;
                        case 'wbw':
                            $block_uid = Wbw::where('uid', $request->input('id'))->value('block_uid');
                            if ($block_uid) {
                                $channelId = WbwBlock::where('uid', $block_uid)->value('channel_uid');
                                if ($channelId) {
                                    $canEdit = ChannelApi::userCanEdit($user['user_uid'], $channelId);
                                }
                            }
                            break;
                        default:
                            // code...
                            break;
                    }
                }

                $resId = [$request->input('id')];
                if (! empty($request->input('course'))) {
                    //
                    /**
                     * 如果res id 是答案，获取学员提问
                     * 如果是学员
                     */
                    // 获取学员提问
                    // 获取学员channel
                    if ($request->input('show_student') === 'true') {
                        $channelsId = CourseApi::getStudentChannels($request->input('course'));
                        switch ($resType) {
                            case 'wbw':
                                // 获取答案单词编号
                                $wbwWord = Wbw::where('uid', $request->input('id'))
                                    ->first();
                                $wbwId = WbwSentenceController::getWbwIdByChannels(
                                    $channelsId,
                                    $wbwWord->book_id,
                                    $wbwWord->paragraph,
                                    $wbwWord->wid
                                );
                                $resId = array_merge($resId, $wbwId);
                                break;
                            case 'sentence':
                                break;
                        }
                    }
                }
                $table = Discussion::whereIn('res_id', $resId)
                    ->where('type', $request->input('type', 'discussion'))
                    ->where('status', $request->input('status', 'active'))
                    ->where('parent', null);
                if ($request->input('type') === 'discussion') {
                    if (
                        isset($userInfo) &&
                        isset($userInfo['roles']) &&
                        in_array('basic', $userInfo['roles'])
                    ) {
                        if (isset($canEdit) && $canEdit === true) {
                        } else {
                            $table = $table->where('editor_uid', $userInfo['id']);
                        }
                    }
                }
                $activeNumber = Discussion::whereIn('res_id', $resId)
                    ->where('parent', null)
                    ->where('type', $request->input('type', 'discussion'))
                    ->where('status', 'active')->count();
                $closeNumber = Discussion::whereIn('res_id', $resId)
                    ->where('parent', null)
                    ->where('type', $request->input('type', 'discussion'))
                    ->where('status', 'close')->count();
                break;
            case 'answer':
                $table = Discussion::where('parent', $request->input('id'));
                $activeNumber = Discussion::where('parent', $request->input('id'))
                    ->where('status', 'active')->count();
                $closeNumber = Discussion::where('parent', $request->input('id'))
                    ->where('status', 'close')->count();
                break;
            case 'res_id':
                /**
                 * 先获取顶级节点
                 * 需要确定用户身份，manager查看全部topic 普通用户只显示自己提交的topic
                 */
                $roots = Discussion::where('res_id', $request->input('id'))
                    ->where('type', $request->input('type', 'discussion'))
                    ->whereIn('status', explode(',', $request->input('status', 'active')))
                    ->where('parent', null)
                    ->select('id')
                    ->get();

                $table = Discussion::where(function ($query) use ($roots) {
                    $query->whereIn('id', $roots)
                        ->orWhereIn('parent', $roots);
                });
                $activeNumber = Discussion::where('res_id', $request->input('id'))
                    ->where('type', $request->input('type', 'discussion'))
                    ->where('status', 'active')->count();
                $closeNumber = Discussion::where('res_id', $request->input('id'))
                    ->where('type', $request->input('type', 'discussion'))
                    ->where('status', 'close')->count();
                break;
            case 'topic-by-user':
                /**
                 * 某用户发表的全部topic
                 */
                if (! $user) {
                    return $this->error('', 403, 403);
                }
                $table = Discussion::where('editor_uid', $user['user_uid'])
                    ->where('type', $request->input('type', 'discussion'))
                    ->whereIn('status', explode(',', $request->input('status', 'active')))
                    ->where('parent', null);
                $activeNumber = Discussion::where('editor_uid', $user['user_uid'])
                    ->where('parent', null)
                    ->where('type', $request->input('type', 'discussion'))
                    ->where('status', 'active')->count();
                $closeNumber = Discussion::where('editor_uid', $user['user_uid'])
                    ->where('parent', null)
                    ->where('type', $request->input('type', 'discussion'))
                    ->where('status', 'close')->count();
                break;
            case 'all':
                $table = Discussion::where('parent', null);
                $activeNumber = Discussion::where('parent', null)
                    ->where('status', 'active')->count();
                $closeNumber = Discussion::where('parent', null)
                    ->where('status', 'close')->count();
                break;
        }
        // FIXME: $search 从未定义，这段标题搜索是永不执行的死代码。
        // 应改为从 $request->input('search') 取值，或直接删除。
        if (! empty($search)) {
            $table = $table->where('title', 'like', $search.'%');
        }
        // FIXME: view 不在枚举内（含缺省）时 $table 从未被赋值，这里会 fatal error。
        // 建议 switch 补 default 分支直接返回参数错误。
        $count = $table->count();

        $table = $table->orderBy($request->input('order', 'created_at'), $request->input('dir', 'desc'));
        $table = $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 100));

        $result = $table->get();

        $can_create = false;
        $can_reply = false;
        $user = AuthService::current($request);

        switch ($request->input('type', 'discussion')) {
            case 'qa':
                switch ($request->input('res_type')) {
                    case 'article':
                        if ($user && ArticleController::userCanEditId($user['user_uid'], $request->input('id'))) {
                            $can_create = true;
                            $can_reply = true;
                        }
                        break;
                }
                break;
            case 'help':
                switch ($request->input('res_type')) {
                    case 'article':
                        if ($user) {
                            $can_reply = true;
                            if (ArticleController::userCanEditId($user['user_uid'], $request->input('id'))) {
                                $can_create = true;
                            }
                        }
                        break;
                }
                break;
            case 'discussion':
                if ($user) {
                    $can_create = true;
                    $can_reply = true;
                }
                break;
        }

        return $this->ok([
            'rows' => DiscussionResource::collection($result),
            'count' => $count,
            'active' => $activeNumber,
            'close' => $closeNumber,
            'can_create' => $can_create,
            'can_reply' => $can_reply,
        ]);
    }

    /**
     * 按句子批量查询讨论概览
     *
     * 对传入的每个句子定位（book/paragraph/word_start/word_end/channel_id）先查到 sentence，
     * 再取该句上全部顶级讨论（title、children_count、editor_uid，按创建时间倒序）。
     * 没有讨论的句子会被跳过，不做登录与权限校验。返回 rows 与 count。
     *
     * @bodyParam data array required 句子定位数组
     * @bodyParam data[].book integer required 卷号
     * @bodyParam data[].paragraph integer required 段号
     * @bodyParam data[].word_start integer required 句子起始词序号
     * @bodyParam data[].word_end integer required 句子结束词序号
     * @bodyParam data[].channel_id string required 译文集 channel uid
     */
    public function discussion_tree(Request $request)
    {
        $output = [];
        $sentences = $request->input('data');
        foreach ($sentences as $key => $sentence) {
            // 先查句子信息
            $sentInfo = Sentence::where('book_id', $sentence['book'])
                ->where('paragraph', $sentence['paragraph'])
                ->where('word_start', $sentence['word_start'])
                ->where('word_end', $sentence['word_end'])
                ->where('channel_uid', $sentence['channel_id'])
                ->first();
            if ($sentInfo) {
                $sentPr = Discussion::where('res_id', $sentInfo['uid'])
                    ->whereNull('parent')
                    ->select('title', 'children_count', 'editor_uid')
                    ->orderBy('created_at', 'desc')->get();
                if (count($sentPr) > 0) {
                    $output[] = [
                        'sentence' => [
                            'book' => $sentInfo->book_id,
                            'paragraph' => $sentInfo->paragraph,
                            'word_start' => $sentInfo->word_start,
                            'word_end' => $sentInfo->word_end,
                            'channel_id' => $sentInfo->channel_uid,
                            'content' => $sentInfo->content,
                            'pr_count' => count($sentPr),
                        ],
                        'pr' => $sentPr,
                    ];
                }
            }
        }

        return $this->ok(['rows' => $output, 'count' => count($output)]);
    }

    /**
     * 发表讨论 / 批注或回复
     *
     * 必须登录，未登录返回 401。传了 parent 即为回复：res_id 与 res_type 继承自父节点，
     * 无需再传，父节点不存在返回 no record，保存后父节点 children_count 自增；
     * 不传 parent 为顶级节点，此时 res_id、res_type、title 均为必填。
     * 锚点字段（pos_start/pos_end/quote_exact/quote_prefix/quote_suffix）两种情况都可选。
     * 默认会向消息队列 discussion 推送通知，可用 notification=false 关闭。
     *
     * @bodyParam parent string 父讨论 id，传入表示这是一条回复
     * @bodyParam res_id string required 关联资源 uid（有 parent 时忽略并继承父节点）
     * @bodyParam res_type string required 关联资源类型（有 parent 时忽略并继承父节点）。Enum: sentence,wbw,article
     * @bodyParam title string required 标题（有 parent 时非必填）
     * @bodyParam content string 正文内容
     * @bodyParam content_type string 正文格式。Default: markdown
     * @bodyParam type string 讨论类型。Enum: discussion,qa,help,note Default: discussion
     * @bodyParam tpl_id string 模板 id
     * @bodyParam pos_start integer 锚点在资源文本中的起始偏移，非负整数
     * @bodyParam pos_end integer 锚点在资源文本中的结束偏移，非负整数
     * @bodyParam quote_exact string 锚定的原文片段
     * @bodyParam quote_prefix string 锚定片段前的上下文，用于重新定位
     * @bodyParam quote_suffix string 锚定片段后的上下文，用于重新定位
     * @bodyParam notification boolean 是否推送消息队列通知。Default: true
     */
    public function store(Request $request)
    {
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), [401], 401);
        }
        //
        // validate
        // read more on validation at http://laravel.com/docs/validation

        $annotationRules = [
            'pos_start' => 'nullable|integer|min:0',
            'pos_end' => 'nullable|integer|min:0',
            'quote_exact' => 'nullable|string',
            'quote_prefix' => 'nullable|string',
            'quote_suffix' => 'nullable|string',
        ];
        if ($request->has('parent')) {
            $rules = $annotationRules;
            $parentInfo = Discussion::find($request->input('parent'));
            if (! $parentInfo) {
                return $this->error('no record');
            }
        } else {
            $rules = array_merge([
                'res_id' => 'required',
                'res_type' => 'required',
                'title' => 'required',
            ], $annotationRules);
        }

        $validated = $request->validate($rules);

        $discussion = new Discussion;
        if ($request->has('parent')) {
            $discussion->res_id = $parentInfo->res_id;
            $discussion->res_type = $parentInfo->res_type;
        } else {
            $discussion->res_id = $request->input('res_id');
            $discussion->res_type = $request->input('res_type');
        }
        $discussion->type = $request->input('type', 'discussion');
        $discussion->tpl_id = $request->input('tpl_id');
        $discussion->title = $request->input('title', null);
        $discussion->content = $request->input('content', null);
        $discussion->content_type = $request->input('content_type', 'markdown');
        $discussion->parent = $request->input('parent', null);
        $discussion->editor_uid = $user['user_uid'];
        $discussion->pos_start = $request->input('pos_start');
        $discussion->pos_end = $request->input('pos_end');
        $discussion->quote_exact = $request->input('quote_exact');
        $discussion->quote_prefix = $request->input('quote_prefix');
        $discussion->quote_suffix = $request->input('quote_suffix');
        $discussion->save();
        // 更新parent children_count
        if ($request->has('parent')) {
            $parentInfo->increment('children_count', 1);
            $parentInfo->save();
        }
        if ($request->input('notification', true)) {
            Mq::publish('discussion', new DiscussionResource($discussion));
        }

        return $this->ok(new DiscussionResource($discussion));
    }

    /**
     * 查询单条讨论
     *
     * 按路径参数做路由模型绑定并直接返回，不做登录与权限校验。
     *
     * @urlParam discussion string required 讨论 id
     */
    public function show(Discussion $discussion)
    {
        //
        return $this->ok(new DiscussionResource($discussion));
    }

    /**
     * 获取 discussion 锚点的数据
     *
     * 以句子为最小单位返回锚点上下文：res_type=wbw 时，由单词定位到所属 wbw block 与巴利句子，
     * 再用该 block 的 channel 渲染出句子内容的 markdown。其他 res_type 暂返回空字符串。
     * 不做登录与权限校验；wbw、wbw block 或句子任一查不到时分别返回 no wbw data / no wbwBlock data / no sent data。
     *
     * @urlParam id string required 讨论 id
     */
    public function anchor($id)
    {
        //
        $discussion = Discussion::find($id);
        $content = '';
        switch ($discussion->res_type) {
            case 'wbw':
                // 从逐词解析表获取逐词解析数据
                $wbw = Wbw::where('uid', $discussion->res_id)->first();
                if (! $wbw) {
                    return $this->error('no wbw data');
                }
                $wbwBlock = WbwBlock::where('uid', $wbw->block_uid)->first();
                if (! $wbwBlock) {
                    return $this->error('no wbwBlock data');
                }
                $sent = PaliSentence::where('book', $wbw->book_id)
                    ->where('paragraph', $wbw->paragraph)
                    ->where('word_begin', '<=', $wbw->wid)
                    ->where('word_end', '>=', $wbw->wid)
                    ->first();
                if (! $sent) {
                    return $this->error('no sent data');
                }
                $sentId = "{$sent['book']}-{$sent['paragraph']}-{$sent['word_begin']}-{$sent['word_end']}";
                $channel = $wbwBlock->channel_uid;
                $content = MdRender::render('{{'.$sentId.'}}', [$channel]);
                break;

            default:
                // code...
                break;
        }

        return $this->ok($content);
    }

    /**
     * 修改讨论 / 批注
     *
     * 必须登录，未登录返回 403。作者本人可改；非作者则要求是资源所属 channel 的可编辑者
     * （res_type=sentence 取 sentence.channel_uid，res_type=wbw 经 wbw block 取 channel_uid），
     * 两者都不满足返回 403。
     * title、content、status 按请求覆盖，未提交时分别回落为 null、null、active；
     * type 与五个锚点字段为增量更新，只改请求里出现的字段，未提交的原样保留。
     *
     * @urlParam discussion string required 讨论 id
     *
     * @bodyParam title string 标题，未提交会被清空
     * @bodyParam content string 正文内容，未提交会被清空
     * @bodyParam status string 状态，未提交会重置为 active。Enum: active,close Default: active
     * @bodyParam type string 讨论类型，仅在提交时更新。Enum: discussion,qa,help,note
     * @bodyParam pos_start integer 锚点起始偏移，仅在提交时更新
     * @bodyParam pos_end integer 锚点结束偏移，仅在提交时更新
     * @bodyParam quote_exact string 锚定的原文片段，仅在提交时更新
     * @bodyParam quote_prefix string 锚定片段前的上下文，仅在提交时更新
     * @bodyParam quote_suffix string 锚定片段后的上下文，仅在提交时更新
     */
    public function update(Request $request, Discussion $discussion)
    {
        //
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), [403], 403);
        }
        //
        $isManager = false;
        $isResManager = false;
        if ($discussion->editor_uid === $user['user_uid']) {
            $isManager = true;
        } else {
            // 查看是否是资源拥有者
            if ($discussion->res_type === 'sentence') {
                $res = Sentence::find($discussion->res_id);
                if ($res) {
                    $channelId = $res->channel_uid;
                }
            } elseif ($discussion->res_type === 'wbw') {
                $res = Wbw::where('uid', $discussion->res_id)->first();
                if ($res) {
                    $block = WbwBlock::where('uid', $res->block_uid)->first();
                    if ($block) {
                        $channelId = $block->channel_uid;
                    }
                }
            }
            if (isset($channelId)) {
                $channel = Channel::find($channelId);
                if ($channel) {
                    $isResManager = ChannelApi::userCanEdit($user['user_uid'], $channelId);
                }
            }
        }
        if (! $isManager && ! $isResManager) {
            return $this->error(__('auth.failed'), [403], 403);
        }

        // FIXME: 未提交 title/content/status 时会被覆盖成 null/active，与下方锚点字段的增量更新策略不一致，
        // 前端只想改单个字段时容易误清内容。建议统一改为 $request->has() 判断后再赋值。
        $discussion->title = $request->input('title', null);
        $discussion->content = $request->input('content', null);
        $discussion->status = $request->input('status', 'active');
        if ($request->has('type')) {
            $discussion->type = $request->input('type');
        }
        // 注释锚点字段：增量更新，只改请求里出现的字段，好让前端能显式清空；
        // 未提交的字段必须原样保留。
        foreach (['pos_start', 'pos_end', 'quote_exact', 'quote_prefix', 'quote_suffix'] as $field) {
            if ($request->has($field)) {
                $discussion->{$field} = $request->input($field);
            }
        }
        // $discussion->editor_uid = $user['user_uid'];
        $discussion->save();

        return $this->ok(new DiscussionResource($discussion));
    }

    /**
     * 删除讨论 / 批注
     *
     * 必须登录，未登录返回 401。目前只允许作者本人删除，其他人返回 403。
     * 不会级联删除子回复，也不会回退父节点的 children_count。
     *
     * @urlParam discussion string required 讨论 id
     */
    public function destroy(Request $request, Discussion $discussion)
    {
        //
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), [401], 401);
        }
        // TODO 其他有权限的人也可以删除
        if ($discussion->editor_uid !== $user['user_uid']) {
            return $this->error(__('auth.failed'), [403], 403);
        }
        // FIXME: 删除时既不级联删除子回复（产生孤儿节点），也不回退父节点的 children_count，计数会漂移。
        // 建议在事务里一并删除子节点并 decrement 父节点计数。
        $delete = $discussion->delete();

        return $this->ok($delete);
    }
}
