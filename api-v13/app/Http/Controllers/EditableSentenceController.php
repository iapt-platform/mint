<?php

namespace App\Http\Controllers;

use App\Models\Sentence;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EditableSentenceController extends Controller
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
     * @param  Sentence  $sentence
     * @return Response
     */
    public function show(string $sentenceId)
    {
        //
        $sentence = Sentence::find($sentenceId);
        $sentId = $sentence->book_id.'-'.
                    $sentence->paragraph.'-'.
                    $sentence->word_start.'-'.
                    $sentence->word_end;
        $corpus = new CorpusController;
        $props = $corpus->getSentTpl($sentId, [$sentence->channel_uid],
            'edit', true,
            'react');

        return $this->ok($props);

    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, Sentence $sentence)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Sentence $sentence)
    {
        //
    }
}
