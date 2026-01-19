<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\GroupMember;
use App\Http\Api\AuthApi;
use App\Http\Api\StudioApi;

class GroupResource extends JsonResource
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
            'uid' => $this->uid,
            'name' => $this->name,
            'description' => $this->description,
            'owner' => $this->owner,
            "studio" => StudioApi::getById($this->owner),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at
        ];
        $user = AuthApi::current($request);
        if($user){
            if($this->owner === $user['user_uid']){
                $data['role'] = 'owner';
            }else{
                $power = GroupMember::where('user_id',$user['user_uid'])
                                      ->where('group_id',$this->uid)
                                      ->value('power');
                switch ($power) {
                    case 0:
                        $data['role'] = 'owner';
                        break;
                    case 1:
                        $data['role'] = 'manager';
                        break;
                    case 2:
                        $data['role'] = 'member';
                        break;
                    default:
                        $data['role'] = 'unknown';
                        break;
                }
            }
        }
        return $data;
    }
}
