<?php

namespace App\Http\Api;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

use App\Models\Sentence;
use App\Models\SentHistory;
use App\Models\Channel;

use App\Http\Api\ShareApi;

class SentenceApi{
    protected $auth = false;
    protected $channel = null;

    public function auth($channelId,$userId){
        $channel = Channel::where('uid',$channelId)->first();
        if(!$channel){
            return false;
        }
        if($channel->owner_uid !== $userId){
            //判断是否为协作
            $power = ShareApi::getResPower($userId,$channel->uid,2);
            if($power < 20){
                return false;
            }
        }
        $this->channel = $channel;
        $this->auth = true;
        return true;
    }
    public function store($sent,$user,$copy=false){
        $row = Sentence::firstOrNew([
            "book_id"=>$sent['book_id'],
            "paragraph"=>$sent['paragraph'],
            "word_start"=>$sent['word_start'],
            "word_end"=>$sent['word_end'],
            "channel_uid"=>$this->channel->uid,
        ],[
            "id"=>app('snowflake')->id(),
            "uid"=>Str::uuid(),
        ]);
        $row->content = $sent['content'];
        $row->strlen = mb_strlen($sent['content'],"UTF-8");
        $row->language = $this->channel->lang;
        $row->status = $this->channel->status;
        if($copy){
            //复制句子，保留原作者信息
            $row->editor_uid = $sent["editor_uid"];
            $row->acceptor_uid = $user["user_uid"];
            $row->pr_edit_at = $sent["updated_at"];
        }else{
            $row->editor_uid = $user["user_uid"];
            $row->acceptor_uid = null;
            $row->pr_edit_at = null;
        }
        $row->create_time = time()*1000;
        $row->modify_time = time()*1000;
        $row->save();

        //保存历史记录
        if($copy){
            $this->saveHistory($row->uid,$sent["editor_uid"],$sent['content']);
        }else{
            $this->saveHistory($row->uid,$user["user_uid"],$sent['content']);
        }
        //清除缓存
        $sentId = "{$sent['book_id']}-{$sent['paragraph']}-{$sent['word_start']}-{$sent['word_end']}";
        $hKey = "/sentence/res-count/{$sentId}/";
        Redis::del($hKey);
    }

    private function saveHistory($uid,$editor,$content){
        $newHis = new SentHistory;
        $newHis->id = app('snowflake')->id();
        $newHis->sent_uid = $uid;
        $newHis->user_uid = $editor;
        if(empty($content)){
            $newHis->content = "";
        }else{
            $newHis->content = $content;
        }

        $newHis->create_time = time()*1000;
        $newHis->save();
    }
}
