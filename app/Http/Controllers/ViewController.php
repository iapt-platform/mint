<?php

namespace App\Http\Controllers;

use App\Models\View;
use App\Models\ProgressChapter;
use App\Models\PaliText;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\ViewResource;
use App\Http\Api\AuthApi;
use App\Http\Api\StudioApi;

class ViewController extends Controller
{
    private function getTargetId($request){
        /*
        *根据target type 和不同类型的不同参数
        */
        $target_id = FALSE;
        switch ($request->get("target_type")) {
            case 'chapter-instance':
                # code...
                $target_id = $request->get("target_id");
                break;
            case 'chapter':
                # code...
                $channelId = $request->get("channel");
                if(Str::isUuid($channelId)){
                    $target_id = ProgressChapter::where("channel_id",$request->get("channel"))
                                            ->where("book",$request->get("book",0))
                                            ->where("para",$request->get("para",0))
                                            ->value("uid");
                }else{
                    $target_id = false;
                }
                break;
            case 'article-instance':
                # code...
                break;
            case 'article':
                # code...
                break;
            default:
                $target_id = $request->get("target_id");
                # code...
                break;
        }
        if(Str::isUuid($target_id)){
            return $target_id;
        }else{
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
        switch ($request->get("view")) {
            case 'count':
                #获取 target id
                $target_id = $this->getTargetId($request);
                if($target_id){
                    $count = View::where("target_id",$target_id)->count();
                }else{
                    $count = 0;
                }
                return $this->ok($count);
                break;
            case 'user-recent':
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                $user_id = $user["user_uid"];
				$views =  View::where("user_id",$user_id)->orderBy('created_at','desc');
				$views = $views->take($request->get("take",10));
                $items = $views->get();
                return $this->ok($items);
                break;
            case 'user':
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                $user_id = $user["user_uid"];
                $table =  View::where("user_id",$user_id);
                break;
            case 'studio':
                # 获取studio内所有 数据
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                //判断当前用户是否有指定的studio的权限
                $studioId = StudioApi::getIdByName($request->get('name'));
                if($user['user_uid'] !== $studioId){
                    return $this->error(__('auth.failed'));
                }
                $table = View::where('user_id',$studioId);
                break;
            default:
                # code...
                break;
        }
        //处理搜索
        if($request->has("search")){
            $table = $table->where('name', 'like', "%".$request->get("search")."%");
        }
        //获取记录总条数
        $count = $table->count();
        //处理排序
        $table = $table->orderBy($request->get("order",'updated_at'),$request->get("dir",'desc'));
        //处理分页
        $table = $table->skip($request->get("offset",0))
                       ->take($request->get("limit",20));
        //获取数据
        $result = $table->get();
        return $this->ok(["rows"=>ViewResource::collection($result),"count"=>$count]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /*
        $rules = array(
            'target_type' => 'required'
        );
        $validator = Validator::make($request->all(), $rules);

        // process the login
        if ($validator->fails()) {
            return $this->error($validator);
        }
*/
        //根据target type 获取 target id
        $target_id = $this->getTargetId($request);
        if(!$target_id){
            return $this->error('no id');
        }
        $clientIp = request()->ip();
        $param = [
            'target_id' => $target_id,
            'target_type' => $request->get("target_type"),
        ];
        $user = AuthApi::current($request);
        if($user){
            //已经登陆
            $user_id = $user['user_uid'];
            $param['user_id'] = $user_id;
        }
        $param['user_ip'] = $clientIp;
        $new = View::firstOrNew($param);
        $new->user_ip = $clientIp;

		switch($request->get("target_type")){
			case "chapter":
				$new->title = ProgressChapter::where("channel_id",$request->get("channel"))
                                            ->where("book",$request->get("book"))
                                            ->where("para",$request->get("para"))
                                            ->value("title");
				$new->org_title = PaliText::where("book",$request->get("book"))
										->where("paragraph",$request->get("para"))
										->value("toc");
				//获取标题 成功
				$new->meta = \json_encode([
					"book"=>$request->get("book"),
					"para"=>$request->get("para"),
					"channel"=>$request->get("channel"),
                    "mode"=>$request->get("mode","read"),
				]);
				break;
            default:
                return $this->error('未知的数据类型');
                break;
		}
		$new->count = $new->count+1;
        $new->save();
		//保存成功

        $count = View::where("target_id",$new->target_id)->count();
        return $this->ok($count);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\View  $view
     * @return \Illuminate\Http\Response
     */
    public function show(View $view)
    {
        //

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\View  $view
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, View $view)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\View  $view
     * @return \Illuminate\Http\Response
     */
    public function destroy(View $view)
    {
        //
    }
}
