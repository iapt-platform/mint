<?php

namespace App\Http\Controllers;

use App\Http\Api\DictApi;
use App\Http\Api\StudioApi;
use App\Http\Resources\UserDictResource;
use App\Models\DictInfo;
use App\Models\UserDict;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class UserDictController extends Controller
{
    /**
     * 列出用户词典条目
     *
     * 按 view 指定的口径返回 user_dict 记录，支持按单词、词根、词典过滤，以及分页与排序。
     * 需要登录的口径：all、studio；studio 口径还要求 name 指定的 studio 属于当前用户，否则返回 auth.failed。
     * community 口径只返回 status>5 且来源为 _USER_WBW_/_USER_DICT_ 的社区公开数据。
     * 返回 rows 与 count（过滤后总数）。
     *
     * @queryParam view string required 查询口径。all=全部；studio=某 studio 创建者的用户词条；user=cookie 中用户的词条（排除系统汇总）；word=按单词查全部；community=社区公开词条；compound=复合词机器人词典；dict=指定词典。Enum: all,studio,user,word,community,compound,dict
     * @queryParam name string studio 口径为 studio 名；dict 口径为系统词典短名。Example: robot_compound
     * @queryParam id string dict 口径下直接指定的词典 uid（与 name 二选一）
     * @queryParam word string 要查询的单词，word/community/compound 口径必填；其他口径传入时也会追加过滤
     * @queryParam parent string 按词根（parent）过滤
     * @queryParam dict string 按词典短名（dict_info.shortname）过滤，解析为 uuid 后才生效
     * @queryParam search string 按单词前缀模糊搜索
     * @queryParam order string 排序字段。Default: updated_at
     * @queryParam dir string 排序方向。Enum: asc,desc Default: desc
     * @queryParam offset integer 跳过的记录数。Default: 0
     * @queryParam limit integer 返回条数上限。Default: 200
     */
    public function index(Request $request)
    {
        //
        $result = false;
        $indexCol = [
            'id',
            'word',
            'type',
            'grammar',
            'mean',
            'parent',
            'note',
            'status',
            'factors',
            'confidence',
            'dict_id',
            'source',
            'updated_at',
            'creator_id',
        ];
        switch ($request->input('view')) {
            case 'all':
                // 获取studio内所有channel
                $user = AuthService::current($request);
                if (! $user) {
                    return $this->error(__('auth.failed'));
                }
                $table = UserDict::select($indexCol);
                break;
            case 'studio':
                // 获取studio内所有channel
                $user = AuthService::current($request);
                if (! $user) {
                    return $this->error(__('auth.failed'));
                }
                // 判断当前用户是否有指定的studio的权限
                if ($user['user_uid'] !== StudioApi::getIdByName($request->input('name'))) {
                    return $this->error(__('auth.failed'));
                }
                $table = UserDict::select($indexCol)
                    ->where('creator_id', $user['user_id'])
                    ->whereIn('source', ['_USER_WBW_', '_USER_DICT_']);
                break;
            case 'user':
                // code...
                $table = UserDict::select($indexCol)
                    ->where('creator_id', $_COOKIE['user_id'])
                    ->where('source', '<>', '_SYS_USER_WBW_');
                break;
            case 'word':
                $table = UserDict::select($indexCol)
                    ->where('word', $request->input('word'));
                break;
            case 'community':
                $table = UserDict::select($indexCol)
                    ->where('word', $request->input('word'))
                    ->where('status', '>', 5)
                    ->where(function ($query) {
                        $query->where('source', '_USER_WBW_')
                            ->orWhere('source', '_USER_DICT_');
                    });
                break;
            case 'compound':
                $dict_id = DictApi::getSysDict('robot_compound');
                // FIXME: $this->error(...) 没有 return，取不到词典时不会中断，会继续用 false 去查询。应改为 return $this->error(...)。
                if ($dict_id === false) {
                    $this->error('no robot_compound');
                }
                $table = UserDict::where('dict_id', $dict_id)->where('word', $request->input('word'));
                break;
            case 'dict':
                $dict_id = false;
                if ($request->has('name')) {
                    $dict_id = DictApi::getSysDict($request->input('name'));
                } elseif ($request->has('id')) {
                    $dict_id = $request->input('id');
                }

                // FIXME: $this->error(...) 没有 return，词典不存在时不会中断，会继续用 false 去查询。应改为 return $this->error(...)。
                if ($dict_id === false) {
                    $this->error('no dict', [], 404);
                }
                $table = UserDict::select($indexCol)
                    ->where('dict_id', $dict_id);
                // FIXME: 这里缺 break，会贯穿到 default（当前恰好无副作用，但属于隐患）。应补上 break;。
            default:
                // code...
                break;
        }
        // FIXME: view 不在枚举内（含缺省）时 $table 从未被赋值，下面的 $table->count() 会 fatal error。
        // 建议 default 分支直接 return $this->error('无法识别的参数view', 400, 400)。
        if ($request->has('search')) {
            $table->where('word', 'like', $request->input('search').'%');
        }
        if (($request->has('word'))) {
            $table = $table->where('word', $request->input('word'));
        }
        if (($request->has('parent'))) {
            $table = $table->where('parent', $request->input('parent'));
        }
        if (($request->has('dict'))) {
            $dictId = DictInfo::where('shortname', $request->input('dict'))->value('id');
            if (Str::isUuid($dictId)) {
                $table = $table->where('dict_id', $dictId);
            }
        }
        $count = $table->count();

        $table->orderBy(
            $request->input('order', 'updated_at'),
            $request->input('dir', 'desc')
        );

        $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 200));

        $result = $table->get();

        return $this->ok(['rows' => UserDictResource::collection($result), 'count' => $count]);
    }

    /**
     * 批量新增/更新用户词典条目
     *
     * 需要登录。data 是 JSON 字符串，解码后逐条处理：以 creator_id + word 以及请求中给出的
     * type/grammar/parent/mean/factors 组合判重，不存在则插入（自动生成雪花 id、按 view 写入 source、
     * 记录 create_time 与 creator_id），已存在则只更新 note 与 confidence。
     * 新增且 status>5 时会同步刷新系统汇总词典 _SYS_USER_WBW_；每次新增都会刷新该单词的 Redis 缓存 dict/user。
     * 返回 [新增条数, 汇总表更新结果]。
     *
     * @bodyParam view string required 数据来源口径，决定写入的 source：dict=_USER_DICT_，wbw=_USER_WBW_。Enum: dict,wbw
     * @bodyParam data string required 词条数组的 JSON 字符串，每项含 word（必填）及可选 type、grammar、parent、mean、factors、factormean、note、confidence、status、language
     */
    public function store(Request $request)
    {
        //
        $user = AuthService::current($request);
        // FIXME: $this->error('not login') 没有 return，未登录时会继续往下执行并在 $user['user_id'] 处 fatal error。
        // 应改为 return $this->error(__('auth.failed'), [], 401)。
        if (! $user) {
            $this->error('not login');
        }

        $_data = json_decode($request->input('data'), true);

        switch ($request->input('view')) {
            case 'dict':
                $src = '_USER_DICT_';
                break;
            case 'wbw':
                $src = '_USER_WBW_';
                break;
            default:
                $this->error('not view');
                break;
        }
        // 查询用户重复的数据
        $iOk = 0;
        $updateOk = 0;
        foreach ($_data as $key => $word) {
            // code...
            $table = UserDict::where('creator_id', $user['user_id'])
                ->where('word', $word['word']);
            if (isset($word['type'])) {
                $table = $table->where('type', $word['type']);
            }
            if (isset($word['grammar'])) {
                $table = $table->where('grammar', $word['grammar']);
            }
            if (isset($word['parent'])) {
                $table = $table->where('parent', $word['parent']);
            }
            if (isset($word['mean'])) {
                $table = $table->where('mean', $word['mean']);
            }
            if (isset($word['factors'])) {
                $table = $table->where('factors', $word['factors']);
            }
            $isDoesntExist = $table->doesntExist();
            if ($isDoesntExist) {
                // 不存在插入数据
                $word['id'] = app('snowflake')->id();
                $word['source'] = $src;
                $word['create_time'] = time() * 1000;
                $word['creator_id'] = $user['user_id'];
                $id = UserDict::insert($word);
                if (isset($word['status']) && $word['status'] > 5) {
                    $updateOk = $this->update_sys_wbw($word);
                } else {
                    $updateOk = true;
                }
                $this->update_redis($word);
                $iOk++;
            } else {
                // 存在，修改数据
                $origin = $table->first();
                if (isset($word['note'])) {
                    $origin->note = $word['note'];
                }
                if (isset($word['confidence'])) {
                    $origin->confidence = $word['confidence'];
                }
                $origin->save();
            }
        }

        return $this->ok([$iOk, $updateOk]);
    }

    /**
     * 查询单条用户词典记录
     *
     * 按主键查找，不做登录与权限校验；查不到返回「没有查询到数据」。
     *
     * @urlParam userdict string required 词条 id
     */
    public function show($id)
    {
        //
        $result = UserDict::find($id);
        if ($result) {
            return $this->ok($result);
        } else {
            return $this->error('没有查询到数据');
        }
    }

    /**
     * 更新单条用户词典记录
     *
     * 把请求体中的全部字段直接写入该记录（未做字段白名单与权限校验）。
     * 更新成功后同步刷新系统汇总词典 _SYS_USER_WBW_ 与该单词的 Redis 缓存 dict/user。
     * 没有记录被更新时返回「没有查询到数据」。
     *
     * @urlParam userdict string required 词条 id
     *
     * @bodyParam word string 单词
     * @bodyParam type string 词性
     * @bodyParam grammar string 语法属性
     * @bodyParam parent string 词根
     * @bodyParam mean string 词义
     * @bodyParam factors string 复合词拆分
     * @bodyParam factormean string 复合词各部分词义
     * @bodyParam note string 备注
     * @bodyParam confidence integer 置信度
     * @bodyParam status integer 状态，大于 5 表示公开到社区
     * @bodyParam language string 语言代码
     */
    public function update(Request $request, $id)
    {
        //
        // FIXME: 安全问题。直接把 $request->all() 整体落库，既无字段白名单（可改 creator_id、source、dict_id 等）
        // 也无登录与创建者校验，任何人都能改任意词条。应先鉴权并只允许更新白名单字段。
        $newData = $request->all();
        $result = UserDict::where('id', $id)
            ->update($newData);
        if ($result) {
            $updateOk = $this->update_sys_wbw($newData);
            $this->update_redis($newData);

            return $this->ok([$result, $updateOk]);
        } else {
            return $this->error('没有查询到数据');
        }
    }

    /**
     * 删除用户词典记录（支持批量）
     *
     * 需要登录，未登录返回 403。传了查询参数 id（JSON 数组）时走批量分支，逐条只删除 creator_id
     * 等于当前用户的记录，返回 [实际删除条数, 汇总表更新结果]；否则删除路径参数指定的单条记录，
     * 非本人创建返回 auth.failed。两种分支都会同步刷新 _SYS_USER_WBW_ 汇总与 Redis 缓存 dict/user。
     *
     * @urlParam userdict string required 要删除的词条 id（未传查询参数 id 时使用）
     *
     * @queryParam id string 要批量删除的词条 id 数组的 JSON 字符串。Example: ["123","456"]
     */
    public function destroy(Request $request, $id)
    {
        //
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), [], 403);
        }
        $user_id = $user['user_id'];

        if ($request->has('id')) {
            $arrId = json_decode($request->input('id'), true);
            $count = 0;
            $updateOk = false;
            foreach ($arrId as $key => $id) {
                // 找到对应数据
                // FIXME: find() 可能返回 null，下一行对 null 取属性会 fatal error。应先判空再处理。
                $data = UserDict::find($id);
                // 查看是否有权限删除
                if ($data->creator_id == $user_id) {
                    $result = UserDict::where('id', $id)
                        ->delete();
                    $count += $result;
                    $updateOk = $this->update_sys_wbw($data);
                    $this->update_redis($data);
                }
            }

            return $this->ok([$count, $updateOk]);
        } else {
            // 删除单个单词
            $userDict = UserDict::find($id);
            // 判断当前用户是否有指定的studio的权限
            // FIXME: $userDict 可能为 null（fatal error）；且用 (int) 与 creator_id 做 !== 严格比较，
            // creator_id 若是字符串列则永远不相等，会误判为无权限。建议先判空并改用 == 或统一类型。
            if ((int) $user_id !== $userDict->creator_id) {
                return $this->error(__('auth.failed'));
            }
            $delete = $userDict->delete();

            return $this->ok($delete);
        }
    }

    /**
     * 批量删除用户词典记录（旧接口）
     *
     * 不走 AuthService，凭据取自 cookie 中的 user_id，只删除该用户创建的记录。
     * 每删一条都会同步刷新 _SYS_USER_WBW_ 汇总与该单词的 Redis 缓存 dict/user。
     * 返回 deleted（实际删除条数）。
     *
     * @queryParam id string required 要删除的词条 id 数组的 JSON 字符串。Example: ["123","456"]
     */
    public function delete(Request $request)
    {
        // FIXME: 本方法不走 AuthService，凭据直接取自 $_COOKIE['user_id']（见下方 $param），
        // 未登录时会 undefined array key。应改用 AuthService::current($request) 并在未登录时返回 401。
        $arrId = json_decode($request->input('id'), true);
        $count = 0;
        $updateOk = false;
        foreach ($arrId as $key => $id) {
            $data = UserDict::where('id', $id)->first();
            if ($data) {
                // 找到对应数据
                $param = [
                    'id' => $id,
                    'creator_id' => $_COOKIE['user_id'],
                ];
                $del = UserDict::where($param)->delete();
                $count += $del;
                $updateOk = $this->update_sys_wbw($data);
                $this->update_redis($data);
            }
        }

        return $this->ok(['deleted' => $count]);
    }

    /*
    更新系统wbw汇总表
    */
    private function update_sys_wbw($data)
    {

        // 查询用户重复的数据
        if (! isset($data['type'])) {
            $data['type'] = null;
        }
        if (! isset($data['grammar'])) {
            $data['grammar'] = null;
        }
        if (! isset($data['parent'])) {
            $data['parent'] = null;
        }
        if (! isset($data['mean'])) {
            $data['mean'] = null;
        }
        if (! isset($data['factors'])) {
            $data['factors'] = null;
        }
        if (! isset($data['factormean'])) {
            $data['factormean'] = null;
        }

        $count = UserDict::where('word', $data['word'])
            ->where('type', $data['type'])
            ->where('grammar', $data['grammar'])
            ->where('parent', $data['parent'])
            ->where('mean', $data['mean'])
            ->where('factors', $data['factors'])
            ->where('factormean', $data['factormean'])
            ->where('source', $data['source'])
            ->count();

        if ($count === 0) {
            // 没有任何用户有这个数据
            // 删除数据
            $result = UserDict::where('word', $data['word'])
                ->where('type', $data['type'])
                ->where('grammar', $data['grammar'])
                ->where('parent', $data['parent'])
                ->where('mean', $data['mean'])
                ->where('factors', $data['factors'])
                ->where('factormean', $data['factormean'])
                ->where('source', '_SYS_USER_WBW_')
                ->delete();

            return $result;
        } else {
            // 更新或新增
            // 查询最早上传这个数据的用户
            $creator_id = UserDict::where('word', $data['word'])
                ->where('type', $data['type'])
                ->where('grammar', $data['grammar'])
                ->where('parent', $data['parent'])
                ->where('mean', $data['mean'])
                ->where('factors', $data['factors'])
                ->where('factormean', $data['factormean'])
                ->whereIn('source', ['_USER_WBW_', '_USER_DICT_'])
                ->orderby('created_at', 'asc')
                ->value('creator_id');

            $count = UserDict::where('word', $data['word'])
                ->where('type', $data['type'])
                ->where('grammar', $data['grammar'])
                ->where('parent', $data['parent'])
                ->where('mean', $data['mean'])
                ->where('factors', $data['factors'])
                ->where('factormean', $data['factormean'])
                ->where('source', '_SYS_USER_WBW_')
                ->count();
            if ($count === 0) {
                // 社区字典没有 新增
                $result = UserDict::insert(
                    [
                        'id' => app('snowflake')->id(),
                        'word' => $data['word'],
                        'type' => $data['type'],
                        'grammar' => $data['grammar'],
                        'parent' => $data['parent'],
                        'mean' => $data['mean'],
                        'factors' => $data['factors'],
                        'factormean' => $data['factormean'],
                        'language' => $data['language'],
                        'source' => '_SYS_USER_WBW_',
                        'creator_id' => $data['creator_id'],
                        'ref_counter' => 1,
                        'dict_id' => DictApi::getSysDict('community_extract'),
                        'create_time' => time() * 1000,
                    ]
                );
            } else {
                // 有，更新
                $result = UserDict::where('word', $data['word'])
                    ->where('type', $data['type'])
                    ->where('grammar', $data['grammar'])
                    ->where('parent', $data['parent'])
                    ->where('mean', $data['mean'])
                    ->where('factors', $data['factors'])
                    ->where('factormean', $data['factormean'])
                    ->where('source', '_SYS_USER_WBW_')
                    ->update(
                        [
                            'creator_id' => $creator_id,
                            'ref_counter' => $count,
                        ]
                    );
            }

            return $result;
        }
    }

    private function update_redis($word)
    {
        // 更新 redis
        $Fetch = UserDict::where(['word' => $word['word'], 'source' => '_USER_WBW_'])->get();
        $redisWord = [];
        foreach ($Fetch as $one) {
            // code...
            $redisWord[] = [
                $one['id'],
                $one['word'],
                $one['type'],
                $one['grammar'],
                $one['parent'],
                $one['mean'],
                $one['note'],
                $one['factors'],
                $one['factormean'],
                $one['status'],
                $one['confidence'],
                $one['creator_id'],
                $one['source'],
                $one['language'],
            ];
        }
        $redisData = json_encode($redisWord, JSON_UNESCAPED_UNICODE);
        Redis::hSet('dict/user', $word['word'], $redisData);
        $redisData1 = Redis::hGet('dict/user', $word['word']);

        // 更新redis结束
    }
}
