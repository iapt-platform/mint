<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\PaliText;

class SearchTitleResource extends JsonResource
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
            'content'=>$this->text,
            'path' => json_decode($this->path),
            'paliTitle' => $this->toc,
        ];
        $data["content"] = PaliText::where('book',$this->book)
                                            ->where('paragraph',$this->paragraph+1)
                                            ->value('html');
        $data["content_type"] = 'html';
        return $data;
    }
}
