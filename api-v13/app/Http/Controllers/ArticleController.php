<?php

namespace App\Http\Controllers;

use App\Http\Api\ChannelApi;
use App\Http\Api\SentenceApi;
use App\Http\Api\ShareApi;
use App\Http\Api\StudioApi;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\ArticleCollection;
use App\Models\Collection;
use App\Models\CustomBook;
use App\Models\CustomBookId;
use App\Models\Sentence;
use App\Services\AuthService;
use App\Tools\OpsLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public static function userCanRead($user_uid, Article $article)
    {
        if ($article->status === 30) {
            return true;
        }
        if (empty($user_uid)) {
            return false;
        }
        // 私有文章，判断是否为所有者
        if ($user_uid === $article->owner) {
            return true;
        }
        // 非所有者
        // 判断是否为文章协作者
        $power = ShareApi::getResPower($user_uid, $article->uid);
        if ($power >= 10) {
            return true;
        }
        // 无读取权限
        // 判断文集是否有读取权限
        $inCollection = ArticleCollection::where('article_id', $article->uid)
            ->select('collect_id')
            ->groupBy('collect_id')->get();
        if (! $inCollection) {
            return false;
        }
        // 查找与文章同主人的文集
        $collections = Collection::whereIn('uid', $inCollection)
            ->where('owner', $article->owner)
            ->select('uid')
            ->get();
        if (! $collections) {
            return false;
        }
        // 查找与文章同主人的文集是否是共享的
        $power = 0;
        foreach ($collections as $collection) {
            // code...
            $currPower = ShareApi::getResPower($user_uid, $collection->uid);
            if ($currPower >= 10) {
                return true;
            }
        }

        return false;
    }

    public static function userCanEditId($user_uid, $articleId)
    {
        $article = Article::find($articleId);
        if ($article) {
            return ArticleController::userCanEdit($user_uid, $article);
        } else {
            return false;
        }
    }

    public static function userCanEdit($user_uid, $article)
    {
        if (empty($user_uid)) {
            return false;
        }
        // 私有文章，判断是否为所有者
        if ($user_uid === $article->owner) {
            return true;
        }
        // 非所有者
        // 判断是否为文章协作者
        $power = ShareApi::getResPower($user_uid, $article->uid);
        if ($power >= 20) {
            return true;
        }
        // 无读取权限
        // 判断文集是否有读取权限
        $inCollection = ArticleCollection::where('article_id', $article->uid)
            ->select('collect_id')
            ->groupBy('collect_id')->get();
        if (! $inCollection) {
            return false;
        }
        // 查找与文章同主人的文集
        $collections = Collection::whereIn('uid', $inCollection)
            ->where('owner', $article->owner)
            ->select('uid')
            ->get();
        if (! $collections) {
            return false;
        }
        // 查找与文章同主人的文集是否是共享的
        $power = 0;
        foreach ($collections as $collection) {
            // code...
            $currPower = ShareApi::getResPower($user_uid, $collection->uid);
            if ($currPower >= 20) {
                return true;
            }
        }

        return false;
    }

    public static function userCanManage($user_uid, $studioName)
    {
        if (empty($user_uid)) {
            return false;
        }
        // 判断是否为所有者
        if ($user_uid === StudioApi::getIdByName($studioName)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 列出 article（文章）
     *
     * 按 view 指定的口径返回文章列表，支持关键字搜索、分页、排序。
     * 默认只返回列表字段（uid、title、subtitle、summary、owner、lang、status、editor_id、时间），
     * content=true 时才附带正文与正文类型。
     * view=studio 需要登录，且当前登录用户必须就是 name 指定的 studio 的拥有者，否则返回 401/403。
     *
     * @queryParam view string required 查询口径。template=按 studio_name 取该 studio 的全部文章（模版库）；studio=取某 studio 内的文章，需要登录；public=只取已公开（status=30）的文章。Enum: template,studio,public
     * @queryParam content string 是否返回文章正文。传 true 时结果附带 content 与 content_type。Example: true
     * @queryParam studio_name string view=template 时使用，指定 studio 的名称
     * @queryParam name string view=studio 时使用，指定 studio 的名称，必须与当前登录用户一致
     * @queryParam view2 string view=studio 时的二级口径：my=我创建的（owner 为该 studio），其余值=协作的（别人共享给我的，res_type=3）。Default: my
     * @queryParam anthology string view=studio 时按文集过滤。all=不过滤；none=不属于我任何文集的文章；其余值按文集 uid 过滤。Enum: all,none
     * @queryParam search string 按标题模糊搜索（title like %search%）
     * @queryParam subtitle string 按副标题过滤（subtitle like，需自行带通配符）
     * @queryParam order string 排序字段。Default: updated_at
     * @queryParam dir string 排序方向。Enum: asc,desc Default: desc
     * @queryParam offset integer 分页起始位置。Default: 0
     * @queryParam limit integer 每页条数。Default: 1000
     */
    public function index(Request $request)
    {
        $field = [
            'uid',
            'title',
            'subtitle',
            'summary',
            'owner',
            'lang',
            'status',
            'editor_id',
            'updated_at',
            'created_at',
        ];
        if ($request->input('content') === 'true') {
            $field[] = 'content';
            $field[] = 'content_type';
        }
        $table = Article::select($field);
        switch ($request->input('view')) {
            case 'template':
                $studioId = StudioApi::getIdByName($request->input('studio_name'));
                $table = $table->where('owner', $studioId);
                break;
            case 'studio':
                // 获取studio内所有 article
                $user = AuthService::current($request);
                if (! $user) {
                    return $this->error(__('auth.failed'), [], 401);
                }
                // 判断当前用户是否有指定的studio的权限
                $studioId = StudioApi::getIdByName($request->input('name'));
                if ($user['user_uid'] !== $studioId) {
                    return $this->error(__('auth.failed'), [], 403);
                }

                if ($request->input('view2', 'my') === 'my') {
                    $table = $table->where('owner', $studioId);
                } else {
                    // 协作
                    $resList = ShareApi::getResList($studioId, 3);
                    $resId = [];
                    foreach ($resList as $res) {
                        $resId[] = $res['res_id'];
                    }
                    $table = $table->whereIn('uid', $resId)->where('owner', '<>', $studioId);
                }

                // 根据anthology过滤
                if ($request->has('anthology')) {
                    switch ($request->input('anthology')) {
                        case 'all':
                            break;
                        case 'none':
                            // 我的文集
                            $myCollection = Collection::where('owner', $studioId)->select('uid')->get();
                            // 收录在我的文集里面的文章
                            $articles = ArticleCollection::whereIn('collect_id', $myCollection)
                                ->select('article_id')->groupBy('article_id')->get();
                            // 不在这些范围之内的文章
                            $table = $table->whereNotIn('uid', $articles);
                            break;
                        default:
                            $articles = ArticleCollection::where('collect_id', $request->input('anthology'))
                                ->select('article_id')->get();
                            $table = $table->whereIn('uid', $articles);
                            break;
                    }
                }
                break;
            case 'public':
                $table = $table->where('status', 30);
                break;
            default:
                // FIXME: 这里只调用 error() 没有 return，view 非法时会继续往下执行并返回全表数据（最多 limit 条）；
                // 建议改成 return $this->error('view error', [], 400)。
                $this->error('view error');
                break;
        }
        // 处理搜索
        if ($request->has('search') && ! empty($request->input('search'))) {
            $table = $table->where('title', 'like', '%'.$request->input('search').'%');
        }
        if ($request->has('subtitle') && ! empty($request->input('subtitle'))) {
            $table = $table->where('subtitle', 'like', $request->input('subtitle'));
        }
        // 获取记录总条数
        $count = $table->count();
        // 处理排序
        $table = $table->orderBy(
            $request->input('order', 'updated_at'),
            $request->input('dir', 'desc')
        );
        // 处理分页
        $table = $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 1000));
        // 获取数据
        $result = $table->get();

        return $this->ok(['rows' => ArticleResource::collection($result), 'count' => $count]);
    }

    /**
     * 统计我的文章数量
     *
     * 返回指定 studio 下「我的」和「协作的」两类文章条数。
     * 需要登录，且当前登录用户必须就是 studio 的拥有者，否则返回鉴权失败。
     *
     * @queryParam studio string required studio 名称，必须与当前登录用户一致
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
        $my = Article::where('owner', $studioId)->count();
        // 协作
        $resList = ShareApi::getResList($studioId, 3);
        $resId = [];
        foreach ($resList as $res) {
            $resId[] = $res['res_id'];
        }
        $collaboration = Article::whereIn('uid', $resId)->where('owner', '<>', $studioId)->count();

        return $this->ok(['my' => $my, 'collaboration' => $collaboration]);
    }

    /**
     * 新建 article（文章）
     *
     * 需要登录。权限：当前用户必须是 studio 的拥有者；若不是，则必须对 anthologyId 指定的文集
     * 拥有大于只读的权限（power>10），否则返回 403。
     * 副作用：整个新建过程在事务中执行——写入 articles 表，若给了 anthologyId 则同时把文章挂进文集；
     * 若再给了 parentNode，会按目录树重建该文集的 article_collections 记录（删除后整表重插）并刷新文集缓存。
     * 标题超过 128 字会被截断。
     *
     * @bodyParam studio string required studio 名称，文章归属的 studio
     * @bodyParam title string required 文章标题，最多 128 字，超出截断
     * @bodyParam lang string required 文章语言代码
     * @bodyParam status integer 公开状态，30 表示公开。不传则用数据库默认值
     * @bodyParam parentId string 父文章 uid，写入 articles.parent
     * @bodyParam anthologyId string 文集 uid，传入则把新文章加入该文集
     * @bodyParam parentNode string 文集目录中的挂接点文章 uid，新文章作为它的子节点插入；
     *                             不传则作为一级节点（level=1）追加到文集末尾
     */
    public function store(Request $request)
    {
        // 判断权限
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), [], 401);
        } else {
            $user_uid = $user['user_uid'];
        }

        $canManage = ArticleController::userCanManage($user_uid, $request->input('studio'));
        if (! $canManage) {
            // 判断是否有文集权限
            if ($request->has('anthologyId')) {
                $currPower = ShareApi::getResPower($user_uid, $request->input('anthologyId'));
                if ($currPower <= 10) {
                    return $this->error(__('auth.failed'), [], 403);
                }
            } else {
                return $this->error(__('auth.failed'), [], 403);
            }
        }
        // 权限判断结束

        // 查询标题是否重复
        /*
        if(Article::where('title',$request->input('title'))->where('owner',$studioUuid)->exists()){
            return $this->error(__('validation.exists'));
        }*/
        $newArticle = new Article;
        DB::transaction(function () use ($user, $request, $newArticle) {
            $studioUuid = StudioApi::getIdByName($request->input('studio'));
            // 新建文章，加入文集必须都成功。否则回滚
            $newArticle->id = app('snowflake')->id();
            $newArticle->uid = Str::uuid();
            $newArticle->title = mb_substr($request->input('title'), 0, 128, 'UTF-8');
            $newArticle->lang = $request->input('lang');
            if (! empty($request->input('status'))) {
                $newArticle->status = $request->input('status');
            }
            $newArticle->owner = $studioUuid;
            $newArticle->owner_id = $user['user_id'];
            $newArticle->editor_id = $user['user_id'];
            $newArticle->parent = $request->input('parentId');
            $newArticle->create_time = time() * 1000;
            $newArticle->modify_time = time() * 1000;
            $newArticle->save();
            OpsLog::debug($user['user_uid'], $newArticle);

            $anthologyId = $request->input('anthologyId');
            if (Str::isUuid($anthologyId)) {
                $parentNode = $request->input('parentNode');
                if (Str::isUuid($parentNode)) {
                    $map = ArticleCollection::where('collect_id', $anthologyId)
                        ->orderBy('id')->get();
                    $newMap = [];
                    $parentNodeLevel = -1;
                    $appended = false;
                    foreach ($map as $key => $row) {
                        $orgNode = $row;
                        if (! $appended) {
                            if ($parentNodeLevel > 0) {
                                if ($row->level <= $parentNodeLevel) {
                                    // parent node 末尾
                                    $newNode = [];
                                    $newNode['collect_id'] = $anthologyId;
                                    $newNode['article_id'] = $newArticle->uid;
                                    $newNode['level'] = $parentNodeLevel + 1;
                                    $newNode['title'] = $newArticle->title;
                                    $newNode['children'] = 0;
                                    $newMap[] = $newNode;
                                    $appended = true;
                                }
                            } else {
                                if ($row->article_id === $parentNode) {
                                    $parentNodeLevel = $row->level;
                                    $orgNode['children'] = $orgNode['children'] + 1;
                                }
                            }
                        }
                        $newMap[] = $orgNode;
                    }
                    if ($parentNodeLevel > 0) {
                        if ($appended === false) {
                            $newNode = [];
                            $newNode['collect_id'] = $anthologyId;
                            $newNode['article_id'] = $newArticle->uid;
                            $newNode['level'] = $parentNodeLevel + 1;
                            $newNode['title'] = $newArticle->title;
                            $newNode['children'] = 0;
                            $newMap[] = $newNode;
                        }
                    } else {
                        // FIXME: parentNode 找不到挂接点时只写日志，不把文章加入文集，结果文章建成了却不在目录里、前端看不到；
                        // 建议改为回退成一级节点追加，或直接抛异常让事务回滚并向前端报错。
                        Log::warning('没找到挂接点'.$parentNode);
                    }

                    $delete = ArticleCollection::where('collect_id', $anthologyId)->delete();
                    $count = 0;
                    foreach ($newMap as $key => $row) {
                        $new = new ArticleCollection;
                        $new->id = app('snowflake')->id();
                        $new->article_id = $row['article_id'];
                        $new->collect_id = $row['collect_id'];
                        $new->title = $row['title'];
                        $new->level = $row['level'];
                        $new->children = $row['children'];
                        $new->editor_id = $user['user_id'];
                        if (isset($row['deleted_at'])) {
                            $new->deleted_at = $row['deleted_at'];
                        }
                        $new->save();
                        $count++;
                    }
                    ArticleMapController::updateCollection($anthologyId);
                } else {
                    $articleMap = new ArticleCollection;
                    $articleMap->id = app('snowflake')->id();
                    $articleMap->article_id = $newArticle->uid;
                    $articleMap->collect_id = $request->input('anthologyId');
                    $articleMap->title = Article::find($newArticle->uid)->title;
                    $articleMap->level = 1;
                    $articleMap->save();
                }
            }
        });
        if (Str::isUuid($newArticle->uid)) {
            return $this->ok(new ArticleResource($newArticle));
        } else {
            return $this->error('fail');
        }
    }

    /**
     * 查看单篇 article（文章）
     *
     * 前端阅读页最常用的接口。允许匿名访问：status=30（公开）的文章任何人可读；
     * 非公开文章需要登录，且满足以下之一——是文章 owner、对该文章有只读以上共享权限（power>=10）、
     * 或对该文章所在的（同一 owner 的）文集有只读以上共享权限，否则返回 403。
     *
     * @urlParam article string required 文章 uid（路由模型绑定到 Article，主键即 uid）
     *
     * @queryParam anthology string 所在文集 uid，用于在返回结果里渲染文集路径（path）
     * @queryParam channel string 渲染标题所用的 channel uid，多个用下划线分隔；不传则回退到文集的默认 channel
     */
    public function show(Request $request, Article $article)
    {
        if (! $article) {
            return $this->error('no recorder');
        }
        // 判断权限
        $user = AuthService::current($request);
        if (! $user) {
            $user_uid = '';
        } else {
            $user_uid = $user['user_uid'];
        }

        $canRead = ArticleController::userCanRead($user_uid, $article);
        if (! $canRead) {
            return $this->error(__('auth.failed'), 403, 403);
        }

        return $this->ok(new ArticleResource($article));
    }

    /**
     * 预览文章正文
     *
     * PUT /api/v2/article-preview/{id}。用请求体里的 content 覆盖文章正文后走一遍渲染再返回，
     * 不写数据库，用于编辑器里的即时预览。
     * 权限与查看文章一致：公开文章匿名可预览，否则需要读权限，无权限返回 401。
     * 未传 content 时返回 error('no content')，但 HTTP 状态码仍是 200。
     *
     * @urlParam id string required 文章 uid
     *
     * @bodyParam content string required 待预览的正文内容（markdown）
     *
     * @queryParam anthology string 所在文集 uid，用于渲染文集路径
     * @queryParam channel string 渲染所用 channel uid，多个用下划线分隔
     */
    public function preview(Request $request, string $articleId)
    {
        $article = Article::find($articleId);
        if (! $article) {
            return $this->error('no recorder');
        }
        // 判断权限
        $user = AuthService::current($request);
        if (! $user) {
            $user_uid = '';
        } else {
            $user_uid = $user['user_uid'];
        }

        $canRead = ArticleController::userCanRead($user_uid, $article);
        if (! $canRead) {
            return $this->error(__('auth.failed'), [], 401);
        }
        if ($request->has('content')) {
            $article->content = $request->input('content');

            return $this->ok(new ArticleResource($article));
        } else {
            return $this->error('no content', [], 200);
        }
    }

    /**
     * 更新 article（文章）
     *
     * 需要登录。权限：必须是文章 owner，或对文章有编辑以上共享权限（power>=20），
     * 或对文章所在的同 owner 文集有编辑以上权限，否则返回 401。
     * 字段会整体覆盖：title/subtitle 截断到 128 字，summary 截断到 1024 字，
     * status 未传时会被重置为 10（非公开），即部分更新也必须带全字段。
     * 副作用：to_tpl=true 时把正文转成模版——按行切句，正文中的句子被替换成 {{book-para-start-end}} 占位，
     * 同时把原文逐句写入 sentences 表（必要时为文集新建 CustomBook 与 channel），该过程需要对应 channel 的写权限。
     *
     * @urlParam article string required 文章 uid（路由模型绑定）
     *
     * @bodyParam title string required 标题，最多 128 字
     * @bodyParam subtitle string 副标题，最多 128 字
     * @bodyParam summary string 摘要，最多 1024 字
     * @bodyParam content string required 正文（markdown）
     * @bodyParam lang string required 语言代码
     * @bodyParam status integer 公开状态，30 表示公开。Default: 10
     * @bodyParam to_tpl boolean 是否把正文转换为模版并把原文切句入库，必须是布尔 true 才生效
     * @bodyParam anthology_id string to_tpl=true 时必填，文集 uid，用于定位/创建书号与 channel
     */
    public function update(Request $request, Article $article)
    {
        if (! $article) {
            return $this->error('no recorder');
        }
        // 鉴权
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), 401, 401);
        } else {
            $user_uid = $user['user_uid'];
        }

        $canEdit = ArticleController::userCanEdit($user_uid, $article);
        if (! $canEdit) {
            return $this->error(__('auth.failed'), 401, 401);
        }

        /*
        //查询标题是否重复
        if(Article::where('title',$request->input('title'))
                  ->where('owner',$article->owner)
                  ->where('uid',"<>",$article->uid)
                  ->exists()){
            return $this->error(__('validation.exists'));
        }*/

        $content = $request->input('content');
        if ($request->input('to_tpl') === true) {
            /**
             * 转化为模版
             */
            $tplContent = $this->toTpl(
                $content,
                $request->input('anthology_id'),
                $user
            );
            $content = $tplContent;
        }

        $article->title = mb_substr($request->input('title'), 0, 128, 'UTF-8');
        $article->subtitle = mb_substr($request->input('subtitle'), 0, 128, 'UTF-8');
        $article->summary = mb_substr($request->input('summary'), 0, 1024, 'UTF-8');
        $article->content = $content;
        $article->lang = $request->input('lang');
        // FIXME: status 未传时会被静默重置为 10（非公开），已公开的文章做部分更新会被降级；
        // 建议改为只有请求体明确带 status 时才赋值，否则保留原值。
        $article->status = $request->input('status', 10);
        $article->editor_id = $user['user_id'];
        $article->modify_time = time() * 1000;
        $article->save();

        OpsLog::debug($user_uid, $article);

        return $this->ok(new ArticleResource($article));
    }

    /**
     * 删除 article（文章）
     *
     * 需要登录，且只有文章 owner 本人可以删除（协作者不行），否则返回鉴权失败。
     * 副作用：在事务中删除文章记录，并同步把该文章从所有文集目录（article_collections）中摘除。
     *
     * @urlParam article string required 文章 uid（路由模型绑定）
     */
    public function destroy(Request $request, Article $article)
    {
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'));
        }
        // 判断当前用户是否有指定的studio的权限
        if ($user['user_uid'] !== $article->owner) {
            return $this->error(__('auth.failed'));
        }
        $delete = 0;
        // FIXME: $delete 按值捕获，闭包内的赋值传不出来，返回值恒为 0，前端拿不到真实删除结果；
        // 建议改为 use (&$delete) 引用捕获，或直接用 DB::transaction 的返回值。
        DB::transaction(function () use ($article, $delete) {
            // TODO 删除文集中的文章
            $delete = $article->delete();
            ArticleMapController::deleteArticle($article->uid);
        });

        return $this->ok($delete);
    }

    public function toTpl($content, $anthologyId, $user)
    {
        // 查询书号
        if (! Str::isUuid($anthologyId)) {
            throw new \Exception('anthology Id not uuid');
        }

        $bookId = $this->getBookId($anthologyId, $user);

        $tpl = $this->convertToTpl($content, $bookId['book'], $bookId['paragraph']);

        // 保存原文到句子表
        $customBook = $this->getCustomBookByBookId($bookId['book']);
        $sentenceSave = new SentenceApi;
        $auth = $sentenceSave->auth($customBook->channel_id, $user['user_uid']);
        if (! $auth) {
            throw new \Exception('auth fail');
        }
        foreach ($tpl['sentences'] as $key => $sentence) {
            $sentenceSave->store($sentence, $user);
        }

        return $tpl['content'];
    }

    private function getCustomBookByBookId($bookId)
    {
        return CustomBook::where('book_id', $bookId)->first();
    }

    private function getBookId($anthologyId, $user)
    {
        $anthology = Collection::where('uid', $anthologyId)->first();
        if (! $anthology) {
            throw new \Exception('anthology not exists id='.$anthologyId);
        }
        $bookId = $anthology->book_id;
        if (empty($bookId)) {
            // 生成 book id
            $newBookId = CustomBook::max('book_id') + 1;

            $newBook = new CustomBook;
            $newBook->id = app('snowflake')->id();
            $newBook->book_id = $newBookId;
            $newBook->title = $anthology->title;
            $newBook->owner = $anthology->owner;
            $newBook->editor_id = $user['user_id'];
            $newBook->lang = $anthology->lang;
            $newBook->status = $anthology->status;
            // 查询anthology所在的studio有没有符合要求的channel 没有的话，建立
            $channelId = ChannelApi::userBookGetOrCreate($anthology->owner, $anthology->lang, $anthology->status);
            if ($channelId === false) {
                throw new \Exception('user book get fail studio='.$anthology->owner.' language='.$anthology->lang);
            }
            $newBook->channel_id = $channelId;
            $ok = $newBook->save();
            if (! $ok) {
                throw new \Exception('user book create fail studio='.$anthology->owner.' language='.$anthology->lang);
            }
            CustomBookId::where('key', 'max_book_number')->update(['value' => $newBookId]);
            $bookId = $newBookId;
            $anthology->book_id = $newBookId;
            $anthology->save();
        } else {
            $channelId = CustomBook::where('book_id', $bookId)->value('channel_id');
        }
        $maxPara = Sentence::where('channel_uid', $channelId)
            ->where('book_id', $bookId)->max('paragraph');
        if (! $maxPara) {
            $maxPara = 0;
        }

        return ['book' => $bookId, 'paragraph' => $maxPara + 1];
    }

    public function convertToTpl($content, $bookId, $paraStart)
    {
        $newSentence = [];
        $para = $paraStart;
        $sentNum = 1;
        $newText = '';
        $isTable = false;
        $isList = false;
        $newSent = '';
        $sentences = explode("\n", $content);
        foreach ($sentences as $row) {
            // $data 为一行文本
            $listHead = '';
            $isList = false;

            $heading = false;
            $title = false;

            $trimData = trim($row);

            // 判断是否为list
            $listLeft = strstr($row, '- ', true);
            if ($listLeft !== false) {
                if (ctype_space($listLeft) || empty($listLeft)) {
                    // - 左侧是空，判定为list
                    $isList = true;
                    $iListPos = mb_strpos($row, '- ', 0, 'UTF-8');
                    $listHead = mb_substr($row, 0, $iListPos + 2, 'UTF-8');
                    $listBody = mb_substr($row, $iListPos + 2, mb_strlen($row, 'UTF-8') - $iListPos + 2, 'UTF-8');
                }
            }

            // TODO 判断是否为标题
            $headingStart = mb_strpos($row, '# ', 0, 'UTF-8');
            if ($headingStart !== false) {
                $headingLeft = mb_substr($row, 0, $headingStart + 2, 'UTF-8');
                $title = mb_substr($row, $headingStart + 2, null, 'UTF-8');
                if (str_replace('#', '', trim($headingLeft)) === '') {
                    // 除了#没有其他东西，那么是标题
                    $heading = $headingLeft;
                    $newText .= $headingLeft;
                    $newText .= '{{'."{$bookId}-{$para}-{$sentNum}-{$sentNum}"."}}\n";
                    $newSentence[] = $this->newSent($bookId, $para, $sentNum, $sentNum, $title);
                    $newSent = '';
                    $para++;
                    $sentNum = 1;

                    continue;
                }
            }

            // 判断是否为表格开始
            if (mb_substr($trimData, 0, 1, 'UTF-8') == '|') {
                $isTable = true;
            }
            if ($trimData != '' && $isTable == true) {
                // 如果是表格 不新增句子
                $newSent .= "{$row}\n";

                continue;
            }
            if ($isList == true) {
                $newSent .= $listBody;
            } else {
                $newSent .= $trimData;
            }

            // 生成句子编号
            if ($trimData == '') {
                // 空行
                if (strlen($newSent) > 0) {
                    // 之前有内容
                    $newText .= '{{'."{$bookId}-{$para}-{$sentNum}-{$sentNum}"."}}\n";
                    $newSentence[] = $this->newSent($bookId, $para, $sentNum, $sentNum, $newSent);
                    $newSent = '';
                }
                // 新的段落 不插入数据库
                $para++;
                $sentNum = 1;
                $newText .= "\n";
                $isTable = false; // 表格开始标记
                $isList = false;

                continue;
            } else {
                $sentNum = $sentNum + 10;
            }

            if (mb_substr($trimData, 0, 2, 'UTF-8') == '{{') {
                // 已经有的句子链接不处理
                $newText .= $trimData."\n";
            } else {
                $newText .= $listHead;
                $newText .= '{{'."{$bookId}-{$para}-{$sentNum}-{$sentNum}"."}}\n";
                $newSentence[] = $this->newSent($bookId, $para, $sentNum, $sentNum, $newSent);
                $newSent = '';
            }
        }

        return [
            'content' => $newText,
            'sentences' => $newSentence,
        ];
    }

    private function newSent($book, $para, $start, $end, $content)
    {
        return [
            'book_id' => $book,
            'paragraph' => $para,
            'word_start' => $start,
            'word_end' => $end,
            'content' => $content,
        ];
    }
}
