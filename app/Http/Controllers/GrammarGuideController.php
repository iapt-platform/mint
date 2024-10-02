<?php

namespace App\Http\Controllers;

use App\Models\DhammaTerm;
use App\Http\Api\ChannelApi;
use Illuminate\Http\Request;

class GrammarGuideController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
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
     * @param  \App\Models\DhammaTerm  $dhammaTerm
     * @return \Illuminate\Http\Response
     */
    public function show(string $id)
    {
        //
        $param = explode('_',$id);

        $localTermChannel = ChannelApi::getSysChannel(
            "_System_Grammar_Term_".strtolower($param[1])."_",
            "_System_Grammar_Term_en_"
        );
        if(!$localTermChannel){
            return $this->error('no term channel');
        }
        $result = DhammaTerm::where('word',$param[0])
                    ->where('channal',$localTermChannel)->first();

        if($result){
            return $this->ok("# {$result->meaning}\n {$result->note}");
        }else{
            return $this->ok("# {$id}\n no record");
        }

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
