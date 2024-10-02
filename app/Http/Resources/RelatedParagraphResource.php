<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\BookTitle;
use App\Models\PaliText;
use App\Models\TagMap;
use App\Models\Tag;
use Illuminate\Support\Facades\Log;

class RelatedParagraphResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $bookTitle = BookTitle::where('sn',$this["book_id"])->first();
        $data = [
            "book"=>$this['book'],
            "para"=> $this['para'],
            "book_title_pali"=> $bookTitle->title,
            'cs6_para'=> $this['cs6_para'],
        ];
        $paliTextUuid = PaliText::where('book',$bookTitle->book)->where('paragraph',$bookTitle->paragraph)->value('uid');
        $tagIds = TagMap::where('anchor_id',$paliTextUuid)->select('tag_id')->get();
        $data['tags'] = Tag::whereIn('id',$tagIds)->select('id','name','color')->get();

        $data['path'] = json_decode(PaliText::where('book',$this['book'])
                                        ->where('paragraph',$this['para'][0])
                                        ->value('path'));
        return $data;
    }
}
