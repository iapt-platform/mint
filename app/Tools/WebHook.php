<?php
namespace App\Tools;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WebHook{
        /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {

    }
    public function wechat($url, $title=null, $message=null)
    {

        $param["msgtype"]="markdown";
        if(empty($title) &&  empty($message) ){
            return 0;
        }
        if($title !== null){
            $param["markdown"]['title'] = $title;
        }
        if($message !== null){
            $param["markdown"]['content'] = $message;
        }
        return $this->send($url, $param);
    }
    public function dingtalk($url, $title=null, $message=null){
        $param = array();
        $param["msgtype"]="markdown";
        if(empty($title) &&  empty($message) ){
            return 0;
        }
        if($title !== null){
            $param["markdown"]['title'] = $title;
        }
        if($message !== null){
            $param["markdown"]['text'] = $message;
        }

        return $this->send($url, $param);
    }

    private function send($url, $param){
        Log::info('webhook send');
        Log::info($param);
        try{
            $response = Http::post($url, $param);
            if($response->successful()){
                return 0;
            }else{
                return 1;
            }
        }catch(\Exception $e){
            Log::error('webhook send fail');
            Log::error($e);
            return 1;
        }
        return 0;
    }
}
