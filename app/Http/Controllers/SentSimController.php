<?php

namespace App\Http\Controllers;

use App\Models\SentSim;
use App\Models\PaliSentence;
use Illuminate\Http\Request;
use App\Http\Resources\SentSimResource;

class SentSimController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->get('view')) {
            case 'sentence':
                $sentId = PaliSentence::where('book',$request->get('book'))
                                ->where('paragraph',$request->get('paragraph'))
                                ->where('word_begin',$request->get('start'))
                                ->where('word_end',$request->get('end'))
                                ->value('id');
                if(!$sentId){
                    return $this->error("no sent");
                }
                $table = SentSim::where('sent1',$sentId)
                                ->orderBy('sim','desc');
                break;
        }
        $table->where('sim','>=',$request->get('sim',0));
        $count = $table->count();
        $table->skip($request->get("offset",0))
              ->take($request->get('limit',20));
        $result = $table->get();
        if($result){
            return $this->ok(["rows"=>SentSimResource::collection($result),"count"=>$count]);
        }else{
            return $this->error("no data");
        }
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
     * @param  \App\Models\SentSim  $sentSim
     * @return \Illuminate\Http\Response
     */
    public function show(SentSim $sentSim)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SentSim  $sentSim
     * @return \Illuminate\Http\Response
     */
    public function edit(SentSim $sentSim)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SentSim  $sentSim
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SentSim $sentSim)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SentSim  $sentSim
     * @return \Illuminate\Http\Response
     */
    public function destroy(SentSim $sentSim)
    {
        //
    }
}
