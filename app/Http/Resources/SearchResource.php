<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\PaliText;
use App\Models\Sentence;
use App\Http\Api\ChannelApi;
use Illuminate\Support\Facades\Cache;
use App\Tools\RedisClusters;


class SearchResource extends JsonResource
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
            "book"=>$this->book,
            "paragraph"=> $this->paragraph,
        ];
        if(isset($this->rank)){
            $data["rank"] = $this->rank;
        }
        $paliText = PaliText::where('book',$this->book)
                            ->where('paragraph',$this->paragraph)
                            ->first();
        if(isset($this->highlight)){
            $data["highlight"] = $this->highlight;
        }else if(isset($this->content)){
            $data["content"] = $this->content;
        }else{
            $channelId = RedisClusters::remember('_System_Pali_VRI_',config('mint.cache.expire'),function(){
                return ChannelApi::getSysChannel('_System_Pali_VRI_');
            });

            $paraContent = Sentence::where('book_id',$this->book)
                ->where('paragraph',$this->paragraph)
                ->where('channel_uid',$channelId)
                ->orderBy('word_start')
                ->select('content')
                ->get();
            $html = '';
            foreach ($paraContent as $key => $value) {
                $html .= $value->content;
            }
            $data["content"] = $html;
        }

        if($paliText){
            $data['path'] = json_decode($paliText->path);
            if($paliText->level<100){
                $data["paliTitle"] = $paliText->toc;
            }else{
                $data["paliTitle"] = PaliText::where('book',$this->book)
                                            ->where('paragraph',$paliText->parent)
                                            ->value('toc');
            }

        }
        return $data;
    }
}
