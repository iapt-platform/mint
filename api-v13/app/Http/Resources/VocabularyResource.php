<?php

namespace App\Http\Resources;

use App\Http\Controllers\DictMeaningController;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VocabularyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $dictMeaning = new DictMeaningController;

        return [
            'word' => $this['word'],
            'count' => $this['count'],
            'strlen' => $this['strlen'],
            'meaning' => $dictMeaning->get($this['word'], $request->input('lang', 'zh-Hans')),
        ];
    }
}
