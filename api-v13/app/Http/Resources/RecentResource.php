<?php

namespace App\Http\Resources;

use App\Http\Api\ChannelApi;
use App\Models\Article;
use App\Models\DhammaTerm;
use App\Models\Sentence;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecentResource extends JsonResource
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
            'id' => $this->id,
            'type' => $this->type,
            'article_id' => $this->article_id,
            'param' => $this->param,
            'updated_at' => $this->updated_at,
        ];
        $title = '';
        switch ($this->type) {
            case 'article':
                $title = Article::where('uid', $this->article_id)->value('title');
                break;
            case 'chapter':
                if (! empty($this->param)) {
                    $param = json_decode($this->param, true);
                    if (isset($param['channel'])) {
                        $channelId = explode('_', $param['channel'])[0];
                    }
                }
                $paliChannel = ChannelApi::getSysChannel('_System_Pali_VRI_');
                if (! isset($channelId)) {
                    $channelId = $paliChannel;
                }
                $para = explode('-', $this->article_id);
                if (count($para) === 2) {
                    $title = Sentence::where('book_id', (int) $para[0])
                        ->where('paragraph', (int) $para[1])
                        ->where('channel_uid', $channelId)
                        ->value('content');
                    if (empty($title)) {
                        $title = Sentence::where('book_id', (int) $para[0])
                            ->where('paragraph', (int) $para[1])
                            ->where('channel_uid', $paliChannel)
                            ->value('content');
                    }
                }

                break;
            case 'term':
                $title = DhammaTerm::where('guid', $this->article_id)->value('word');
                break;
            default:
                $title = $this->article_id;
                break;
        }
        $data['title'] = $title;

        return $data;
    }
}
