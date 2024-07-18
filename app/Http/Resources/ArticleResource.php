<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use App\Models\CourseMember;
use App\Models\Course;
use App\Models\Collection;
use App\Models\ArticleCollection;
use App\Models\Channel;

use App\Http\Controllers\ArticleController;

use App\Http\Api\MdRender;
use App\Http\Api\UserApi;
use App\Http\Api\StudioApi;
use App\Http\Api\AuthApi;
use App\Http\Api\ChannelApi;


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
            "parent_uid" => $this->parent,
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

        //查询该文章在哪些文集中出现
        $collectionCount = ArticleCollection::where('article_id',$this->uid)->count();
        if($collectionCount>0){
            $data['anthology_count'] = $collectionCount;
            $collection = ArticleCollection::where('article_id',$this->uid)->first();
            $data['anthology_first'] = Collection::find($collection->collect_id);
        }
        if($request->has('anthology') && Str::isUuid($request->get('anthology'))){
            $anthology = Collection::where('uid',$request->get('anthology'))->first();
        }
        //渲染简化版标题
        $channels = [];
        if($request->has('channel')){
            //有channel
            $channels = explode('_',$request->get('channel')) ;
        }else if(isset($anthology) && $anthology && !empty($anthology->default_channel)){
            //没有channel,使用文集channel
            $channels[] = $anthology->default_channel;
        }
        $mdRender = new MdRender(['format'=>'simple']);

        //path
        if($request->has('anthology') && Str::isUuid($request->get('anthology'))){
            $data['path'] = array();
            if(isset($anthology) && $anthology){
                $data['path'][] = ['key'=>$anthology->uid,
                            'title'=>$anthology->title,
                            'level'=>0];
            }

            $currLevel = -1;
            $aList = ArticleCollection::where('collect_id',$request->get('anthology'))
                                                ->orderBy('id','desc')
                                                ->select(['article_id','title','level'])->get();
            $path = array();
            foreach ($aList as $article) {
                if($article->article_id === $this->uid ||
                ($currLevel >= 0 && $article->level < $currLevel)){
                    $currLevel = $article->level;
                    $path[] = ['key'=>$article->article_id,
                                'title'=>$mdRender->convert($article->title,$channels),
                                'level'=>$article->level];
                }
            }
            for ($i=count($path)-1; $i >=0 ; $i--) {
                $data['path'][] = $path[$i];
            }

            //下级目录
            $level = -1;
            $subToc = array();
            for ($i=count($aList)-1; $i >=0 ; $i--) {
                $article = $aList[$i];
                if($level>=0){
                    if($article->level>$level){
                        $subToc[] =[
                            "key"=>$article->article_id,
                            "title"=>$mdRender->convert($article->title,$channels),
                            "level"=>$article->level
                        ];
                    }else{
                        break;
                    }
                }
                if($article->article_id === $this->uid){
                    $level = $article->level;
                }
            }
            $data['toc'] = $subToc;
        }


        $data['title_text'] = $mdRender->convert($this->title,$channels);

        //render html
        $channels = array();
        if(isset($this->content) && !empty($this->content)){
            if($request->has('channel')){
                $channels = explode('_',$request->get('channel')) ;
            }else if($request->has('anthology')){
                $defaultChannel = Collection::where('uid',$request->get('anthology'))
                                    ->value('default_channel');
                if($defaultChannel){
                    $channels[] = $defaultChannel;
                }
            }
            if(count($channels) === 0){
                //查找用户默认channel
                $studioChannel = Channel::where('owner_uid',$this->owner)
                                        ->where('type','translation')
                                        ->get();
                if($studioChannel){
                    $channelId = $studioChannel[0]->uid;
                    $channels = [$channelId];
                }else{
                    $channelId = ChannelApi::getSysChannel('_community_translation_'.strtolower($this->lang).'_',
                                                        '_community_translation_en_');
                    if($channelId){
                        $channels = [$channelId];
                    }
                }
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
                            $channelId = $userInCourse->channel_id;
                            $channels = [$channelId];
                        }
                    }else if($request->get('view')==="answer"){
                        /**
                         * 显示答案
                         * 算法：查询course 答案 channel
                         */
                        $channelId = Course::where('id',$request->get('course'))->value('channel_id');
                        $channels = [$channelId];
                    }else{
                        //显示答案
                        $channelId = Course::where('id',$request->get('course'))->value('channel_id');
                        $channels = [$channelId];
                    }
                }else{
                    $channelId = Course::where('id',$request->get('course'))->value('channel_id');
                    $channels = [$channelId];
                }
            }

            $mode = $request->get('mode','read');
            $format = $request->get('format','react');

            $html = MdRender::render($this->content,
                                     $channels,$query_id,$mode,
                                     'translation','markdown',$format);
            //Log::debug('article render',['content'=>$this->content,'format'=>$format,'html'=>$html]);

            $data["html"] = $html;
            if(empty($this->summary)){
                $data["_summary"] = MdRender::render($this->content,
                                                    $channels,$query_id,$mode,
                                                    'translation','markdown','text');
            }
        }
        return $data;
    }
}
