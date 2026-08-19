<?php

namespace App\Http\Controllers;

use App\Http\Api\ChannelApi;
use App\Http\Api\ShareApi;
use App\Http\Api\StudioApi;
use App\Http\Controllers\Concerns\ChecksChannelEditPower;
use App\Http\Resources\TermResource;
use App\Models\Channel;
use App\Models\DhammaTerm;
use App\Services\AuthService;
use App\Tools\Tools;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DhammaTermController extends Controller
{
    use ChecksChannelEditPower;

    /**
     * Display a listing of the resource.
     *
     * @return Response
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
            'note',
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
                return $this->ok(DhammaTerm::find($request->input('id')));
                break;
            case 'user':
                // code...
                $user = AuthService::current($request);
                if (! $user) {
                    return $this->error(__('auth.failed'));
                }
                $userUid = $user['user_uid'];
                $search = $request->input('search');
                $table = DhammaTerm::select($indexCol)
                    ->where('owner', $userUid);
                break;
            case 'word':
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
                    Cache::put($key, $hotMeaning, 3600);

                    return $hotMeaning;
                });

                return $this->ok(['rows' => $value, 'count' => count($value)]);
                break;
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
     * Store a newly created resource in storage.
     *
     * @return Response
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
        $table = DhammaTerm::where('owner', $user['user_uid'])
            ->where('word', $request->input('word'))
            ->where('tag', $request->input('tag'));
        if (! empty($request->input('channel'))) {
            $isDoesntExist = $table->where('channal', $request->input('channel'))
                ->doesntExist();
        } else {
            $isDoesntExist = $table->whereNull('channal')->where('language', $request->input('language'))
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
            $term->editor_id = $user['user_id'];
            $term->editor_uid = $user['user_uid'];
            $term->create_time = time() * 1000;
            $term->modify_time = time() * 1000;
            $term->save();
            // 删除cache
            $this->deleteCache($term);

            return $this->ok(new TermResource($term));
        } else {
            return $this->error('word existed', [], 200);
        }
    }

    private function deleteCache($term)
    {
        if (empty($term->channal)) {
            // 通用 查询studio所有channel
            $channels = Channel::where('owner_uid', $term->owner)->select('uid')->get();
            foreach ($channels as $channel) {
                Cache::forget("/term/{$channel}/{$term->word}");
            }
        } else {
            Cache::forget("/term/{$term->channal}/{$term->word}");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return Response
     */
    public function show(Request $request, $id)
    {
        //
        $result = DhammaTerm::where('guid', $id)->first();
        if ($result) {
            return $this->ok(new TermResource($result));
        } else {
            return $this->error('没有查询到数据');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  DhammaTerm  $dhammaTerm
     * @return Response
     */
    public function update(Request $request, string $id)
    {
        //
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), [], 401);
        }
        $dhammaTerm = DhammaTerm::find($id);
        if (! $dhammaTerm) {
            return $this->error('404');
        }

        if (empty($dhammaTerm->channal)) {
            // 查看有没有studio权限。access token 是 channel 级的，代持不了
            // studio 权限，故 studio 级术语只有 owner 本人能改。
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
        $dhammaTerm->editor_id = $user['user_id'];
        $dhammaTerm->editor_uid = $user['user_uid'];
        // create_time 是创建时刻，改动时不该被刷新
        $dhammaTerm->modify_time = time() * 1000;
        $dhammaTerm->save();
        // 删除cache
        $this->deleteCache($dhammaTerm);

        return $this->ok(new TermResource($dhammaTerm));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(DhammaTerm $dhammaTerm, Request $request)
    {
        /**
         * 一次删除多个单词
         */
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'));
        }
        $count = 0;
        if ($request->has('uuid')) {
            // 查看是否有删除权限
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
