<?php

namespace App\Http\Controllers;

use App\Models\Like;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->get("view")) {
            case 'count':
                # code...
                $resulte = Like::where("target_id",$request->get("target_id"))
                                ->groupBy("type")
                                ->select("type")
                                ->selectRaw("count(*)")
                                ->get();
                if(isset($_COOKIE["user_uid"])){
                    foreach ($resulte as $key => $value) {
                        # code...
                        if(Like::where(["target_id"=>$request->get("target_id"),
                                        'type'=>$value->type,
                                        'user_id'=>$_COOKIE["user_uid"]])->exists()){
                            $resulte[$key]->selected = true;
                        }
                    }
                }
                break;
            default:
                # code...
                break;
        }

        return $this->ok($resulte);
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
        if(!isset($_COOKIE["user_uid"])){
            return $this->error("no login");
        }
        $param = $request->all();
        $param['user_id'] = $_COOKIE["user_uid"];
        $like = Like::firstOrCreate($param);

        return $this->ok($like);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Like  $like
     * @return \Illuminate\Http\Response
     */
    public function show(Like $like)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Like  $like
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Like $like)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Like  $like
     * @return \Illuminate\Http\Response
     */
    public function destroy(Like $like)
    {
        //
        if(!isset($_COOKIE["user_uid"])){
            return $this->error("no login");
        }
        if($like->user_id==$_COOKIE["user_uid"]){
            return $this->ok($like->delete());
        }
        
        $param = $request->all();
        $param['user_id'] = $_COOKIE["user_uid"];
        $like = Like::where($param)->delete();
        return $this->ok($like);
        
    }
    public function delete(Request $request){
        if(!isset($_COOKIE["user_uid"])){
            return $this->error("no login");
        }
        $param = [
            "id"=>$request->get('id'),
            'user_id'=>$_COOKIE["user_uid"]
        ];
        $del = Like::where($param)->delete();
        $count = Like::where('target_id',$request->get('target_id'))
                    ->where('type',$request->get('type'))
                    ->count();
        return $this->ok([
            'deleted'=>$del,
            'count'=>$count
            ]);
    }
}
