<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Api\UserApi;
use App\Http\Api\StudioApi;
use App\Models\Collection;
use App\Models\Channel;
use App\Models\CourseMember;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [
            "id"=>$this->id,
            "title"=> $this->title,
            "subtitle"=> $this->subtitle,
            "summary"=> $this->summary,
            "teacher"=> UserApi::getById($this->teacher),
            "course_count"=>10,
            "member_count"=>CourseMember::where('course_id',$this->id)->count(),
            "publicity"=> $this->publicity,
            "start_at"=> $this->start_at,
            "end_at"=> $this->end_at,
            "content"=> $this->content,
            "content_type"=> $this->content_type,
            "cover"=> $this->cover,
            "channel_id"=>$this->channel_id,
            "join"=> $this->join,
            "request_exp"=> $this->request_exp,
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at,
        ];
        $textbook = Collection::where('uid',$this->anthology_id)->select(['uid','title','owner'])->first();
        if($textbook){
            $data['anthology_id'] = $textbook->uid;
            $data['anthology_title'] = $textbook->title;
            $data['anthology_owner'] = StudioApi::getById($textbook->owner);
        }
        if(!empty($this->channel_id)){
            $channel = Channel::where('uid',$this->channel_id)->select(['name','owner_uid'])->first();
            if($channel){
                $data['channel_name'] = $channel->name;
                $data['channel_owner'] = StudioApi::getById($channel->owner_uid);
            }
        }

        return $data;
    }
}
