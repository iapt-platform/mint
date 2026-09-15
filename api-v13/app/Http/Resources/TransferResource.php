<?php

namespace App\Http\Resources;

use App\Http\Api\ChannelApi;
use App\Http\Api\StudioApi;
use App\Http\Api\UserApi;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransferResource extends JsonResource
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
            'origin_owner' => StudioApi::getById($this->origin_owner),
            'res_type' => $this->res_type,
            'res_id' => $this->res_id,
            'transferor' => UserApi::getByUuid($this->transferor_id),
            'new_owner' => StudioApi::getById($this->new_owner),
            'status' => $this->status,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
        if (! empty($this->editor_id)) {
            $data['editor'] = UserApi::getByUuid($this->editor_id);
        }
        switch ($this->res_type) {
            case 'channel':
                $data['channel'] = ChannelApi::getById($this->res_id);
                break;
            default:
                // code...
                break;
        }

        return $data;
    }
}
