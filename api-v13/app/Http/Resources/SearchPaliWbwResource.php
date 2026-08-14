<?php

namespace App\Http\Resources;

use App\Models\PaliText;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchPaliWbwResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [
            'book' => $this->book,
            'paragraph' => $this->paragraph,
        ];
        if (isset($this->rank)) {
            $data['rank'] = $this->rank;
        }
        $paliText = PaliText::where('book', $this->book)
            ->where('paragraph', $this->paragraph)
            ->first();
        if ($paliText) {
            $data['path'] = json_decode($paliText->path);
            if ($paliText->level < 100) {
                $data['paliTitle'] = $paliText->toc;
            } else {
                $data['paliTitle'] = PaliText::where('book', $this->book)
                    ->where('paragraph', $paliText->parent)
                    ->value('toc');
            }
            $keyWords = explode(',', $request->input('key'));
            $keyWordsUpper = $keyWords;
            foreach ($keyWords as $key => $word) {
                if (mb_substr($word, -3, null, 'UTF-8') === 'nti') {
                    $keyWordsUpper[] = mb_substr($word, 0, mb_strlen($word, 'UTF-8') - 3, 'UTF-8');
                } elseif (mb_substr($word, -3, null, 'UTF-8') === 'ti') {
                    $keyWordsUpper[] = mb_substr($word, 0, mb_strlen($word, 'UTF-8') - 2, 'UTF-8');
                }
            }
            foreach ($keyWords as $key => $word) {
                $keyWordsUpper[] = mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8').mb_substr($word, 1, null, 'UTF-8');
            }
            $keyReplace = [];
            foreach ($keyWordsUpper as $key => $word) {
                $keyReplace[] = "<span class='hl'>{$word}</span>";
            }
            $data['highlight'] = str_replace($keyWordsUpper, $keyReplace, $paliText->html);
        }

        return $data;
    }
}
