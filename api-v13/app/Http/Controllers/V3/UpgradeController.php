<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpgradeRequest;
use App\Http\Requests\UpdateUpgradeRequest;
use App\Http\Resources\V3\BaseResource;
use App\Models\Upgrade;

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

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUpgradeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Upgrade $upgrade)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUpgradeRequest $request, Upgrade $upgrade)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Upgrade $upgrade)
    {
        //
    }
}
