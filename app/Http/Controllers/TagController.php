<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Tag;
use App\Models\TagMap;
use App\Models\ProgressChapter;
use Illuminate\Http\Request;
use App\Http\Resources\TagResource;
use App\Http\Api\AuthApi;
use App\Http\Api\StudioApi;

class TagController extends Controller
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
            case 'studio':
                $studioId = StudioApi::getIdByName($request->get('name'));
                $table = Tag::where('owner_id',$studioId);
                break;
        }

        if($request->has("search")){
            $table = $table->where('name', 'like', "%".$request->get("search")."%");
        }

        $count = $table->count();

        $table = $table->orderBy($request->get('order','created_at'),$request->get('dir','desc'));

        $table = $table->skip($request->get("offset",0))
                    ->take($request->get('limit',10));

        $result = $table->get();

        return $this->ok(
            [
            "rows"=>TagResource::collection($result),
            "count"=>$count,
            ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'),401,401);
        }
        //判断当前用户是否有指定的studio的权限
        $studioId = StudioApi::getIdByName($request->get('studio'));
        if($user['user_uid'] !== $studioId){
            return $this->error(__('auth.failed'),403,403);
        }
        //查询是否重复
        if(Tag::where('name',$request->get('name'))
                  ->where('owner_id',$user['user_uid'])
                  ->exists()){
            return $this->error(__('validation.exists',['name']),200,200);
        }
        $tag = new Tag;
        $tag->name = $request->get("name");
        $tag->description = $request->get("description");
        $tag->color = $request->get("color");
        $tag->owner_id = $studioId;
        $tag->save();
        return $this->ok(new TagResource($tag));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function show(Tag $tag)
    {
        //
        return $this->ok(new TagResource($tag));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tag $tag)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'),401,401);
        }
        //判断当前用户是否有指定的studio的权限
        $studioId = StudioApi::getIdByName($request->get('studio'));
        if($user['user_uid'] !== $studioId){
            return $this->error(__('auth.failed'),403,403);
        }
        //查询是否重复
        if(Tag::where('name',$request->get('name'))
                  ->where('owner_id',$user['user_uid'])
                  ->where('id','<>',$tag->id)
                  ->exists()){
            return $this->error(__('validation.exists',['name']),200,200);
        }

        $tag->name = $request->get("name");
        $tag->description = $request->get("description");
        $tag->color = $request->get("color");
        $tag->owner_id = $studioId;
        $tag->save();
        return $this->ok(new TagResource($tag));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tag $tag)
    {
        //
    }
}
