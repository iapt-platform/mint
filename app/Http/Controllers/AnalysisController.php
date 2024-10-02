<?php

namespace App\Http\Controllers;

use App\Models\WbwAnalysis;
use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $result = WbwAnalysis::selectRaw('d1, data ,count(*) as ct')
                             ->where('type',9)
                             ->groupby('d1')
                             ->groupby('data')
                             ->orderbyRaw('d1,ct desc')
                             ->get();
        return view('wbwanalyses',['data'=>$result]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WbwAnalysis  $wbwAnalysis
     * @return \Illuminate\Http\Response
     */
    public function show(WbwAnalysis $wbwAnalysis)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WbwAnalysis  $wbwAnalysis
     * @return \Illuminate\Http\Response
     */
    public function edit(WbwAnalysis $wbwAnalysis)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WbwAnalysis  $wbwAnalysis
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WbwAnalysis $wbwAnalysis)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WbwAnalysis  $wbwAnalysis
     * @return \Illuminate\Http\Response
     */
    public function destroy(WbwAnalysis $wbwAnalysis)
    {
        //
    }
}
