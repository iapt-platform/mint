<?php

namespace App\Http\Controllers;

use App\Models\UserDict;
use Illuminate\Http\Request;

require_once __DIR__."/../../../public/app/dict/grm_abbr.php";


class DictController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $output = [];
        $wordDataOutput = [];
        $dictListOutput = [];
        $caseListOutput = [];
		$indexCol = ['word','note','dict_id'];

        $word = $request->get('word');
        $result = UserDict::select($indexCol)->where('word',$word)->where('source','_PAPER_')->get();
        $anchor = $word;
        $wordData=[
            'word'=> $word,
            'factors'=> "",
            'parents'=> "",
            'case'=> [],
            'anchor'=> $anchor,
            'dict' => [],
        ];
        $dictList=[
            'href'=> '#'.$anchor,
            'title'=> "{$word}",
        ];
        foreach ($result as $key => $value) {
            # code...
            $dictName= $value->dict_id;
            $anchor = "{$word}-$dictName";
            $wordData['dict'][] = [
                'dictname'=> $dictName,
                'word'=> $word,
                'note'=> $this->GrmAbbr($value->note,0),
                'anchor'=> $anchor,
            ];
            $dictList['children'][] = [
                'href'=> '#'.$anchor,
                'title'=> "{$dictName}",
            ];
        }
        $wordDataOutput[]=$wordData;
        $dictListOutput[]=$dictList;


        $output['words'] = $wordDataOutput;
        $output['dictlist'] = $dictListOutput;
        $output['caselist'] = $caseListOutput;

        //$result = UserDict::select('word')->where('word','like',"{$word}%")->groupBy('word')->get();
        //$output['like'] = $result;

        return $this->ok($output);
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
     * @param  \App\Models\UserDict  $userDict
     * @return \Illuminate\Http\Response
     */
    public function show(UserDict $userDict)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserDict  $userDict
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserDict $userDict)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserDict  $userDict
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserDict $userDict)
    {
        //
    }

    private function GrmAbbr($input,$dictid){
        $mean = $input;

        foreach (GRM_ABBR as $key => $value) {
            # code...
            if($dictid !== 0){
                if($value["dictid"]=== $dictid && strpos($input,$value["abbr"]."|") == false){
                    $mean = str_ireplace($value["abbr"],"|@{$value["abbr"]}-grammar_{$value["replace"]}",$mean);
                }
            }else{
                if( strpos($mean,"|@".$value["abbr"]) == false){
                    $mean = str_ireplace($value["abbr"],"|@{$value["abbr"]}-grammar_{$value["replace"]}|",$mean);
                }
            }

        }
        return $mean;
    }
}
