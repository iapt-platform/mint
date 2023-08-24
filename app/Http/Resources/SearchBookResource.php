<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\BookTitle;
use App\Models\PaliText;

class SearchBookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $book = BookTitle::where('sn',$this->pcd_book_id)->first();
        if($book){
            $toc = PaliText::where('book',$book->book)->where("paragraph",$book->paragraph)->value('toc');
            return [
                "book"=>$book->book,
                "paragraph"=> $book->paragraph,
                "paliTitle"=> $toc,
                'pcdBookId'=>$this->pcd_book_id,
                "count"=>$this->co,
            ];
        }else{
            Log::error('book title is null');
            Log::error($this);
        }

    }
}
