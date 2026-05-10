<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Article;
use App\Models\ArticleCollection;
use App\Models\Collection;
use App\Models\CustomBook;
use App\Models\CustomBookId;
use App\Models\Sentence;

use App\Http\Resources\ArticleResource;
use App\Services\AuthService;
use App\Http\Api\ShareApi;
use App\Http\Api\StudioApi;
use App\Http\Api\ChannelApi;
use App\Http\Api\SentenceApi;
use App\Tools\OpsLog;

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
        //私有文章，判断是否为所有者
        if ($user_uid === $article->owner) {
            return true;
        }
        //非所有者
        //判断是否为文章协作者
        $power = ShareApi::getResPower($user_uid, $article->uid);
        if ($power >= 10) {
            return true;
        }
        //无读取权限
        //判断文集是否有读取权限
        $inCollection = ArticleCollection::where('article_id', $article->uid)
            ->select('collect_id')
            ->groupBy('collect_id')->get();
        if (!$inCollection) {
            return false;
        }
        //查找与文章同主人的文集
        $collections = Collection::whereIn('uid', $inCollection)
            ->where('owner', $article->owner)
            ->select('uid')
            ->get();
        if (!$collections) {
            return false;
        }
        //查找与文章同主人的文集是否是共享的
        $power = 0;
        foreach ($collections as $collection) {
            # code...
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
        //私有文章，判断是否为所有者
        if ($user_uid === $article->owner) {
            return true;
        }
        //非所有者
        //判断是否为文章协作者
        $power = ShareApi::getResPower($user_uid, $article->uid);
        if ($power >= 20) {
            return true;
        }
        //无读取权限
        //判断文集是否有读取权限
        $inCollection = ArticleCollection::where('article_id', $article->uid)
            ->select('collect_id')
            ->groupBy('collect_id')->get();
        if (!$inCollection) {
            return false;
        }
        //查找与文章同主人的文集
        $collections = Collection::whereIn('uid', $inCollection)
            ->where('owner', $article->owner)
            ->select('uid')
            ->get();
        if (!$collections) {
            return false;
        }
        //查找与文章同主人的文集是否是共享的
        $power = 0;
        foreach ($collections as $collection) {
            # code...
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
        //判断是否为所有者
        if ($user_uid === StudioApi::getIdByName($studioName)) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
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
            'created_at'
        ];
        if ($request->input('content') === "true") {
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
                # 获取studio内所有 article
                $user = AuthService::current($request);
                if (!$user) {
                    return $this->error(__('auth.failed'), [], 401);
                }
                //判断当前用户是否有指定的studio的权限
                $studioId = StudioApi::getIdByName($request->input('name'));
                if ($user['user_uid'] !== $studioId) {
                    return $this->error(__('auth.failed'), [], 403);
                }

                if ($request->input('view2', 'my') === 'my') {
                    $table = $table->where('owner', $studioId);
                } else {
                    //协作
                    $resList = ShareApi::getResList($studioId, 3);
                    $resId = [];
                    foreach ($resList as $res) {
                        $resId[] = $res['res_id'];
                    }
                    $table = $table->whereIn('uid', $resId)->where('owner', '<>', $studioId);
                }

                //根据anthology过滤
                if ($request->has('anthology')) {
                    switch ($request->input('anthology')) {
                        case 'all':
                            break;
                        case 'none':
                            # 我的文集
                            $myCollection = Collection::where('owner', $studioId)->select('uid')->get();
                            //收录在我的文集里面的文章
                            $articles = ArticleCollection::whereIn('collect_id', $myCollection)
                                ->select('article_id')->groupBy('article_id')->get();
                            //不在这些范围之内的文章
                            $table =  $table->whereNotIn('uid', $articles);
                            break;
                        default:
                            $articles = ArticleCollection::where('collect_id', $request->input('anthology'))
                                ->select('article_id')->get();
                            $table =  $table->whereIn('uid', $articles);
                            break;
                    }
                }
                break;
            case 'public':
                $table = $table->where('status', 30);
                break;
            default:
                $this->error("view error");
                break;
        }
        //处理搜索
        if ($request->has("search") && !empty($request->input("search"))) {
            $table = $table->where('title', 'like', "%" . $request->input("search") . "%");
        }
        if ($request->has("subtitle") && !empty($request->input("subtitle"))) {
            $table = $table->where('subtitle', 'like', $request->input("subtitle"));
        }
        //获取记录总条数
        $count = $table->count();
        //处理排序
        $table = $table->orderBy(
            $request->input("order", 'updated_at'),
            $request->input("dir", 'desc')
        );
        //处理分页
        $table = $table->skip($request->input("offset", 0))
            ->take($request->input("limit", 1000));
        //获取数据
        $result = $table->get();
        return $this->ok(["rows" => ArticleResource::collection($result), "count" => $count]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showMyNumber(Request $request)
    {
        $user = AuthService::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'));
        }
        //判断当前用户是否有指定的studio的权限
        $studioId = StudioApi::getIdByName($request->input('studio'));
        if ($user['user_uid'] !== $studioId) {
            return $this->error(__('auth.failed'));
        }
        //我的
        $my = Article::where('owner', $studioId)->count();
        //协作
        $resList = ShareApi::getResList($studioId, 3);
        $resId = [];
        foreach ($resList as $res) {
            $resId[] = $res['res_id'];
        }
        $collaboration = Article::whereIn('uid', $resId)->where('owner', '<>', $studioId)->count();

        return $this->ok(['my' => $my, 'collaboration' => $collaboration]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //判断权限
        $user = AuthService::current($request);
        if (!$user) {
            Log::error('未登录');
            return $this->error(__('auth.failed'), [], 401);
        } else {
            $user_uid = $user['user_uid'];
        }

        $canManage = ArticleController::userCanManage($user_uid, $request->input('studio'));
        if (!$canManage) {
            Log::error('userCanManage 失败');
            //判断是否有文集权限
            if ($request->has('anthologyId')) {
                $currPower = ShareApi::getResPower($user_uid, $request->input('anthologyId'));
                if ($currPower <= 10) {
                    Log::error('没有文集编辑权限');
                    return $this->error(__('auth.failed'), [], 403);
                }
            } else {
                Log::error('没有文集id');
                return $this->error(__('auth.failed'), [], 403);
            }
        }
        //权限判断结束

        //查询标题是否重复
        /*
        if(Article::where('title',$request->input('title'))->where('owner',$studioUuid)->exists()){
            return $this->error(__('validation.exists'));
        }*/
        Log::debug('开始新建' . $request->input('title'));

        $newArticle = new Article;
        DB::transaction(function () use ($user, $request, $newArticle) {
            $studioUuid = StudioApi::getIdByName($request->input('studio'));
            //新建文章，加入文集必须都成功。否则回滚
            $newArticle->id = app('snowflake')->id();
            $newArticle->uid = Str::uuid();
            $newArticle->title = mb_substr($request->input('title'), 0, 128, 'UTF-8');
            $newArticle->lang = $request->input('lang');
            if (!empty($request->input('status'))) {
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

            Log::debug('开始挂接 id=' . $newArticle->uid);
            $anthologyId = $request->input('anthologyId');
            if (Str::isUuid($anthologyId)) {
                $parentNode = $request->input('parentNode');
                if (Str::isUuid($parentNode)) {
                    Log::debug('有挂接点' . $parentNode);
                    $map = ArticleCollection::where('collect_id', $anthologyId)
                        ->orderBy('id')->get();
                    Log::debug('查询到原map数据' . count($map));
                    $newMap = array();
                    $parentNodeLevel = -1;
                    $appended = false;
                    foreach ($map as $key => $row) {
                        $orgNode = $row;
                        if (!$appended) {
                            if ($parentNodeLevel > 0) {
                                if ($row->level <= $parentNodeLevel) {
                                    //parent node 末尾
                                    $newNode = array();
                                    $newNode['collect_id'] = $anthologyId;
                                    $newNode['article_id'] = $newArticle->uid;
                                    $newNode['level'] = $parentNodeLevel + 1;
                                    $newNode['title'] = $newArticle->title;
                                    $newNode['children'] = 0;
                                    $newMap[] = $newNode;
                                    Log::debug('新增节点', ['node' => $newNode]);
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
                            //
                            Log::debug('没挂上 挂到结尾');
                            $newNode = array();
                            $newNode['collect_id'] = $anthologyId;
                            $newNode['article_id'] = $newArticle->uid;
                            $newNode['level'] = $parentNodeLevel + 1;
                            $newNode['title'] = $newArticle->title;
                            $newNode['children'] = 0;
                            $newMap[] = $newNode;
                        }
                    } else {
                        Log::error('没找到挂接点' . $parentNode);
                    }
                    Log::debug('新map数据' . count($newMap));

                    $delete = ArticleCollection::where('collect_id', $anthologyId)->delete();
                    Log::debug('删除旧map数据' . $delete);
                    $count = 0;
                    foreach ($newMap as $key => $row) {
                        $new = new ArticleCollection;
                        $new->id = app('snowflake')->id();
                        $new->article_id = $row["article_id"];
                        $new->collect_id = $row["collect_id"];
                        $new->title = $row["title"];
                        $new->level = $row["level"];
                        $new->children = $row["children"];
                        $new->editor_id = $user["user_id"];
                        if (isset($row["deleted_at"])) {
                            $new->deleted_at = $row["deleted_at"];
                        }
                        $new->save();
                        $count++;
                    }
                    Log::debug('新map数据' . $count);
                    ArticleMapController::updateCollection($anthologyId);
                } else {
                    $articleMap = new ArticleCollection();
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
     * Display the specified resource.
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function show(Request  $request, Article $article)
    {
        //
        if (!$article) {
            return $this->error("no recorder");
        }
        //判断权限
        $user = AuthService::current($request);
        if (!$user) {
            $user_uid = "";
        } else {
            $user_uid = $user['user_uid'];
        }

        $canRead = ArticleController::userCanRead($user_uid, $article);
        if (!$canRead) {
            return $this->error(__('auth.failed'), 403, 403);
        }
        return $this->ok(new ArticleResource($article));
    }
    /**
     * Display the specified resource.
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $article
     * @return \Illuminate\Http\Response
     */
    public function preview(Request  $request, string $articleId)
    {
        //
        $article = Article::find($articleId);
        if (!$article) {
            return $this->error("no recorder");
        }
        //判断权限
        $user = AuthService::current($request);
        if (!$user) {
            $user_uid = "";
        } else {
            $user_uid = $user['user_uid'];
        }

        $canRead = ArticleController::userCanRead($user_uid, $article);
        if (!$canRead) {
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Article $article)
    {
        //
        if (!$article) {
            return $this->error("no recorder");
        }
        //鉴权
        $user = AuthService::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'), 401, 401);
        } else {
            $user_uid = $user['user_uid'];
        }

        $canEdit = ArticleController::userCanEdit($user_uid, $article);
        if (!$canEdit) {
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
        $article->status = $request->input('status', 10);
        $article->editor_id = $user['user_id'];
        $article->modify_time = time() * 1000;
        $article->save();

        OpsLog::debug($user_uid, $article);
        return $this->ok(new ArticleResource($article));
    }

    /**
     * Remove the specified resource from storage.
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Article $article)
    {
        //
        $user = AuthService::current($request);
        if (!$user) {
            return $this->error(__('auth.failed'));
        }
        //判断当前用户是否有指定的studio的权限
        if ($user['user_uid'] !== $article->owner) {
            return $this->error(__('auth.failed'));
        }
        $delete = 0;
        DB::transaction(function () use ($article, $delete) {
            //TODO 删除文集中的文章
            $delete = $article->delete();
            ArticleMapController::deleteArticle($article->uid);
        });

        return $this->ok($delete);
    }

    public function toTpl($content, $anthologyId, $user)
    {
        //查询书号
        if (!Str::isUuid($anthologyId)) {
            throw new \Exception('anthology Id not uuid');
        }

        $bookId = $this->getBookId($anthologyId, $user);

        $tpl = $this->convertToTpl($content, $bookId['book'], $bookId['paragraph']);

        //保存原文到句子表
        $customBook = $this->getCustomBookByBookId($bookId['book']);
        $sentenceSave = new SentenceApi;
        $auth = $sentenceSave->auth($customBook->channel_id, $user['user_uid']);
        if (!$auth) {
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
        if (!$anthology) {
            throw new \Exception('anthology not exists id=' . $anthologyId);
        }
        $bookId = $anthology->book_id;
        if (empty($bookId)) {
            //生成 book id
            $newBookId = CustomBook::max('book_id') + 1;

            $newBook = new CustomBook;
            $newBook->id = app('snowflake')->id();
            $newBook->book_id = $newBookId;
            $newBook->title = $anthology->title;
            $newBook->owner = $anthology->owner;
            $newBook->editor_id = $user['user_id'];
            $newBook->lang = $anthology->lang;
            $newBook->status = $anthology->status;
            //查询anthology所在的studio有没有符合要求的channel 没有的话，建立
            $channelId = ChannelApi::userBookGetOrCreate($anthology->owner, $anthology->lang, $anthology->status);
            if ($channelId === false) {
                throw new \Exception('user book get fail studio=' . $anthology->owner . ' language=' . $anthology->lang);
            }
            $newBook->channel_id = $channelId;
            $ok = $newBook->save();
            if (!$ok) {
                throw new \Exception('user book create fail studio=' . $anthology->owner . ' language=' . $anthology->lang);
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
        if (!$maxPara) {
            $maxPara = 0;
        }
        return ['book' => $bookId, 'paragraph' => $maxPara + 1];
    }

    public function convertToTpl($content, $bookId, $paraStart)
    {
        $newSentence = array();
        $para = $paraStart;
        $sentNum = 1;
        $newText =  "";
        $isTable = false;
        $isList = false;
        $newSent = "";
        $sentences = explode("\n", $content);
        foreach ($sentences as $row) {
            //$data 为一行文本
            $listHead = "";
            $isList = false;

            $heading = false;
            $title = false;

            $trimData = trim($row);

            # 判断是否为list
            $listLeft = strstr($row, "- ", true);
            if ($listLeft !== FALSE) {
                if (ctype_space($listLeft) || empty($listLeft)) {
                    # - 左侧是空，判定为list
                    $isList = true;
                    $iListPos = mb_strpos($row, '- ', 0, "UTF-8");
                    $listHead = mb_substr($row, 0, $iListPos + 2, "UTF-8");
                    $listBody = mb_substr($row, $iListPos + 2, mb_strlen($row, "UTF-8") - $iListPos + 2, "UTF-8");
                }
            }

            # TODO 判断是否为标题
            $headingStart = mb_strpos($row, "# ", 0, 'UTF-8');
            if ($headingStart !== false) {
                $headingLeft = mb_substr($row, 0, $headingStart + 2, 'UTF-8');
                $title = mb_substr($row, $headingStart + 2, null, 'UTF-8');
                if (str_replace('#', '', trim($headingLeft)) === '') {
                    # 除了#没有其他东西，那么是标题
                    $heading = $headingLeft;
                    $newText .= $headingLeft;
                    $newText .= '{{' . "{$bookId}-{$para}-{$sentNum}-{$sentNum}" . "}}\n";
                    $newSentence[] = $this->newSent($bookId, $para, $sentNum, $sentNum, $title);
                    $newSent = "";
                    $para++;
                    $sentNum = 1;
                    continue;
                }
            }

            //判断是否为表格开始
            if (mb_substr($trimData, 0, 1, "UTF-8") == "|") {
                $isTable = true;
            }
            if ($trimData != "" && $isTable == true) {
                //如果是表格 不新增句子
                $newSent .= "{$row}\n";
                continue;
            }
            if ($isList == true) {
                $newSent .= $listBody;
            } else {
                $newSent .= $trimData;
            }

            #生成句子编号
            if ($trimData == "") {
                #空行
                if (strlen($newSent) > 0) {
                    //之前有内容
                    $newText .= '{{' . "{$bookId}-{$para}-{$sentNum}-{$sentNum}" . "}}\n";
                    $newSentence[] = $this->newSent($bookId, $para, $sentNum, $sentNum, $newSent);
                    $newSent = "";
                }
                #新的段落 不插入数据库
                $para++;
                $sentNum = 1;
                $newText .= "\n";
                $isTable = false; //表格开始标记
                $isList = false;
                continue;
            } else {
                $sentNum = $sentNum + 10;
            }

            if (mb_substr($trimData, 0, 2, "UTF-8") == "{{") {
                #已经有的句子链接不处理
                $newText .= $trimData . "\n";
            } else {
                $newText .= $listHead;
                $newText .= '{{' . "{$bookId}-{$para}-{$sentNum}-{$sentNum}" . "}}\n";
                $newSentence[] = $this->newSent($bookId, $para, $sentNum, $sentNum, $newSent);
                $newSent = "";
            }
        }

        return [
            'content' => $newText,
            'sentences' => $newSentence,
        ];
    }

    private function newSent($book, $para, $start, $end, $content)
    {
        return array(
            'book_id' => $book,
            'paragraph' => $para,
            'word_start' => $start,
            'word_end' => $end,
            'content' => $content,
        );
    }
}
