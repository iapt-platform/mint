<?php

namespace App\Http\Controllers;

use App\Tools\CaseMan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CaseController extends Controller
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
     * 输入一个单词，输出三藏中所有可能的变形
     *
     * @param  string  $word
     * @return Response
     */
    public function show($word)
    {
        //
        $output = [];
        $case = new CaseMan;
        $result = $case->BaseToWord($word, 0.2);
        $output[] = ['word' => $word, 'case' => $result, 'count' => count($result)];
        $parent = $case->WordToBase($word, 1, false);
        foreach ($parent as $key => $base) {
            $result = $case->BaseToWord($key, 0.2);
            if (count($result) > 0) {
                $output[] = ['word' => $key, 'case' => $result, 'count' => count($result)];
            }
        }

        return $this->ok(['rows' => $output, 'count' => count($output)]);
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
