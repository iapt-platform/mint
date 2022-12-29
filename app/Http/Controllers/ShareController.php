<?php

namespace App\Http\Controllers;

use App\Models\Share;
use App\Models\GroupInfo;
use Illuminate\Http\Request;
use App\Http\Resources\ShareResource;

class ShareController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $result=false;
		$indexCol = ['id','res_id','res_type','power','updated_at','created_at'];
		switch ($request->get('view')) {
            case 'group':
	            # 获取 group 内所有 成员
                $user = \App\Http\Api\AuthApi::current($request);
                if($user){
                    //TODO 判断当前用户是否有指定的 group 的权限

                    if(GroupInfo::where('uid',$request->get('id'))->where('owner',$user['user_uid'])->exists()){
                        $table = Share::where('cooperator_id', $request->get('id'));
                    }else{
                        return $this->error(__('auth.failed'));
                    }
                }else{
                    return $this->error(__('auth.failed'));
                }
				break;
        }
        if(isset($_GET["search"])){
            //TODO 搜索资源标题
            $table = $table->where('title', 'like', $_GET["search"]."%");
        }
        $count = $table->count();
        if(isset($_GET["order"]) && isset($_GET["dir"])){
            $table = $table->orderBy($_GET["order"],$_GET["dir"]);
        }else{
            $table = $table->orderBy('updated_at','desc');
        }

        if(isset($_GET["limit"])){
            $offset = 0;
            if(isset($_GET["offset"])){
                $offset = $_GET["offset"];
            }
            $table = $table->skip($offset)->take($_GET["limit"]);
        }
        $result = $table->get();
        //TODO 获取当前用户的身份
        $role = "member";

		if($result){
			return $this->ok(["rows"=>ShareResource::collection($result),"count"=>$count,'role'=>$role]);
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
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Share  $share
     * @return \Illuminate\Http\Response
     */
    public function show(Share $share)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Share  $share
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Share $share)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Share  $share
     * @return \Illuminate\Http\Response
     */
    public function destroy(Share $share)
    {
        //
    }
}
