<?php

namespace App\Services;

use App\Http\Resources\AiModelResource;
use App\Models\AiModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class AIModelService
{
    public function getModelsById($id)
    {
        // 添加表存在检查
        if (! Schema::hasTable('ai_models')) {
            return [];
        }

        $table = AiModel::whereIn('uid', $id);
        $result = $table->get();

        return AiModelResource::collection(resource: $result);
    }

    public function getModelById($id)
    {
        // 添加表存在检查
        if (! Schema::hasTable('ai_models')) {
            return [];
        }
        $result = AiModel::where('uid', $id)
            ->first();

        return new AiModelResource(resource: $result);
    }

    public function getSysModels($type = null)
    {
        // 添加表存在检查
        if (! Schema::hasTable('ai_models')) {
            return [];
        }
        if (empty($type)) {
            $types = ['wbw', 'chat', 'summarize'];
        } else {
            $types = [$type];
        }

        $sysModels = [];
        foreach ($types as $key => $type) {
            $sysModels[$type] = $this->getModelsById(Cache::get('/ai/model/system/'.$type) ?? []);
        }
        if (! empty($type)) {
            return $sysModels[$type];
        }

        return $sysModels;
    }
}
