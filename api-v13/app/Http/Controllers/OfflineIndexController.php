<?php

namespace App\Http\Controllers;

use App\Services\PacketService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OfflineIndexController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $index = app(PacketService::class)->index($request->input('file', null));

        return response()->json(
            $index,
            200,
            [
                'Content-Type' => 'application/json;charset=UTF-8',
                'Charset' => 'utf-8',
            ],
            JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $filename
     * @return Response
     */
    public function show($filename) {}

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
