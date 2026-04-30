<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WordIndex;
use App\Tools\CaseMan;
use Illuminate\Support\Facades\Log;

class SearchWordSliceController extends Controller
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
     * @param  string  $slice
     * @return \Illuminate\Http\Response
     */
    public function show($slice)
    {
        //
        $words = WordIndex::where('word', 'like', str_replace('-', '%', $slice))
            ->orderBy('len')
            ->select(['word', 'count', 'len'])->get();

        return $this->ok(['rows' => $words, 'count' => count($words)]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
