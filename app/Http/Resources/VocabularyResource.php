<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use App\Tools\RedisClusters;
use App\Http\Controllers\DictMeaningController;

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
        $dictMeaning = new DictMeaningController();
        return [
            "word"=>$this['word'],
            "count"=> $this['count'],
            "strlen"=> $this['strlen'],
            "meaning"=> $dictMeaning->get($this['word'],$request->get("lang","zh-Hans")),
        ];
    }
}
