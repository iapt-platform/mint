<?php

namespace App\Http\Controllers;

use App\Models\BookTitle;
use Illuminate\Http\Request;
use App\Http\Resources\BookTitleResource;

class BookTitleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $result = BookTitle::orderBy('sn')->get();
        return $this->ok(["rows"=>BookTitleResource::collection($result),"count"=>count($result)]);
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
     * @param  \App\Models\BookTitle  $bookTitle
     * @return \Illuminate\Http\Response
     */
    public function show(BookTitle $bookTitle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BookTitle  $bookTitle
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BookTitle $bookTitle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BookTitle  $bookTitle
     * @return \Illuminate\Http\Response
     */
    public function destroy(BookTitle $bookTitle)
    {
        //
    }
}
