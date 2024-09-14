<?php

namespace App\Http\Controllers;

use App\Models\DhammaTerm;
use Illuminate\Http\Request;
use App\Http\Resources\TermVocabularyResource;
use App\Http\Api\ChannelApi;

class TermVocabularyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $table = DhammaTerm::select(['guid','word','tag','meaning','other_meaning']);
        switch ($request->get('view')) {
            case "grammar":
                $localTerm = ChannelApi::getSysChannel(
                    "_System_Grammar_Term_".strtolower($request->get('lang'))."_",
                    "_System_Grammar_Term_en_"
                );
                if(!$localTerm){
                    return $this->error('no term channel');
                }
                $table = $table->where('channal',$localTerm);
                break;
            case "studio":
                break;
            case "user":
                break;
            case "community":
                $localTerm = ChannelApi::getSysChannel(
                    "_community_term_".strtolower($request->get('lang'))."_",
                    "_community_term_en_"
                );
                if(!$localTerm){
                    return $this->error('no term channel');
                }
                $table = $table->where('channal',$localTerm);
                break;
        }
        $result = $table->get();
        return $this->ok(["rows"=>TermVocabularyResource::collection($result),'count'=>count($result)]);
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
     * @param  \App\Models\DhammaTerm  $dhammaTerm
     * @return \Illuminate\Http\Response
     */
    public function show(DhammaTerm $dhammaTerm)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DhammaTerm  $dhammaTerm
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DhammaTerm $dhammaTerm)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DhammaTerm  $dhammaTerm
     * @return \Illuminate\Http\Response
     */
    public function destroy(DhammaTerm $dhammaTerm)
    {
        //
    }
}
