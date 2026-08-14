<?php

namespace App\Http\Controllers;

use App\Http\Api\PaliTextApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SuggestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
        switch ($request->input('view')) {
            case 'chapter':
                $chapter = PaliTextApi::getChapterStartEnd($request->input('book'), $request->input('para'));
                if (! $chapter) {
                    return $this->error('no data');
                }

                break;
        }
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
     * @param  \App\Models\Article  $article
     * @return Response
     */
    public function show(Article $article)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Article  $article
     * @return Response
     */
    public function update(Request $request, Article $article)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Article  $article
     * @return Response
     */
    public function destroy(Article $article)
    {
        //
    }
}
