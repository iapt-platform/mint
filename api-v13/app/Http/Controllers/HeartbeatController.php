<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HeartbeatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        //
        if (file_exists(base_path('.stop'))) {
            $status = 503;
        } else {
            $status = 200;
        }

        return response()->json(
            ['createdAt' => now()],
            $status,
            [
                'Content-Type' => 'application/json;charset=UTF-8',
                'Charset' => 'utf-8',
            ],
            JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
