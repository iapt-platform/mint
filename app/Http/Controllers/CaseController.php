<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Tools\CaseMan;

class CaseController extends Controller
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
     * 输入一个单词，输出三藏中所有可能的变形
     *
     * @param  string  $word
     * @return \Illuminate\Http\Response
     */
    public function show($word)
    {
        //
        $case  = new CaseMan();
        $result = $case->BaseToWord($word);
        return $this->ok(['rows'=>$result,'count'=>count($result)]);
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
