<?php

namespace App\Http\Controllers;

use App\Http\Api\ChannelApi;
use App\Http\Api\PaliTextApi;
use App\Http\Api\ShareApi;
use App\Http\Api\StudioApi;
use App\Http\Resources\ChannelResource;
use App\Models\Channel;
use App\Models\CustomBook;
use App\Models\DhammaTerm;
use App\Models\PaliSentence;
use App\Models\Sentence;
use App\Models\WbwBlock;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ChannelController extends Controller
{
    /**
     * 列出 channel（译文集）
     *
     * 按 view 指定的口径返回 channel 列表，支持关键字搜索、分页、排序。
     * 需要登录的口径：studio、studio-all、user-edit、user-in-chapter。
     *
     * @queryParam view string required 查询口径。
     *             Enum: public,studio,studio-all,user-edit,user-in-chapter,system,paragraphs,id
     * @queryParam name string studio 名称，view=studio / studio-all 时必填
     * @queryParam view2 string view=studio 时的二级口径：my=我的，其余值=协作的。Default: my
     * @queryParam collaborator string view=studio 且 view2 为协作时，按协作者 uid 过滤。Default: all
     * @queryParam id string view=id 时的 channel uid 列表，逗号分隔
     * @queryParam book integer view=user-in-chapter 时的典籍 id；传入后会联查该章节的翻译进度
     * @queryParam para string view=user-in-chapter 时的段落号
     * @queryParam book_id integer view=paragraphs 时的典籍 id
     * @queryParam paragraph string 联查 progress_chapters 时的段落号，与 book 配合使用
     * @queryParam progress string 传入则在每行附带 final 逐句完成情况，依赖 book 与 para
     * @queryParam type integer 按 channel 类型过滤
     * @queryParam updated_at string 只返回该时间之后更新的记录，用于离线包增量同步。
     *             Example: 2023-09-18T05:39:51.000000Z
     * @queryParam created_at string 只返回该时间之后新建的记录
     */
    public function index(Request $request)
    {
        //
        $result = false;
        // $chapter 只有 view=user-in-chapter 会赋值，下面按进度取数时要先判断
        $chapter = null;
        $indexCol = [
            'channels.uid',
            'name',
            'channels.summary',
            'type',
            'owner_uid',
            'channels.lang',
            'status',
            'is_system',
            'channels.updated_at',
            'channels.created_at',
        ];
        if ($request->has('book')) {
            $indexCol[] = 'progress_chapters.progress';
        }
        // FIXME: switch 没有 default 分支，view 缺失或不在枚举内时 $table 始终未定义，
        // 下面的 $table->count() 会直接 500。应补 default 返回参数错误。
        switch ($request->input('view')) {
            case 'public':
                $table = Channel::select($indexCol)
                    ->where('status', 30);
                break;
            case 'studio':
                // 获取studio内所有channel
                $user = AuthService::current($request);
                if (! $user) {
                    return $this->error(__('auth.failed'));
                }
                // 判断当前用户是否有指定的studio的权限
                $studioId = StudioApi::getIdByName($request->input('name'));
                if (! StudioApi::userCanList($user['user_uid'], $studioId)) {
                    return $this->error(__('auth.failed'), 403, 403);
                }

                $table = Channel::select($indexCol);
                if ($request->input('view2', 'my') === 'my') {
                    $table = $table->where('owner_uid', $studioId);
                } else {
                    // 协作
                    $resList = ShareApi::getResList($studioId, 2);
                    $resId = [];
                    foreach ($resList as $res) {
                        $resId[] = $res['res_id'];
                    }
                    $table = $table->whereIn('channels.uid', $resId);
                    if ($request->input('collaborator', 'all') !== 'all') {
                        $table = $table->where('owner_uid', $request->input('collaborator'));
                    } else {
                        $table = $table->where('owner_uid', '<>', $studioId);
                    }
                }
                break;
            case 'studio-all':
                /**
                 * studio 的和协作的
                 */
                // 获取user所有有权限的channel列表
                $user = AuthService::current($request);
                if (! $user) {
                    return $this->error(__('auth.failed'));
                }
                // 判断当前用户是否有指定的studio的权限
                if ($user['user_uid'] !== StudioApi::getIdByName($request->input('name'))) {
                    return $this->error(__('auth.failed'));
                }
                $channelById = [];
                $channelId = [];
                // 获取共享channel
                $allSharedChannels = ShareApi::getResList($user['user_uid'], 2);
                foreach ($allSharedChannels as $key => $value) {
                    // code...
                    $channelId[] = $value['res_id'];
                    $channelById[$value['res_id']] = $value;
                }
                $table = Channel::select($indexCol)
                    ->whereIn('uid', $channelId)
                    ->orWhere('owner_uid', $user['user_uid']);
                break;
            case 'user-edit':
                /**
                 * 某用户有编辑权限的
                 */
                // 获取user所有有权限的channel列表
                $user = AuthService::current($request);
                if (! $user) {
                    return $this->error(__('auth.failed'));
                }
                $channelById = [];
                $channelId = [];
                // 获取共享channel
                $allSharedChannels = ShareApi::getResList($user['user_uid'], 2);
                foreach ($allSharedChannels as $key => $value) {
                    // code...
                    if ($value['power'] >= 20) {
                        $channelId[] = $value['res_id'];
                        $channelById[$value['res_id']] = $value;
                    }
                }
                $table = Channel::select($indexCol)
                    ->whereIn('uid', $channelId)
                    ->orWhere('owner_uid', $user['user_uid']);
                break;
            case 'user-in-chapter':
                // 获取user 在某章节 所有有权限的channel列表
                $user = AuthService::current($request);
                if (! $user) {
                    return $this->error(__('auth.failed'));
                }
                $channelById = [];
                $channelId = [];
                // 获取共享channel
                $allSharedChannels = ShareApi::getResList($user['user_uid'], 2);
                foreach ($allSharedChannels as $key => $value) {
                    // code...
                    $channelId[] = $value['res_id'];
                    $channelById[$value['res_id']] = $value;
                }
                // 获取全网公开channel
                $chapter = PaliTextApi::getChapterStartEnd($request->input('book'), $request->input('para'));
                $publicChannelsWithContent = Sentence::where('book_id', $request->input('book'))
                    ->whereBetween('paragraph', $chapter)
                    ->where('strlen', '>', 0)
                    ->where('status', 30)
                    ->groupBy('channel_uid')
                    ->select('channel_uid')
                    ->get();
                foreach ($publicChannelsWithContent as $key => $value) {
                    // code...
                    $value['res_id'] = $value->channel_uid;
                    $value['power'] = 10;
                    $value['type'] = 2;
                    if (! isset($channelById[$value['res_id']])) {
                        $channelId[] = $value['res_id'];
                        $channelById[$value['res_id']] = $value;
                    }
                }
                $table = Channel::select($indexCol)
                    ->whereIn('uid', $channelId)
                    ->orWhere('owner_uid', $user['user_uid']);
                break;
            case 'system':
                $table = Channel::select($indexCol)
                    ->where('owner_uid', config('mint.admin.root_uuid'));
                break;
            case 'paragraphs':
                $channels = Sentence::where('book_id', $request->input('book_id'))
                    ->whereIn('paragraph', explode(',', $request->input('para')))
                    ->groupBy('channel_uid')->select('channel_uid')->get();
                if (count($channels) > 0) {
                    $channelIds = array_map(fn ($item) => $item['channel_uid'], $channels->toArray());
                    $table = Channel::select($indexCol)
                        ->whereIn('uid', $channelIds);
                } else {
                    // FIXME: whereIsNull 不是 Eloquent 方法，会被动态 where 解析成 where('is_null', 'uid')，
                    // 查不存在的列直接报 SQL 错误。本意应是返回空结果集，改用 ->whereRaw('1 = 0') 或 whereNull('uid')。
                    $table = Channel::select($indexCol)->whereIsNull('uid');
                }
                break;
            case 'id':
                $table = Channel::select($indexCol)
                    ->whereIn('uid', explode(',', $request->input('id')));
        }

        if ($request->has('book')) {
            if ($request->input('view') === 'public') {
                $table = $table->leftJoin('progress_chapters', 'channels.uid', '=', 'progress_chapters.channel_id')
                    ->where('progress_chapters.book', $request->input('book'))
                    ->where('progress_chapters.para', $request->input('paragraph'));
            } else {
                $table = $table->leftJoin('progress_chapters', function ($join) use ($request) {
                    $join->on('channels.uid', '=', 'progress_chapters.channel_id')
                        ->where('progress_chapters.book', $request->input('book'))
                        ->where('progress_chapters.para', $request->input('paragraph')); // 条件写在这里！
                });
            }
        }
        // 处理搜索
        if (! empty($request->input('search'))) {
            $table = $table->where('name', 'like', '%'.$request->input('search').'%');
        }
        if ($request->has('type')) {
            $table = $table->where('type', $request->input('type'));
        }
        if ($request->has('updated_at')) {
            $table = $table->where('updated_at', '>', $request->input('updated_at'));
        }
        if ($request->has('created_at')) {
            $table = $table->where('created_at', '>', $request->input('created_at'));
        }
        // 获取记录总条数
        $count = $table->count();
        // 处理排序
        $table = $table->orderBy(
            $request->input('order', 'created_at'),
            $request->input('dir', 'desc')
        );
        // 处理分页
        $table = $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 200));
        // 获取数据
        $result = $table->get();
        // TODO 将下面代码转移到resource
        if ($result) {
            if ($request->has('progress') && $chapter) {
                // 获取进度
                // 获取单句长度
                $sentLen = PaliSentence::where('book', $request->input('book'))
                    ->whereBetween('paragraph', $chapter)
                    ->orderBy('word_begin')
                    ->select(['book', 'paragraph', 'word_begin', 'word_end', 'length'])
                    ->get();
            }
            foreach ($result as $key => $value) {
                if ($request->has('progress') && $chapter) {
                    // 获取进度
                    $finalTable = Sentence::where('book_id', $request->input('book'))
                        ->whereBetween('paragraph', $chapter)
                        ->where('channel_uid', $value->uid)
                        ->where('strlen', '>', 0)
                        ->select(['strlen', 'book_id', 'paragraph', 'word_start', 'word_end']);
                    if ($finalTable->count() > 0) {
                        $finished = $finalTable->get();
                        $final = [];
                        foreach ($sentLen as $sent) {
                            // code...
                            $first = Arr::first($finished, function ($value, $key) use ($sent) {
                                return $value->book_id == $sent->book &&
                                    $value->paragraph == $sent->paragraph &&
                                    $value->word_start == $sent->word_begin &&
                                    $value->word_end == $sent->word_end;
                            });
                            $final[] = [$sent->length, $first ? true : false];
                        }
                        $value['final'] = $final;
                    }
                }
                // 角色
                if (isset($user['user_uid'])) {
                    if ($value->owner_uid === $user['user_uid']) {
                        $value['role'] = 'owner';
                    } else {
                        if (isset($channelById) && isset($channelById[$value->uid])) {
                            switch ($channelById[$value->uid]['power']) {
                                case 10:
                                    // code...
                                    $value['role'] = 'member';
                                    break;
                                case 20:
                                    $value['role'] = 'editor';
                                    break;
                                case 30:
                                    $value['role'] = 'owner';
                                    break;
                                default:
                                    // code...
                                    $value['role'] = $channelById[$value->uid]['power'];
                                    break;
                            }
                        }
                    }
                }
                // 获取studio信息
                $value->studio = StudioApi::getById($value->owner_uid);
            }

            return $this->ok(['rows' => $result, 'count' => $count]);
        } else {
            return $this->ok(['rows' => [], 'count' => 0]);
        }
    }

    /**
     * 获取我的，和协作channel数量
     *
     * @return Response
     */
    public function showMyNumber(Request $request)
    {
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'));
        }
        // 判断当前用户是否有指定的studio的权限
        $studioId = StudioApi::getIdByName($request->input('studio'));
        if ($user['user_uid'] !== $studioId) {
            return $this->error(__('auth.failed'));
        }
        // 我的
        $my = Channel::where('owner_uid', $studioId)->count();
        // 协作
        $resList = ShareApi::getResList($studioId, 2);
        $resId = [];
        foreach ($resList as $res) {
            $resId[] = $res['res_id'];
        }
        $collaboration = Channel::whereIn('uid', $resId)->where('owner_uid', '<>', $studioId)->count();

        return $this->ok(['my' => $my, 'collaboration' => $collaboration]);
    }

    /**
     * 获取章节的进度
     *
     * @return Response
     */
    public function progress(Request $request)
    {
        $indexCol = ['uid', 'name', 'summary', 'type', 'owner_uid', 'lang', 'status', 'updated_at', 'created_at'];

        $sent = $request->input('sentence');
        $query = [];
        $queryWithChannel = [];
        $sentContainer = [];
        $sentLenContainer = [];

        $paliChannel = ChannelApi::getSysChannel('_System_Pali_VRI_');
        $customBookChannel = [];

        foreach ($sent as $value) {
            $ids = explode('-', $value);
            $idWithChannel = $ids;
            if (count($ids) === 4) {
                if ($ids[0] < 1000) {
                    $idWithChannel[] = $paliChannel;
                } else {
                    if (! isset($customBookChannel[$ids[0]])) {
                        $cbChannel = CustomBook::where('book_id', $ids[0])->value('channel_id');
                        if ($cbChannel) {
                            $customBookChannel[$ids[0]] = $cbChannel;
                        } else {
                            $customBookChannel[$ids[0]] = $paliChannel;
                        }
                    }
                    $idWithChannel[] = $customBookChannel[$ids[0]];
                }
                $sentContainer[$value] = false;
                $query[] = $ids;
                $queryWithChannel[] = $idWithChannel;
            }
        }
        // 获取单句长度
        if (count($query) > 0) {
            $table = Sentence::whereIns([
                'book_id',
                'paragraph',
                'word_start',
                'word_end',
                'channel_uid',
            ], $queryWithChannel)
                ->select(['book_id', 'paragraph', 'word_start', 'word_end', 'strlen']);
            $sentLen = $table->get();

            foreach ($sentLen as $value) {
                $strlen = $value->strlen;
                if (empty($strlen)) {
                    $strlen = 0;
                }
                $sentId = "{$value->book_id}-{$value->paragraph}-{$value->word_start}-{$value->word_end}";
                $sentLenContainer[$sentId] = $strlen;
            }
        }

        $channelById = [];
        $channelId = [];

        // 获取全网公开的有译文的channel
        if ($request->input('owner') === 'all' || $request->input('owner') === 'public') {
            if (count($query) > 0) {
                $fields = ['book_id', 'paragraph', 'word_start', 'word_end'];
                $publicChannelsWithContent = Sentence::whereIns($fields, $query)
                    ->where('strlen', '>', 0)
                    ->where('status', 30)
                    ->groupBy('channel_uid')
                    ->select('channel_uid')
                    ->get();
                foreach ($publicChannelsWithContent as $key => $value) {
                    // code...
                    $value['res_id'] = $value->channel_uid;
                    $value['power'] = 10;
                    $value['type'] = 2;
                    if (! isset($channelById[$value['res_id']])) {
                        $channelId[] = $value['res_id'];
                        $channelById[$value['res_id']] = $value;
                    }
                }
            }
        }

        // 获取 user 在某章节 所有有权限的 channel 列表
        $user = AuthService::current($request);
        if ($user !== false) {
            // 我自己的
            if ($request->input('owner') === 'all' || $request->input('owner') === 'my') {
                $my = Channel::select($indexCol)->where('owner_uid', $user['user_uid'])->get();
                foreach ($my as $key => $value) {
                    $channelId[] = $value->uid;
                    $channelById[$value->uid] = [
                        'res_id' => $value->uid,
                        'power' => 30,
                        'type' => 2,
                    ];
                }
            }

            // 获取共享channel
            if ($request->input('owner') === 'all' || $request->input('owner') === 'collaborator') {
                $allSharedChannels = ShareApi::getResList($user['user_uid'], 2);
                foreach ($allSharedChannels as $key => $value) {
                    // code...
                    if (! in_array($value['res_id'], $channelId)) {
                        $channelId[] = $value['res_id'];
                        $channelById[$value['res_id']] = $value;
                    }
                }
            }
        }

        // 所有有这些句子译文的channel
        if (count($query) > 0) {
            $allChannels = Sentence::whereIns(['book_id', 'paragraph', 'word_start', 'word_end'], $query)
                ->where('strlen', '>', 0)
                ->groupBy('channel_uid')
                ->select('channel_uid')
                ->get();
        }

        // 所有需要查询的channel
        $table = Channel::select(['uid', 'name', 'summary', 'type', 'owner_uid', 'lang', 'status', 'updated_at', 'created_at'])
            ->whereIn('uid', $channelId);
        if ($user !== false) {
            $table->orWhere('owner_uid', $user['user_uid']);
        }
        $result = $table->get();

        foreach ($result as $key => $value) {
            // 角色
            if ($user !== false && $value->owner_uid === $user['user_uid']) {
                $value['role'] = 'owner';
            } else {
                if (isset($channelById[$value->uid])) {
                    switch ($channelById[$value->uid]['power']) {
                        case 10:
                            // code...
                            $value['role'] = 'member';
                            break;
                        case 20:
                            $value['role'] = 'editor';
                            break;
                        case 30:
                            $value['role'] = 'owner';
                            break;
                        default:
                            // code...
                            $value['role'] = $channelById[$value->uid]['power'];
                            break;
                    }
                }
            }
            // 获取studio信息
            $result[$key]['studio'] = StudioApi::getById($value->owner_uid);

            // 获取进度
            if (count($query) > 0) {
                $currChannelId = $value->uid;
                $hasContent = Arr::first($allChannels, function ($value, $key) use ($currChannelId) {
                    return $value->channel_uid === $currChannelId;
                });
                if ($hasContent && count($query) > 0) {
                    $finalTable = Sentence::whereIns(['book_id', 'paragraph', 'word_start', 'word_end'], $query)
                        ->where('channel_uid', $currChannelId)
                        ->where('strlen', '>', 0)
                        ->select(['strlen', 'book_id', 'paragraph', 'word_start', 'word_end', 'created_at', 'updated_at']);
                    $created_at = time();
                    $edit_at = 0;
                    if ($finalTable->count() > 0) {
                        $finished = $finalTable->get();
                        $currChannel = [];
                        foreach ($finished as $rowFinish) {
                            $createTime = strtotime($rowFinish->created_at);
                            $updateTime = strtotime($rowFinish->updated_at);
                            if ($createTime < $created_at) {
                                $created_at = $createTime;
                            }
                            if ($updateTime > $edit_at) {
                                $edit_at = $updateTime;
                            }
                            $currChannel["{$rowFinish->book_id}-{$rowFinish->paragraph}-{$rowFinish->word_start}-{$rowFinish->word_end}"] = 1;
                        }
                        $final = [];
                        foreach ($sentContainer as $sentId => $rowSent) {
                            // code...
                            if (isset($currChannel[$sentId])) {
                                $final[] = [$sentLenContainer[$sentId], true];
                            } else {
                                $final[] = [$sentLenContainer[$sentId], false];
                            }
                        }
                        $result[$key]['final'] = $final;
                        $result[$key]['content_created_at'] = date('Y-m-d H:i:s', $created_at);
                        $result[$key]['content_updated_at'] = date('Y-m-d H:i:s', $edit_at);
                    }
                }
            }
        }

        // FIXME: 第二个元素漏写了键名，实际返回的是 {rows: [...], 0: n} 而不是 {rows, count}，
        // 与本控制器其他列表接口的返回结构不一致。应为 'count' => count($result)。
        return $this->ok(['rows' => $result, count($result)]);
    }

    /**
     * 新建 channel
     *
     * 在指定 studio 下新建译文集。同一 studio 内不允许重名。
     * 调用者必须对该 studio 有管理权限。
     *
     * @bodyParam studio string required 所属 studio 名称
     * @bodyParam name string required channel 名称，在同一 studio 内唯一
     * @bodyParam type integer required channel 类型
     * @bodyParam lang string required 译文语言代码。Example: zh-Hans
     */
    public function store(Request $request)
    {
        //
        $user = AuthService::current($request);
        if (! $user) {
            // FIXME: error() 的签名是 error(string $message, mixed $data, int $status)，
            // 这里把 401 当成了 data 传进去，响应体的 data 会变成数字 401。本文件多处同样写法。
            return $this->error(__('auth.failed'), 401, 401);
        }
        // 判断当前用户是否有指定的studio的权限
        $studioId = StudioApi::getIdByName($request->input('studio'));
        if (! StudioApi::userCanManage($user['user_uid'], $studioId)) {
            return $this->error(__('auth.failed'), 403, 403);
        }
        $studio = StudioApi::getById($studioId);
        // 查询是否重复
        if (Channel::where('name', $request->input('name'))
            ->where('owner_uid', $studioId)
            ->exists()
        ) {
            return $this->error(__('validation.exists', ['name']), 200, 200);
        }

        $channel = new Channel;
        $channel->id = app('snowflake')->id();
        $channel->name = $request->input('name');
        $channel->owner_uid = $studioId;
        $channel->type = $request->input('type');
        $channel->lang = $request->input('lang');
        $channel->editor_id = $user['user_id'];
        if (isset($studio['roles'])) {
            if (in_array('basic', $studio['roles'])) {
                $channel->status = 5;
            }
        }
        $channel->create_time = time() * 1000;
        $channel->modify_time = time() * 1000;
        $channel->save();

        return $this->ok($channel);
    }

    /**
     * 获取单个 channel
     *
     * 返回 channel 详情，附带所属 studio 与作者信息。
     *
     * @urlParam channel string required channel uid
     */
    public function show($id)
    {
        //
        $channel = Channel::find($id);
        if (! $channel) {
            return $this->error('no res');
        }
        $studio = StudioApi::getById($channel->owner_uid);
        $channel->studio = $studio;
        $channel->owner_info = ['nickname' => $studio['nickName'], 'username' => $studio['realName']];

        return $this->ok($channel);
    }

    /**
     * 按名称获取 channel
     *
     * @urlParam name string required channel 名称
     */
    public function showByName(string $name)
    {
        //
        $indexCol = ['uid', 'name', 'summary', 'type', 'owner_uid', 'lang', 'is_system', 'status', 'updated_at', 'created_at'];
        $channel = Channel::where('name', $name)->select($indexCol)->first();
        if ($channel) {
            return $this->ok(new ChannelResource($channel));
        } else {
            return $this->error('no channel');
        }
    }

    /**
     * 整体更新 channel
     *
     * 覆盖式更新，未传的字段会被写成 null；只改部分字段请用 PATCH /channel。
     * 系统 channel 不可修改；非 owner 需要 30 以上的协作权限。
     *
     * @urlParam channel string required channel uid
     *
     * @bodyParam name string required channel 名称
     * @bodyParam type integer required channel 类型
     * @bodyParam summary string required 简介
     * @bodyParam lang string required 译文语言代码
     * @bodyParam status integer required 状态。30 为全网公开
     * @bodyParam source_type string 来源类型，传入才会更新
     * @bodyParam source_id string 来源 id，传入才会更新
     */
    public function update(Request $request, Channel $channel)
    {
        // 鉴权
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), 401, 401);
        }
        if ($channel->is_system) {
            return $this->error('system channel', 403, 403);
        }
        if ($channel->owner_uid !== $user['user_uid']) {
            // 判断是否为协作
            $power = ShareApi::getResPower($user['user_uid'], $request->input('id'));
            if ($power < 30) {
                return $this->error(__('auth.failed'), 403, 403);
            }
        }
        $channel->name = $request->input('name');
        $channel->type = $request->input('type');
        $channel->summary = $request->input('summary');
        $channel->lang = $request->input('lang');
        $channel->status = $request->input('status');
        if ($request->has('source_type')) {
            $channel->source_type = $request->input('source_type');
        }
        if ($request->has('source_id')) {
            $channel->source_id = $request->input('source_id');
        }
        $channel->save();

        return $this->ok($channel);
    }

    /**
     * 局部更新 channel
     *
     * 只更新请求体中出现的字段。系统 channel 不可修改；非 owner 需要 30 以上的协作权限。
     *
     * @bodyParam name string channel 名称
     * @bodyParam type integer channel 类型
     * @bodyParam summary string 简介
     * @bodyParam lang string 译文语言代码
     * @bodyParam status integer 状态。30 为全网公开
     * @bodyParam config string channel 配置
     */
    public function patch(Request $request, Channel $channel)
    {
        // 鉴权
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), [], 401);
        }
        if ($channel->is_system) {
            return $this->error('system channel', 403, 403);
        }
        if ($channel->owner_uid !== $user['user_uid']) {
            // 判断是否为协作
            $power = ShareApi::getResPower($user['user_uid'], $request->input('id'));
            if ($power < 30) {
                return $this->error(__('auth.failed'), [], 403);
            }
        }
        if ($request->has('name')) {
            $channel->name = $request->input('name');
        }
        if ($request->has('type')) {
            $channel->type = $request->input('type');
        }
        if ($request->has('summary')) {
            $channel->summary = $request->input('summary');
        }
        if ($request->has('lang')) {
            $channel->lang = $request->input('lang');
        }
        if ($request->has('status')) {
            $channel->status = $request->input('status');
        }
        if ($request->has('config')) {
            $channel->config = $request->input('config');
        }
        $channel->save();

        return $this->ok($channel);
    }

    /**
     * 删除 channel
     *
     * 只有 owner 可以删除。channel 下已有译文、术语或逐词解析数据时拒绝删除。
     *
     * @urlParam channel string required channel uid
     */
    public function destroy(Request $request, Channel $channel)
    {
        //
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'));
        }
        // 判断当前用户是否有指定的studio的权限
        if ($user['user_uid'] !== $channel->owner_uid) {
            return $this->error(__('auth.failed'));
        }
        // 查询其他资源
        if (Sentence::where('channel_uid', $channel->uid)->exists()) {
            return $this->error('译文有数据无法删除');
        }
        if (DhammaTerm::where('channal', $channel->uid)->exists()) {
            return $this->error('术语有数据无法删除');
        }
        if (WbwBlock::where('channel_uid', $channel->uid)->exists()) {
            return $this->error('逐词解析有数据无法删除');
        }
        // FIXME: $delete 按值捕获，闭包内的赋值传不出来，接口永远返回 0。
        // 应改为 use (&$delete) 或让闭包 return 删除结果。
        $delete = 0;
        DB::transaction(function () use ($channel, $delete) {
            // TODO 删除相关资源
            $delete = $channel->delete();
        });

        return $this->ok($delete);
    }
}
