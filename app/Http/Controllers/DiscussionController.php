<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Discussion;
use App\Models\Wbw;
use App\Models\WbwBlock;
use App\Models\PaliSentence;
use App\Models\Sentence;
use App\Http\Resources\DiscussionResource;
use App\Http\Api\MdRender;
use App\Http\Api\AuthApi;
use App\Http\Api\Mq;
use App\Http\Controllers\ArticleController;

class DiscussionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
		switch ($request->get('view')) {
            case 'question-by-topic':
                $topic = Discussion::where('id',$request->get('id'));
                $topic->where('status',$request->get('status','active'))
                      ->select('res_id')->first();
                if(!$topic){
			        return $this->error("无效的id");
                }
                $table = Discussion::where('res_id',$topic->res_id);
                $activeNumber = Discussion::where('res_id',$topic->res_id)
                                            ->where('status','active')->count();
                $closeNumber = Discussion::where('res_id',$topic->res_id)
                                            ->where('status','close')->count();
                $table->where('status',$request->get('status','active'))
                                    ->where('parent',null);
                break;
            case 'question':
                $table = Discussion::where('res_id',$request->get('id'))
                                    ->where('type', $request->get('type','discussion'))
                                    ->where('status',$request->get('status','active'))
                                    ->where('parent',null);
                $activeNumber = Discussion::where('res_id',$request->get('id'))
                                            ->where('parent',null)
                                            ->where('type', $request->get('type','discussion'))
                                            ->where('status','active')->count();
                $closeNumber = Discussion::where('res_id',$request->get('id'))
                                            ->where('parent',null)
                                            ->where('type', $request->get('type','discussion'))
                                            ->where('status','close')->count();
                break;
            case 'answer':
                $table = Discussion::where('parent',$request->get('id'));
                $activeNumber = Discussion::where('parent',$request->get('id'))
                                        ->where('status','active')->count();
                $closeNumber = Discussion::where('parent',$request->get('id'))
                                        ->where('status','close')->count();
                break;
            case 'res_id':
                /**
                 * 先获取顶级节点
                 *
                 */
                $roots = Discussion::where('res_id',$request->get('id'))
                                    ->where('type', $request->get('type','discussion'))
                                    ->whereIn('status',explode(',',$request->get('status','active')) )
                                    ->where('parent',null)
                                    ->select('id')
                                    ->get();

                $table = Discussion::where(function ($query) use ($roots) {
                                        $query->whereIn('id'  , $roots)
                                            ->orWhereIn('parent', $roots);
                                        });
                $activeNumber = Discussion::where('res_id',$request->get('id'))
                                            ->where('type', $request->get('type','discussion'))
                                            ->where('status','active')->count();
                $closeNumber = Discussion::where('res_id',$request->get('id'))
                                            ->where('type', $request->get('type','discussion'))
                                            ->where('status','close')->count();
                break;
            case 'all':
                $table = Discussion::where('parent',null);
                $activeNumber = Discussion::where('parent',null)
                                        ->where('status','active')->count();
                $closeNumber = Discussion::where('parent',null)
                                        ->where('status','close')->count();
                break;
        }
        if(!empty($search)){
            $table = $table->where('title', 'like', $search."%");
        }
        $count = $table->count();

        $table = $table->orderBy($request->get('order','created_at'),$request->get('dir','desc'));
        $table = $table->skip($request->get("offset",0))
              ->take($request->get('limit',1000));

        $result = $table->get();

        $can_create = false;
        $can_reply = false;
        $user = AuthApi::current($request);

        switch ($request->get('type','discussion')) {
            case 'qa':
                switch ($request->get('res_type')) {
                    case 'article':
                        if($user && ArticleController::userCanEditId($user['user_uid'],$request->get('id'))){
                            $can_create = true;
                            $can_reply = true;
                        }
                        break;
                }
                break;
            case 'help':
                switch ($request->get('res_type')) {
                    case 'article':
                        if($user){
                            $can_reply = true;
                            if(ArticleController::userCanEditId($user['user_uid'],$request->get('id'))){
                                $can_create = true;
                            }
                        }
                        break;
                }
                break;
            case 'discussion':
                if($user){
                    $can_create = true;
                    $can_reply = true;
                }
                break;
        }

        return $this->ok([
            "rows" => DiscussionResource::collection($result),
            "count" => $count,
            'active' => $activeNumber,
            'close' => $closeNumber,
            'can_create' => $can_create,
            'can_reply' => $can_reply,
            ]);

    }

    public function discussion_tree(Request $request){
        $output = [];
        $sentences = $request->get("data");
        foreach ($sentences as $key => $sentence) {
            # 先查句子信息
            $sentInfo = Sentence::where('book_id',$sentence['book'])
                                ->where('paragraph',$sentence['paragraph'])
                                ->where('word_start',$sentence['word_start'])
                                ->where('word_end',$sentence['word_end'])
                                ->where('channel_uid',$sentence['channel_id'])
                                ->first();
            if($sentInfo){
                $sentPr = Discussion::where('res_id',$sentInfo['uid'])
                                ->whereNull('parent')
                                ->select('title','children_count','editor_uid')
                                ->orderBy('created_at','desc')->get();
                if(count($sentPr)>0){
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
        return $this->ok(['rows'=>$output,'count'=>count($output)]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = AuthApi::current($request);
        if(!$user){
            Log::error('discussion store auth failed {request}',['request'=>$request]);
            return $this->error(__('auth.failed'),[401],401);
        }
        //
        // validate
        // read more on validation at http://laravel.com/docs/validation

        if($request->has('parent')){
            $rules = [];
            $parentInfo = Discussion::find($request->get('parent'));
            if(!$parentInfo){
                return $this->error('no record');
            }
        }else{
            $rules = array(
            'res_id' => 'required',
            'res_type' => 'required',
            'title' => 'required',
        );
        }

        $validated = $request->validate($rules);

        $discussion = new Discussion;
        if($request->has('parent')){
            $discussion->res_id = $parentInfo->res_id;
            $discussion->res_type = $parentInfo->res_type;
        }else{
            $discussion->res_id = $request->get('res_id');
            $discussion->res_type = $request->get('res_type');
        }
        $discussion->type = $request->get('type','discussion');
        $discussion->tpl_id = $request->get('tpl_id');
        $discussion->title = $request->get('title',null);
        $discussion->content = $request->get('content',null);
        $discussion->content_type = $request->get('content_type',"markdown");
        $discussion->parent = $request->get('parent',null);
        $discussion->editor_uid = $user['user_uid'];
        $discussion->save();
        //更新parent children_count
        if($request->has('parent')){
            $parentInfo->increment('children_count',1);
            $parentInfo->save();
        }
        Mq::publish('discussion',new DiscussionResource($discussion));

        return $this->ok(new DiscussionResource($discussion));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Discussion  $discussion
     * @return \Illuminate\Http\Response
     */
    public function show(Discussion $discussion)
    {
        //
        return $this->ok(new DiscussionResource($discussion));

    }

        /**
     * 获取discussion 锚点的数据。以句子为最小单位，逐词解析也要显示单词所在的句子
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function anchor($id)
    {
        //
        $discussion = Discussion::find($id);
        switch ($discussion->res_type) {
            case 'wbw':
                # 从逐词解析表获取逐词解析数据
                $wbw = Wbw::where('uid',$discussion->res_id)->first();
                if(!$wbw){
                    return $this->error('no wbw data');
                }
                $wbwBlock = WbwBlock::where('uid',$wbw->block_uid)->first();
                if(!$wbwBlock){
                    return $this->error('no wbwBlock data');
                }
                $sent = PaliSentence::where('book',$wbw->book_id)
                                    ->where('paragraph',$wbw->paragraph)
                                    ->where('word_begin','<=',$wbw->wid)
                                    ->where('word_end','>=',$wbw->wid)
                                    ->first();
                if(!$sent){
                    return $this->error('no sent data');
                }
                $sentId = "{$sent['book']}-{$sent['paragraph']}-{$sent['word_begin']}-{$sent['word_end']}";
                $channel = $wbwBlock->channel_uid;
                $content = MdRender::render("{{".$sentId."}}",[$channel]);
                return $this->ok($content);
                break;

            default:
                # code...
                break;
        }
        return $this->ok();

    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Discussion  $discussion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Discussion $discussion)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'),[403],403);
        }
        //
        if($discussion->editor_uid !== $user['user_uid']){
            return $this->error(__('auth.failed'),[403],403);
        }
        $discussion->title = $request->get('title',null);
        $discussion->content = $request->get('content',null);
        $discussion->status = $request->get('status','active');
        if($request->has('type')){
            $discussion->type = $request->get('type');
        }
        $discussion->editor_uid = $user['user_uid'];
        $discussion->save();
        return $this->ok(new DiscussionResource($discussion));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Discussion  $discussion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,Discussion $discussion)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'),[401],401);
        }
        //TODO 其他有权限的人也可以删除
        if($discussion->editor_uid !== $user['user_uid']){
            return $this->error(__('auth.failed'),[403],403);
        }
        $delete = $discussion->delete();
        return $this->ok($delete);
    }
}
