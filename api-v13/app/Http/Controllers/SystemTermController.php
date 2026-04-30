<?php

namespace App\Http\Controllers;

use App\Models\DhammaTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SystemTermController extends Controller
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
    public function show(string $lang,string $word)
    {
        //
        $url = "https://staging.wikipali.org/api/v2/channel-name/_System_Grammar_Term_{$lang}_";
        $response = Http::get($url);
		if($response->successful()){
            $channelId = $response['data']['uid'];
            $term = DhammaTerm::where('channal',$channelId)
                            ->where('tag',':abbr:')
                            ->where('word',$word)
                            ->first();
            if($term){
                return $this->ok($term);
            }else{
                return $this->error('no term');
            }
        }else{
            return $this->error('no channel');
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
