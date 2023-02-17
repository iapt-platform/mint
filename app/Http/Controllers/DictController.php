<?php

namespace App\Http\Controllers;

use App\Models\UserDict;
use App\Models\DictInfo;
use Illuminate\Http\Request;
use App\Tools\CaseMan;
use Illuminate\Support\Facades\Log;

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
        $words = [];
        $word_base = [];
        $searched = [];
        $words[$request->get('word')] = [];
        for ($i=0; $i < 2; $i++) {
            # code...
            $word_base = [];
            foreach ($words as $word => $case) {
                # code...
                $searched[] = $word;
                $result = UserDict::select($indexCol)->where('word',$word)->where('source','_PAPER_')->get();
                $anchor = $word;
                $wordData=[
                    'word'=> $word,
                    'factors'=> "",
                    'parents'=> "",
                    'case'=> [],
                    'grammar'=>$case,
                    'anchor'=> $anchor,
                    'dict' => [],
                ];
                $dictList=[
                    'href'=> '#'.$anchor,
                    'title'=> "{$word}",
                ];
                foreach ($result as $key => $value) {
                    # code...
                    $dictInfo= DictInfo::find($value->dict_id);

                    $anchor = "{$word}-{$dictInfo->shortname}";
                    $wordData['dict'][] = [
                        'dictname'=> $dictInfo->name,
                        'word'=> $word,
                        'note'=> $this->GrmAbbr($value->note,0),
                        'anchor'=> $anchor,
                    ];
                    $dictList['children'][] = [
                        'href'=> '#'.$anchor,
                        'title'=> "{$dictInfo->shortname}",
                    ];
                }
                $wordDataOutput[]=$wordData;
                $dictListOutput[]=$dictList;

                //TODO 加变格查询
                $case = new CaseMan();
                $parent = $case->WordToBase($word);
                foreach ($parent as $base => $case) {
                    # code...
                    if(!in_array($base,$searched)){
                        $word_base[$base] = $case;
                        Log::info($case);
                    }
                }
            }
            if(count($word_base)===0){
                break;
            }else{
                $words = $word_base;
            }
        }




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
