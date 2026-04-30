<?php

namespace App\Services;

use App\Models\Discussion;
use Illuminate\Support\Facades\Hash;
use App\Http\Api\Mq;
use App\Http\Resources\DiscussionResource;

class DiscussionService
{
    public function create(array $data): Discussion
    {
        if (isset($data['parent'])) {
            $parentInfo = Discussion::find($data['parent']);
            if (!$parentInfo) {
                throw new \Exception('没有找到parent', 500);
            }
            $data['res_id '] = $parentInfo->res_id;
            $data['res_type'] = $parentInfo->res_type;
        }
        $discussion = Discussion::create($data);
        //更新parent children_count
        if (isset($data['parent'])) {
            $parentInfo->increment('children_count', 1);
            $parentInfo->save();
        }
        if (isset($data['notification']) && $data['notification'] == 'true') {
            Mq::publish('discussion', new DiscussionResource($discussion));
        }
        return $discussion;
    }
}
