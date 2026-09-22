<?php

namespace App\Http\Resources;

use App\Http\Api\StudioApi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChannelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * 类型取自 channels 表的列定义：type / source_id 都是 varchar 而不是数字，
     * progress 是 double。带 ? 的键只在特定查询口径下出现。
     *
     * @param  Request  $request
     * @return array{
     *     uid: string,
     *     name: string,
     *     summary: string|null,
     *     type: string,
     *     studio: array{id: string, nickName: string, realName: string, studioName: string}|false,
     *     lang: string,
     *     is_system: bool,
     *     status: int,
     *     created_at: string,
     *     updated_at: string,
     *     source_type: string|null,
     *     source_id: string|null,
     *     progress?: float,
     *     role?: string
     * }
     */
    public function toArray($request)
    {
        $data = [
            'uid' => $this->uid,
            'name' => $this->name,
            'summary' => $this->summary,
            'type' => $this->type,
            'studio' => StudioApi::getById($this->owner_uid),
            'lang' => $this->lang,
            'is_system' => $this->is_system,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'source_type' => $this->source_type,
            'source_id' => $this->source_id,
        ];
        if (isset($this->source_type)) {
            $data['source_type'] = $this->source_type;
        }
        if (isset($this->source_id)) {
            $data['source_id'] = $this->source_id;
        }
        if (isset($this->progress)) {
            $data['progress'] = $this->progress;
        }
        if (isset($this->role)) {
            $data['role'] = $this->role;
        }

        return $data;
    }
}
