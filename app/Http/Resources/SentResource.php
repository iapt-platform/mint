<?php

namespace App\Http\Resources;

use App\Http\Api\MdRender;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Api\StudioApi;
use App\Http\Api\UserApi;
use App\Http\Api\ChannelApi;
use App\Http\Api\SuggestionApi;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $channel = ChannelApi::getById($this->channel_uid);
        if(!$channel){
            Log::error('channel left',['data'=>$this->channel_uid,'uid'=>$this->uid]);
        }
        if($request->get('mode','read')==="read"){
            $mode = 'read';
        }else{
            $mode = 'edit';
        }
        $data = [
                "id" => $this->uid,
                "content"=>$this->content,
                "content_type"=>$this->content_type,
                "html"=> "",
                "book"=> $this->book_id,
                "paragraph"=> $this->paragraph,
                "word_start"=> $this->word_start,
                "word_end"=> $this->word_end,
                "editor"=> UserApi::getByUuid($this->editor_uid),
                'fork_at' => $this->fork_at,
                "updated_at"=> $this->updated_at,
            ];

        if($channel){
            $data['channel'] = $channel;
            $data['studio'] = StudioApi::getById($channel["studio_id"]);
        }
        if($request->has('channels')){
            $channels = explode(',',$request->get('channels'));
        }else{
            $channels = [$this->channel_uid];
        }
        //TODO 找出channel id = '' 的原因
        $mChannels=array();
        foreach ($channels as $key => $value) {
            if(Str::isUuid($value)){
                $mChannels[] = $value;
            }
        }
        if($request->get('html',true)){
            $data['html'] = MdRender::render($this->content,
                                             $mChannels,
                                             null,
                                             $mode,
                                             $channel['type'],
                                             $this->content_type,
                                             $request->get('format','react')
                                            );
        }
        if($request->get('mode') === "edit" || $request->get('mode') === "wbw"){
            $data['suggestionCount'] = SuggestionApi::getCountBySent($this->book_id,
                                                                   $this->paragraph,
                                                                   $this->word_start,
                                                                   $this->word_end,
                                                                   $this->channel_uid
                                                                );
        }
        if(isset($this->acceptor_uid) && !empty($this->acceptor_uid)){
            $data["acceptor"]=UserApi::getByUuid($this->acceptor_uid);
            $data["pr_edit_at"]=$this->pr_edit_at;
        }
        return $data;
    }
}
