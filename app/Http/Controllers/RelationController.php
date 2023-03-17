<?php

namespace App\Http\Controllers;

use App\Models\Relation;
use Illuminate\Http\Request;
use App\Http\Resources\RelationResource;
use App\Http\Api\AuthApi;

class RelationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $table = Relation::select(['id','name','case','to','editor_id','updated_at','created_at']);
        if(($request->has('search'))){
            $table->where('name', 'like', $request->get('search')."%");
        }
        if(!empty($request->get('order')) && !empty($request->get('dir'))){
            $table->orderBy($request->get('order'),$request->get('dir'));
        }else{
            $table->orderBy('updated_at','desc');
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
			return $this->ok(["rows"=>RelationResource::collection($result),"count"=>$count]);
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
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //TODO 判断权限
        $validated = $request->validate([
            'name' => 'required',
        ]);
        $case = $request->get('case','');
        $new = new Relation;
        $new->name = $validated['name'];
        if($request->has('case')){
            $new->case = json_encode($request->get('case'),JSON_UNESCAPED_UNICODE);
        }else{
            $new->case = null;
        }
        if($request->has('to')){
            $new->to = json_encode($request->get('to'),JSON_UNESCAPED_UNICODE);
        }else{
            $new->to = null;
        }
        $new->editor_id = $user['user_uid'];
        $new->save();
        return $this->ok(new RelationResource($new));

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Relation  $relation
     * @return \Illuminate\Http\Response
     */
    public function show(Relation $relation)
    {
        //
        return $this->ok(new RelationResource($relation));

    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Relation  $relation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Relation $relation)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }

        $relation->name = $request->get('name');
        if($request->has('case')){
            $relation->case = json_encode($request->get('case'),JSON_UNESCAPED_UNICODE);
        }else{
            $relation->case = null;
        }
        if($request->has('to')){
            $relation->to = json_encode($request->get('to'),JSON_UNESCAPED_UNICODE);
        }else{
            $relation->to = null;
        }
        $relation->editor_id = $user['user_uid'];
        $relation->save();
        return $this->ok(new RelationResource($relation));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Relation  $relation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,Relation $relation)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //TODO 判断当前用户是否有权限
        $delete = 0;
        $delete = $relation->delete();

        return $this->ok($delete);
    }
}
