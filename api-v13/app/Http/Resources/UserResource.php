<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
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
            'id' => $this->userid,
            'userName' => $this->username,
            'nickName' => $this->nickname,
            'email' => $this->email,
            'role' => json_decode($this->role),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        if ($this->avatar) {
            $data['avatarName'] = $this->avatar;
            $img = str_replace('.jpg', '_s.jpg', $this->avatar);
            if (App::environment('local')) {
                $data['avatar'] = Storage::url($img);
            } else {
                $data['avatar'] = Storage::temporaryUrl($img, now()->addDays(6));
            }
        }

        return $data;
    }
}
