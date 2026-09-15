<?php

namespace App\Http\Controllers;

use App\Models\WordIndex;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SearchWordSliceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $slice
     * @return Response
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
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
