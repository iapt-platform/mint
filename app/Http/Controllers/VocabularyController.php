<?php

namespace App\Http\Controllers;

use App\Models\Vocabulary;
use Illuminate\Http\Request;
use App\Http\Resources\VocabularyResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class VocabularyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->get("view")) {
            case 'key':
                $key = $request->get("key");
                $result = Cache::remember("/dict_vocabulary/{$key}",10,function() use($key){
                        return Vocabulary::whereRaw('word like ? or word_en like ?',[$key."%",$key."%"])
                                    ->whereOr('word_en','like',$key."%")
                                    ->orderBy('strlen')
                                    ->orderBy('word')
                                    ->take(10)->get();
                });
                return $this->ok(['rows'=>VocabularyResource::collection($result),'count'=>count($result)]);
                break;
        }
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
     * @param  \App\Models\Vocabulary  $vocabulary
     * @return \Illuminate\Http\Response
     */
    public function show(Vocabulary $vocabulary)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Vocabulary  $vocabulary
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Vocabulary $vocabulary)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Vocabulary  $vocabulary
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vocabulary $vocabulary)
    {
        //
    }
}
