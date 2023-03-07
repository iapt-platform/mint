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
            return $this->error(__('auth.failed'));
        }

        $method = $request->method();
        $api = substr($request->path(),7);
        switch ($api) {
            case 'dict':
                $newLog = [
                    "op_type_id"=>30,
                    "op_type"=>"dict_lookup",
                    "content"=>$request->get("word")
                ];
                break;
            default:
                # code...
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
