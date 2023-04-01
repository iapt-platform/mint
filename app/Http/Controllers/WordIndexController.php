<?php

namespace App\Http\Controllers;

use App\Models\WordIndex;
use Illuminate\Http\Request;
use App\Http\Resources\WordIndexResource;
use Illuminate\Support\Facades\Cache;

class WordIndexController extends Controller
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
                $result = Cache::remember("/word_index/{$key}",120,function() use($key){
                    return WordIndex::where('word','like',$key."%")
                                    ->whereOr('word_en','like',$key."%")
                                    ->orderBy('word')
                                    ->take(10)->get();
                });
                return $this->ok(['rows'=>WordIndexResource::collection($result),'count'=>count($result)]);
                break;
            default:
                return $this->error('view error');
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
     * @param  \App\Models\WordIndex  $wordIndex
     * @return \Illuminate\Http\Response
     */
    public function show(WordIndex $wordIndex)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WordIndex  $wordIndex
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WordIndex $wordIndex)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WordIndex  $wordIndex
     * @return \Illuminate\Http\Response
     */
    public function destroy(WordIndex $wordIndex)
    {
        //
    }
}
