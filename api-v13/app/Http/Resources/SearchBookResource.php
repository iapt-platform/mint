<?php

namespace App\Http\Resources;

use App\Models\BookTitle;
use App\Models\PaliText;
use App\Models\TagMap;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class SearchBookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $book = BookTitle::where('sn', $this->pcd_book_id)->first();
        $data = [
            'pcdBookId' => $this->pcd_book_id,
            'count' => $this->co,
        ];
        if ($book) {
            $toc = PaliText::where('book', $book->book)
                ->where('paragraph', $book->paragraph)
                ->value('toc');
            $data['book'] = $book->book;
            $data['paragraph'] = $book->paragraph;
            $data['paliTitle'] = $toc;
            // tags
            $data['tags'] = $this->getTags($book->book, $book->paragraph);
        } else {
            Log::error('book title is null pcd_book_id='.$this->pcd_book_id);
            $data['book'] = 0;
            $data['paragraph'] = 0;
            $data['paliTitle'] = '';
        }

        return $data;
    }

    private function getTags(string $book, string $para)
    {
        $uid = PaliText::where('book', $book)->where('paragraph', $para)->first()->uid;
        $tagsName = TagMap::where('table_name', 'pali_texts')
            ->where('anchor_id', $uid)
            ->join('tags', 'tag_maps.tag_id', '=', 'tags.id')
            ->select('tags.name')
            ->get();

        return $tagsName;
    }
}
