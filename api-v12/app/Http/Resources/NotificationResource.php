<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

use App\Models\SentPr;
use App\Models\Wbw;
use App\Models\Discussion;
use App\Models\Sentence;

use App\Http\Api\UserApi;
use App\Http\Api\PaliTextApi;
use App\Http\Api\ChannelApi;

class NotificationResource extends JsonResource
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
            "id" => $this->id,
            "from" => UserApi::getByUuid($this->from),
            "to" => UserApi::getByUuid($this->to),
            "url" => $this->url,
            "content" => $this->content,
            "content_type" => $this->content_type,
            "res_type" => $this->res_type,
            "res_id" => $this->res_id,
            "status" => $this->status,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
        $data['channel'] = ChannelApi::getById($this->channel);
        switch ($this->res_type) {
            case 'suggestion':
                $prData = SentPr::where('uid', $this->res_id)->first();
                if ($prData) {
                    $link = config('mint.server.dashboard_base_path') . "/article/para/{$prData->book_id}-{$prData->paragraph}";
                    $link .= "?book={$prData->book_id}&par={$prData->paragraph}&channel={$prData->channel_uid}";
                    $link .= "&mode=edit&pr=" . $this->res_id;
                    $data['url'] = $link;
                    //获取标题
                    if ($prData->book_id < 1000) {
                        $path = json_decode(PaliTextApi::getChapterPath($prData->book_id, $prData->paragraph));
                        if (count($path) > 0) {
                            $data['title'] = end($path)->title;
                            $data['book_title'] = $path[0]->title;
                        } else {
                            Log::error('no path data', ['pr data' => $prData]);
                        }
                        //内容
                        $orgContent = Sentence::where('book_id', $prData->book_id)
                            ->where('paragraph', $prData->paragraph)
                            ->where('word_start', $prData->word_start)
                            ->where('word_end', $prData->word_end)
                            ->where('channel_uid', $prData->channel_uid)
                            ->value('content');
                        $content = '>' . mb_substr($orgContent, 0, 70, "UTF-8") . "\n\n";
                        $content .= mb_substr($prData->content, 0, 140, "UTF-8");
                        $data['content'] = $content;
                    }
                }
                break;
            case 'discussion':
                $discussion = Discussion::where('id', $this->res_id)->first();
                if ($discussion->parent) {
                    $topic = Discussion::where('id', $discussion->parent)->first();
                }
                if ($discussion) {
                    $link = config('mint.server.dashboard_base_path') . '/discussion/topic/';
                    if (isset($topic)) {
                        $link .= "{$topic->id}#{$discussion->id}";
                    } else {
                        $link .= "{$discussion->id}";
                    }
                    $data['url'] = $link;
                    //标题
                    switch ($discussion->res_type) {
                        case 'sentence':
                            break;
                        case 'wbw':
                            $wbw = Wbw::where('uid', $discussion->res_id)->first();
                            if ($wbw) {
                                $data['title'] = $wbw->word;
                            }

                            break;
                        default:
                            break;
                    }
                    /*
                        {
                            $path = json_decode(PaliTextApi::getChapterPath($prData->book_id,$prData->paragraph));
                            if(count($path)>0){
                                $data['title'] = end($path)->title;
                                $data['book_title'] = $path[0]->title;
                            }else{
                                Log::error('no path data',['pr data'=>$prData]);
                            }
                            //内容
                            $orgContent = Sentence::where('book_id',$prData->book_id)
                                                    ->where('paragraph',$prData->paragraph)
                                                    ->where('word_start',$prData->word_start)
                                                    ->where('word_end',$prData->word_end)
                                                    ->where('channel_uid',$prData->channel_uid)
                                                    ->value('content');
                            $content = '>'. mb_substr($orgContent,0,70,"UTF-8")."\n\n";
                            $content .= mb_substr($prData->content,0,140,"UTF-8");
                            $data['content'] = $content;
                        }
                        */
                }
                break;
            case 'task':
                $link = config('mint.server.dashboard_base_path') . "/article/task/{$this->res_id}";
                $data['url'] = $link;
                break;
            default:
                # code...
                break;
        }
        return $data;
    }
}
