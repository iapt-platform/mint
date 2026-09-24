<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Resources\V3\BaseResource;

class UpgradeController extends Controller
{
    /**
     * 客户端升级检查
     *
     * 目前只回报服务可用，尚未接入版本比对逻辑。
     *
     * @unauthenticated
     */
    public function index()
    {
        //
        return BaseResource::make(['status' => 'ok']);
    }
}
