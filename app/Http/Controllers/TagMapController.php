<?php

namespace App\Http\Controllers;

use App\Models\TagMap;
use App\Models\Tag;
use Illuminate\Http\Request;
use App\Http\Resources\TagMapResource;
use App\Http\Resources\TagResource;
use App\Http\Api\AuthApi;
use App\Http\Api\StudioApi;
use App\Http\Api\CourseApi;
use Illuminate\Support\Str;

class TagMapController extends Controller
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
            case 'items':
                $table = TagMap::where('tag_id',$request->get('tag_id'));
                break;
        }

        $count = $table->count();

        $table = $table->orderBy($request->get('order','created_at'),$request->get('dir','desc'));

        $table = $table->skip($request->get("offset",0))
                    ->take($request->get('limit',10));

        $result = $table->get();

        return $this->ok(
            [
            "rows"=>TagMapResource::collection($result),
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
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'),401,401);
        }
        //判断当前用户是否有指定的studio的权限
        $studioId = StudioApi::getIdByName($request->get('studio'));
        if($user['user_uid'] !== $studioId){
            if(!empty($request->get('course'))){
                $role = CourseApi::role($request->get('course'),$user['user_uid']);
                if(empty($role) && $role === 'student'){
                    return $this->error(__('auth.failed'),403,403);
                }
            }else{
                return $this->error(__('auth.failed'),403,403);
            }
        }
        //查询是否重复
        if(TagMap::where('anchor_id',$request->get('anchor_id'))
                  ->where('tag_id',$request->get('tag_id'))
                  ->where('owner_uid',$studioId)
                  ->exists()){
            return $this->error(__('validation.exists',['name']),200,200);
        }
        $tag = new TagMap;
        $tag->id = Str::uuid();
        $tag->table_name = $request->get("table_name");
        $tag->anchor_id = $request->get("anchor_id");
        $tag->tag_id = $request->get("tag_id");
        $tag->editor_uid = $user['user_uid'];
        $tag->owner_uid = $studioId;
        $tag->save();

        $tagsMap = TagMap::where('anchor_id',$request->get("anchor_id"))
                    ->where('owner_uid',$studioId)
                    ->select('tag_id')
                    ->get();

        return $this->ok(
            [
            "rows"=>TagMapResource::collection($tagsMap),
            "count"=>count($tagsMap),
            ]
        );

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TagMap  $tagMap
     * @return \Illuminate\Http\Response
     */
    public function show(TagMap $tagMap)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TagMap  $tagMap
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TagMap $tagMap)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TagMap  $tagMap
     * @return \Illuminate\Http\Response
     */
    public function destroy(TagMap $tagMap)
    {
        //
    }
}
