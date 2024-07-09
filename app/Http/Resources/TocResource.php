<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ProgressChapter;

class TocResource extends JsonResource
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
            "pali_title"=> $this->toc,
            "level"=>$this->level
        ];

        $title= ProgressChapter::where('book',$this->book)
                                        ->where('para',$this->paragraph)
                                        ->where('lang','zh')
                                        ->whereNotNull('title')
                                        ->value('title');
        if(!empty($title)){
            $data['title'] = $title;
        }
        if($request->has('channels')){
            if(strpos($request->get('channels'),',') ===FALSE){
                $channels = explode('_',$request->get('channels'));
            }else{
                $channels = explode(',',$request->get('channels'));
            }
            $title = ProgressChapter::where('book',$this->book)
                                ->where('para',$this->paragraph)
                                ->where('channel_id',$channels[0])
                                ->whereNotNull('title')
                                ->value('title');
            if(!empty($title)){
                $data['title'] = $title;
            }
            //查询完成度
            foreach ($channels as $key => $channel) {
                $progress = ProgressChapter::where('book',$this->book)
                                ->where('para',$this->paragraph)
                                ->where('channel_id',$channel)
                                ->value('progress');
                if($progress){
                    $data['progress'][] = $progress;
                }else{
                    $data['progress'][] = 0;
                }
            }
        }
        return $data;
    }
}
