<?php

namespace App\Services;

use App\Models\AiModel;
use App\Http\Resources\AiModelResource;
use Illuminate\Support\Facades\Cache;

class AIModelService
{

    public function getModelsById($id)
    {
        $table = AiModel::whereIn('uid', $id);
        $result = $table->get();
        return AiModelResource::collection(resource: $result);
    }
    public function getModelById($id)
    {
        $result = AiModel::where('uid', $id)
            ->first();
        return new AiModelResource(resource: $result);
    }

    public function getSysModels($type = null)
    {
        if (empty($type)) {
            $types = ['wbw', 'chat', 'summarize'];
        } else {
            $types = [$type];
        }

        $sysModels = [];
        foreach ($types as $key => $type) {
            $sysModels[$type] =  $this->getModelsById(Cache::get('/ai/model/system/' . $type) ?? []);
        }
        if (!empty($type)) {
            return $sysModels[$type];
        }
        return $sysModels;
    }
}
