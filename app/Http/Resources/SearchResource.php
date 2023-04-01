<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\PaliText;

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
            "content"=> $this->content,
            "rank"=> $this->rank,
            "highlight"=> $this->highlight,
        ];

        $paliText = PaliText::where('book',$this->book)
                            ->where('paragraph',$this->paragraph)
                            ->first();
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
