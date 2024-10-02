<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\UserOperationLog;
use App\Models\UserOperationFrame;
use App\Models\UserOperationDaily;
use App\Http\Api\AuthApi;

define("MAX_INTERVAL", 600000);
define("MIN_INTERVAL", 60000);

/**
 * 	$active_type[10] = "channel_update";
 * 	$active_type[11] = "channel_create";
 * 	$active_type[20] = "article_update";
 * 	$active_type[21] = "article_create";
 * 	$active_type[30] = "dict_lookup";
 * 	$active_type[40] = "term_update";
 * 	$active_type[42] = "term_create";
 * 	$active_type[41] = "term_lookup";
 * 	$active_type[60] = "wbw_update";
 * 	$active_type[61] = "wbw_create";
 * 	$active_type[70] = "sent_update";
 * 	$active_type[71] = "sent_create";
 * 	$active_type[80] = "collection_update";
 * 	$active_type[81] = "collection_create";
 * 	$active_type[90] = "nissaya_open";
 */
class UserOperation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $user = AuthApi::current($request);
        if(!$user){
            return $response;
        }


        $api = explode('/',$request->path());
        if(count($api)<3){
            return $response;
        }if($api[0] !== 'api' || $api[1] !=='v2'){
            return $response;
        }
        $method = $request->method();
        switch ($api[2]) {
            case 'channel':
                switch ($method) {
                    case 'POST':
                        $newLog = [
                            "op_type_id"=>11,
                            "op_type"=>"channel_create",
                            "content"=>$request->get('studio').'/'.$request->get('name'),
                        ];
                        break;
                    case 'PUT':
                        $newLog = [
                            "op_type_id"=>10,
                            "op_type"=>"channel_update",
                            "content"=>$request->get('name'),
                        ];
                        break;
                }
                break;
            case 'article':
                switch ($method) {
                    case 'POST':
                        $newLog = [
                            "op_type_id"=>21,
                            "op_type"=>"article_create",
                            "content"=>$request->get('studio').'/'.$request->get('title'),
                        ];
                        break;
                    case 'PUT':
                        $newLog = [
                            "op_type_id"=>20,
                            "op_type"=>"article_update",
                            "content"=>$request->get('title'),
                        ];
                        break;
                }
                break;
            case 'dict':
                $newLog = [
                    "op_type_id"=>30,
                    "op_type"=>"dict_lookup",
                    "content"=>$request->get("word")
                ];
                break;
            case 'terms':
                switch ($method) {
                    case 'POST':
                        $newLog = [
                            "op_type_id"=>42,
                            "op_type"=>"term_create",
                            "content"=>$request->get('word'),
                        ];
                        break;
                    case 'PUT':
                        $newLog = [
                            "op_type_id"=>40,
                            "op_type"=>"term_update",
                            "content"=>$request->get('word'),
                        ];
                        break;
                }
                break;
            case 'sentence':
                switch ($method) {
                    case 'POST':
                        $newLog = [
                            "op_type_id"=>71,
                            "op_type"=>"sent_create",
                            "content"=>$request->get('channel'),
                        ];
                        break;
                    case 'PUT':
                        $newLog = [
                            "op_type_id"=>70,
                            "op_type"=>"sent_update",
                            "content"=>$request->get('channel'),
                        ];
                        break;
                    /*
                    case 'GET':
                        if($request->get('view')==='sent-can-read' && $request->has('type')){
                            $newLog = [
                                "op_type"=>"res_read",
                                "content"=>$request->get('sentence'),
                            ];
                            switch ($request->get('type')) {
                                case 'translation':
                                    $newLog["op_type_id"] = 101;
                                    break;
                                case 'nissaya':
                                    $newLog["op_type_id"] = 102;
                                    break;
                                case 'original':
                                    $newLog["op_type_id"] = 103;
                                    break;
                                case 'wbw':
                                    $newLog["op_type_id"] = 104;
                                    break;
                                case 'commentary':
                                    $newLog["op_type_id"] = 105;
                                    break;
                                default:
                                    unset($newLog);
                                    break;
                            }
                        }
                        break;
                        */
                }
                break;
            case 'anthology':
                switch ($method) {
                    case 'POST':
                        $newLog = [
                            "op_type_id"=>81,
                            "op_type"=>"collection_create",
                            "content"=>$request->get('title'),
                        ];
                        break;
                    case 'PUT':
                        $newLog = [
                            "op_type_id"=>80,
                            "op_type"=>"collection_update",
                            "content"=>$request->get('title'),
                        ];
                        break;
                }
                break;
            /*
            case 'article-map':
                switch ($method) {
                    case 'POST':
                        $newLog = [
                            "op_type_id"=>111,
                            "op_type"=>"article_map_create",
                            "content"=>$request->get('anthology_id'),
                        ];
                        break;
                    case 'PUT':
                        $newLog = [
                            "op_type_id"=>110,
                            "op_type"=>"article_map_update",
                        ];
                        if(isset($api[3])){
                            $newLog['content'] = $api[3];
                        }
                        break;
                }
                break;
                */
            case 'wbw':
                switch ($method) {
                    case 'POST':
                        $newLog = [
                            "op_type_id"=>60,
                            "op_type"=>"wbw_update",
                            "content"=>$request->get('book')."_".$request->get('para')."_".$request->get('channel_id'),
                        ];
                        break;
                }
                break;
        }
        if(isset($newLog)){
            $currTime = round((microtime(true))*1000,0);
            #获取客户端时区偏移 beijing = +8
            if (isset($_COOKIE["timezone"])) {
                $client_timezone = (0 - (int) $_COOKIE["timezone"]) * 60 * 1000;
            } else {
                $client_timezone = 0;
            }

            $log = new UserOperationLog();
            $log->id = app('snowflake')->id();
            $log->user_id = $user['user_id'];
            $log->op_type_id = $newLog["op_type_id"];
            $log->op_type = $newLog["op_type"];
            $log->content = $newLog["content"];
            $log->timezone = $client_timezone;
            $log->create_time = $currTime;
            $log->save();

            //frame
            // 查询上次编辑活跃结束时间
            $last = UserOperationFrame::where("user_id",$user['user_id'])->orderBy("updated_at","desc")->first();
            if($last){
                //找到，判断是否超时，超时新建，未超时修改
                $id = (int) $last["id"];
                $start_time = (int) $last["op_start"];
                $endtime = (int) $last["op_end"];
                $hit = (int) $last["hit"];
                if ($currTime - $endtime > MAX_INTERVAL) {
                    //超时新建
                    $new_record = true;
                } else {
                    //未超时修改
                    $new_record = false;
                }
            }else{
                //没找到，新建
                $new_record = true;
            }
            $this_active_time = 0; //时间增量
            if ($new_record) {
                #新建
                $newFrame = new UserOperationFrame();
                #最小思考时间 MIN_INTERVAL
                $newFrame->id = app('snowflake')->id();
                $newFrame->user_id = $user['user_id'];
                $newFrame->op_start = $currTime - MIN_INTERVAL;
                $newFrame->op_end = $currTime;

                $newFrame->duration = MIN_INTERVAL;
                $newFrame->hit = 1;
                $newFrame->timezone = $client_timezone;
                $newFrame->save();
                $this_active_time = MIN_INTERVAL;
            } else {
                $this_active_time = $currTime - $last->op_end;
                #修改
                $last->op_end = $currTime;
                $last->duration = $currTime - $start_time;
                $last->hit = $last->hit + 1;
                $last->save();
            }

            #更新经验总量表
            #计算客户端日期 unix时间戳 以毫秒计
            $client_currtime = $currTime + $client_timezone;
            $client_date = strtotime(gmdate("Y-m-d", $client_currtime / 1000)) * 1000;

            #查询是否存在
            $daily = UserOperationDaily::where("user_id",$user['user_id'])->where("date_int",$client_date)->first();
            if ($daily) {
                #更新
                $daily->duration = $daily->duration + $this_active_time;
                $daily->hit = $daily->hit + 1;
                $daily->save();
            } else {
                #新建
                $daily = new UserOperationDaily();
                $daily->id = app('snowflake')->id();
                $daily->user_id = $user['user_id'];
                $daily->date_int = $client_date;
                $daily->duration = MIN_INTERVAL;
                $daily->hit = 1;
                $daily->save();
            }
            #更新经验总量表结束
        }

        return $response;
    }
}
