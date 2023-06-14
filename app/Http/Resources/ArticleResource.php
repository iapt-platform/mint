<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Api\MdRender;
use App\Models\CourseMember;
use App\Models\Course;
use App\Models\Collection;
use App\Models\ArticleCollection;
use Illuminate\Support\Facades\Log;
use App\Http\Api\UserApi;
use App\Http\Api\StudioApi;
use App\Http\Api\AuthApi;
use App\Http\Controllers\ArticleController;

class ArticleResource extends JsonResource
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
            "uid" => $this->uid,
            "title" => $this->title,
            "subtitle" => $this->subtitle,
            "summary" => $this->summary,
            "studio"=> StudioApi::getById($this->owner),
            "editor"=> UserApi::getById($this->editor_id),
            "status" => $this->status,
            "lang" => $this->lang,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
        $user = AuthApi::current($request);
        if($user){
            $canEdit = ArticleController::userCanEdit($user['user_uid'],$this);
            if($canEdit){
                $data['role'] = 'editor';
            }
        }

        //查询文集
        $collectionCount = ArticleCollection::where('article_id',$this->uid)->count();
        if($collectionCount>0){
            $data['anthology_count'] = $collectionCount;
            $collection = ArticleCollection::where('article_id',$this->uid)->first();
            $data['anthology_first'] = Collection::find($collection->collect_id);
        }
        if(isset($this->content) && !empty($this->content)){
            if($request->has('channel')){
                $channel = $request->get('channel');
            }else{
                $channel = '';
            }
            $data["content"] = $this->content;
            $data["content_type"] = $this->content_type;
            $query_id = null;
            if($request->has('course')){
                if($request->has('exercise')){
                    $query_id = $request->get('exercise');
                    if($request->has('user')){
                        /**
                         * 显示指定用户作业
                         * 查询用户在课程中的channel
                         */
                        $userId = UserApi::getIdByName($request->get('user'));

                        $userInCourse = CourseMember::where('course_id',$request->get('course'))
                                    ->where('user_id',$userId)
                                    ->first();
                        if($userInCourse){
                            $channel = $userInCourse->channel_id;
                        }
                    }else if($request->get('view')==="answer"){
                        /**
                         * 显示答案
                         * 算法：查询course 答案 channel
                         */
                        $channel = Course::where('id',$request->get('course'))->value('channel_id');
                    }else{
                        //显示答案
                        $channel = Course::where('id',$request->get('course'))->value('channel_id');
                    }
                }else{
                    $channel = Course::where('id',$request->get('course'))->value('channel_id');
                }
            }
            if($request->has('mode')){
                $mode = $request->get('mode');
            }else{
                $mode = 'read';
            }
            $data["html"] = MdRender::render($this->content,$channel,$query_id,$mode);
        }
        return $data;
    }
}
