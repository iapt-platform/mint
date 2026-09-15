<?php

namespace App\Http\Resources;

use App\Http\Api\ChannelApi;
use App\Http\Api\StudioApi;
use App\Models\ArticleCollection;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionResource extends JsonResource
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
            'uid' => $this->uid,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'summary' => $this->summary,
            'studio' => StudioApi::getById($this->owner),
            'childrenNumber' => ArticleCollection::where('collect_id', $this->uid)->count(),
            'status' => $this->status,
            'lang' => $this->lang,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        $channel = ChannelApi::getById($this->default_channel);
        if ($channel) {
            $data['default_channel'] = $channel;
        }
        $arrList = ArticleCollection::where('collect_id', $this->uid)
            ->select(['article_id', 'level', 'title'])
            ->orderBy('id')->get()->toArray();
        if ($this->fullArticleList === true) {
            $data['article_list'] = $arrList;
        } else {
            $data['article_list'] = array_slice($arrList, 0, 4);
        }

        return $data;
    }
}
