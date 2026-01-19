<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Api\UserApi;
use App\Http\Api\StudioApi;
use App\Models\Collection;
use App\Models\Channel;
use App\Models\CourseMember;
use App\Http\Api\AuthApi;
use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;

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
            "sign_up_message"=> $this->sign_up_message,
            "teacher"=> UserApi::getByUuid($this->teacher),
            "course_count"=>10,
            "publicity"=> $this->publicity,
            "start_at"=> $this->start_at,
            "end_at"=> $this->end_at,
            "sign_up_start_at"=> $this->sign_up_start_at,
            "sign_up_end_at"=> $this->sign_up_end_at,
            "content"=> $this->content,
            "content_type"=> $this->content_type,
            "cover"=> $this->cover,
            "channel_id"=>$this->channel_id,
            "join"=> $this->join,
            "number"=> $this->number,
            "request_exp"=> $this->request_exp,
            "studio" => StudioApi::getById($this->studio_id),
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at,
        ];
        $data['member_count'] = CourseMember::where('course_id',$this->id)
                                            ->where('is_current',true)->count();

        $data['members'] = CourseMember::where('course_id',$this->id)
                                        ->where('is_current',true)
                                        ->select(['role','status'])
                                        ->get();
        $user = AuthApi::current($request);
        if($user){
            $data['my_role'] = CourseMember::where('course_id',$this->id)
                                            ->where('is_current',true)
                                            ->where('user_id',$user['user_uid'])
                                            ->value('role');
        }

        if($this->cover){
            $thumb = str_replace('.jpg','_m.jpg',$this->cover);
            if (App::environment('local')) {
                $data['cover_url'] = [Storage::url($this->cover),Storage::url($thumb)];
            }else{
                $data['cover_url'] = [
                    Storage::temporaryUrl($this->cover, now()->addDays(6)),
                    Storage::temporaryUrl($thumb, now()->addDays(6)),
                ];
            }
        }
        if(Str::isUuid($this->cover)){
            $coverId = Attachment::find($this->cover);
            $cover_url = [];
            if($coverId){
                $cover_url[] = Storage::disk('public')->url($coverId->bucket.'/'.$coverId->name);
                $cover_url[] = Storage::disk('public')->url($coverId->bucket.'/'.$coverId->id.'_s.jpg');
                $cover_url[] = Storage::disk('public')->url($coverId->bucket.'/'.$coverId->id.'_m.jpg');
                $cover_url[] = Storage::disk('public')->url($coverId->bucket.'/'.$coverId->id.'_l.jpg');
                $data['cover_url'] = $cover_url;
            }
        }

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

        if($request->get('view') === "study" || $request->get('view') === "teach"){
            $user = AuthApi::current($request);
            if($user){
                $course_member = CourseMember::where('user_id',$user["user_uid"])
                                    ->where('course_id',$this->id)
                                    ->where('is_current',true)
                                    ->first();
                if($course_member){
                    $data['my_status'] = $course_member->status;
                    $data['my_status_id'] = $course_member->id;
                }
            }
        }else{
            //计算待审核
            $data['count_progressing'] = CourseMember::where('course_id',$this->id)
                                                ->where('status',"invited")
                                                ->where('is_current',true)
                                                ->count();
        }

        return $data;
    }
}
