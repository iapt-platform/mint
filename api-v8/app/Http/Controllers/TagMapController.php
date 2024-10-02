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
            case 'item':
                $studioId = StudioApi::getIdByName($request->get('studio'));
                $table = TagMap::where('owner_uid',$studioId)
                               ->where('anchor_id',$request->get('res_id'))
                               ->leftJoin('tags','tag_maps.tag_id', '=', 'tags.id')
                               ->select(['tag_maps.id','table_name','anchor_id',
                                        'tag_id','tags.name','tags.color','tags.description',
                                    'owner_uid','editor_uid','tag_maps.created_at','tag_maps.updated_at']);
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

    public function userCanManage($userId,$ownerId,$courseId=null){
        if($userId === $ownerId){
            return true;
        }
        if(!empty($courseId)){
            $role = CourseApi::role($courseId,$userId);
            if(!empty($role) && $role !== 'student'){
                return true;
            }
        }
        return false;
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
        if($this->userCanManage($user['user_uid'],
                                $studioId,
                                $request->get('course')) ===false){
            return $this->error(__('auth.failed'),403,403);
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
    public function destroy(Request $request, TagMap $tagMap)
    {
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //判断当前用户是否有指定的studio的权限
        if($this->userCanManage($user['user_uid'],
                                $tagMap->owner_uid,
                                $request->get('course')) ===false){
            return $this->error(__('auth.failed'),403,403);
        }
        $delete = $tagMap->delete();

        return $this->ok($delete);
    }
}
