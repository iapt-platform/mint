<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->uid,
            'chat_id' => $this->chat_id,
            'parent_id' => $this->parent_id,
            'session_id' => $this->session_id,
            'role' => $this->role,
            'content' => $this->content,
            'model_name' => $this->model_name,
            'tool_calls' => $this->tool_calls,
            'tool_call_id' => $this->tool_call_id,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
