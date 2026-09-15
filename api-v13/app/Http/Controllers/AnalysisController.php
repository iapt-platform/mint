<?php

namespace App\Http\Controllers;

use App\Models\WbwAnalysis;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AnalysisController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
        $result = WbwAnalysis::selectRaw('d1, data ,count(*) as ct')
            ->where('type', 9)
            ->groupby('d1')
            ->groupby('data')
            ->orderbyRaw('d1,ct desc')
            ->get();

        return view('wbwanalyses', ['data' => $result]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
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
     * @return Response
     */
    public function show(WbwAnalysis $wbwAnalysis)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(WbwAnalysis $wbwAnalysis)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, WbwAnalysis $wbwAnalysis)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(WbwAnalysis $wbwAnalysis)
    {
        //
    }
}
