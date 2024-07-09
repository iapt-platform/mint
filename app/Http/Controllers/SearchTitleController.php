<?php

namespace App\Http\Controllers;

use App\Models\PaliText;
use Illuminate\Http\Request;
use App\Http\Resources\SearchTitleIndexResource;

class SearchTitleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $key = strtolower($request->get('key'));
        $table = PaliText::where('level','<',8)
                         ->where(function ($query) use($key){
                            $query->where('title_en','like',"%{$key}%")
                                  ->orWhere('title','like',"%{$key}%");
                        });
        $count = $table->count();
        $table = $table->orderBy('title_en');
        $table = $table->skip($request->get("offset",0))
                         ->take($request->get('limit',10));

        $result = $table->get();
        return $this->ok(["rows"=>SearchTitleIndexResource::collection($result),"count"=>$count]);
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
     * @param  \App\Models\PaliText  $paliText
     * @return \Illuminate\Http\Response
     */
    public function show(PaliText $paliText)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PaliText  $paliText
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PaliText $paliText)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PaliText  $paliText
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaliText $paliText)
    {
        //
    }
}
