<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class VocabularyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $key = "dict_first_mean/";
        $meaning = Cache::get($key."zh-Hans/{$this['word']}");
        if(empty($meaning)){
            $meaning = Cache::get($key."com/{$this['word']}");
        }
        return [
            "word"=>$this['word'],
            "count"=> $this['count'],
            "meaning"=> $meaning,
        ];
    }
}
