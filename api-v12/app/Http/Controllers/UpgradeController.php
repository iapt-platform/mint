<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUpgradeRequest;
use App\Http\Requests\UpdateUpgradeRequest;
use App\Models\Upgrade;

class UpgradeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
