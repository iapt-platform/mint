<?php

namespace App\Http\Controllers;

use App\Models\DhammaTerm;
use Illuminate\Http\Request;
use App\Http\Api\ChannelApi;

class TermSummaryController extends Controller
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
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show(string $id)
    {
        //
        $term = DhammaTerm::where('guid',$id)->first();
        if(!$term){
            return $this->error('no id');
        }
        if(empty($term->note)){
            $community_channel = ChannelApi::getSysChannel("_community_term_zh-hans_");
                        //查找社区解释
            $note = DhammaTerm::where("word",$term->word)
                                        ->where('channal',$community_channel)
                                        ->value('note');
        }else{
            $note = $term->note;
        }
        #替换术语
        $pattern = "/\[\[(.+?)\]\]/";
        $replacement = '$1';
        $html = preg_replace($pattern,$replacement,$note);

        $pattern = "/\{\{(.+?)\}\}/";
        $replacement = '';
        $html = preg_replace($pattern,$replacement,$html);

        $html = mb_substr($html,0,500,"UTF-8");
        return $this->ok($html);
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
