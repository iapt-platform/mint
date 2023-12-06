<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\PaliText;

class NavCSParaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [];
        $data['content'] = PaliText::where('book',$this->book)
                                ->where('paragraph',$this->paragraph)
                                ->value('text');
        $data['book'] = $this->book;
        $data['start'] = $this->paragraph;
        return $data;
    }
}
