<?php

namespace App\Http\Resources;

use App\Models\PaliText;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NavCSParaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [];
        $data['content'] = PaliText::where('book', $this->book)
            ->where('paragraph', $this->paragraph)
            ->value('text');
        $data['book'] = $this->book;
        $data['start'] = $this->paragraph;

        return $data;
    }
}
