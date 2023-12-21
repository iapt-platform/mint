<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Api\UserApi;
use App\Models\SentPr;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [
            "id"=>$this->id,
            "from"=> UserApi::getByUuid($this->from),
            "to"=> UserApi::getByUuid($this->to),
            "url"=> $this->url,
            "content"=> $this->content,
            "content_type"=> $this->content_type,
            "res_type"=> $this->res_type,
            "res_id"=> $this->res_id,
            "status"=> $this->status,
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at,
        ];

        switch ($this->res_type) {
            case 'suggestion':
                $prData = SentPr::where('uid',$this->res_id)->first();
                if($prData){
                    $link = config('app.url')."/pcd/article/para/{$prData->book_id}-{$prData->paragraph}";
                    $link .= "?book={$prData->book_id}&par={$prData->paragraph}&channel={$prData->channel_uid}";
                    $link .= "&mode=edit&pr=".$this->res_id;
                    $data['url'] = $link;
                }

                break;

            default:
                # code...
                break;
        }
        return $data;
    }
}
