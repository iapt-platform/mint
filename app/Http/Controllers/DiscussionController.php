<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use Illuminate\Http\Request;
use App\Http\Resources\DiscussionResource;


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
            case 'question':
                $table = Discussion::where('res_id',$request->get('id'))->where('parent',null);
                break;
            case 'answer':
                $table = Discussion::where('parent',$request->get('id'));
                break;
        }
        if(!empty($search)){
            $table->where('title', 'like', $search."%");
        }
        if(!empty($request->get('order')) && !empty($request->get('dir'))){
            $table->orderBy($request->get('order'),$request->get('dir'));
        }else{
            $table->orderBy('updated_at','asc');
        }
        $count = $table->count();
        if(!empty($request->get('limit'))){
            $offset = 0;
            if(!empty($request->get("offset"))){
                $offset = $request->get("offset");
            }
            $table->skip($offset)->take($request->get('limit'));
        }
        $result = $table->get();
        if($result){
			return $this->ok(["rows"=>DiscussionResource::collection($result),"count"=>$count]);
		}else{
			return $this->error("没有查询到数据");
		}
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = \App\Http\Api\AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //
        // validate
        // read more on validation at http://laravel.com/docs/validation
        $rules = array(
            'res_id' => 'required',
            'res_type' => 'required',
        );
        if(!$request->has('parent')){
            $rules['title'] = 'required';
        }

        $validated = $request->validate($rules);

        $discussion = new Discussion;
        $discussion->res_id = $request->get('res_id');
        $discussion->res_type = $request->get('res_type');
        $discussion->title = $request->get('title',null);
        $discussion->content = $request->get('content',null);
        $discussion->parent = $request->get('parent',null);
        $discussion->editor_uid = $user['user_uid'];
        $discussion->save();
        //更新parent children_count
        if($request->has('parent')){
            $parent = Discussion::find($request->get('parent'));
            if($parent){
                $parent->increment('children_count',1);
                $parent->save();
            }
        }
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Discussion  $discussion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Discussion $discussion)
    {
        //
        $user = \App\Http\Api\AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //
        if($discussion->editor !== $user['user_uid']){
            return $this->error(__('auth.failed'));
        }
        $discussion->title = $request->get('title',null);
        $discussion->content = $request->get('content',null);
        $discussion->editor_uid = $user['user_uid'];
        $discussion->save();
        return $this->ok($discussion);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Discussion  $discussion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Discussion $discussion)
    {
        //
        $user = \App\Http\Api\AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //TODO 其他有权限的人也可以删除
        if($discussion->editor !== $user['user_uid']){
            return $this->error(__('auth.failed'));
        }
        $delete = $discussion->delete();
        return $this->ok($delete);
    }
}
