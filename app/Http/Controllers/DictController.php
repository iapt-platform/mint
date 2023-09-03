<?php

namespace App\Http\Controllers;

use App\Models\UserDict;
use App\Models\DictInfo;
use Illuminate\Http\Request;
use App\Tools\CaseMan;
use Illuminate\Support\Facades\Log;
use App\Http\Api\DictApi;

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
        $userLang = $request->get('lang',"zh");

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
                /**
                 * 按照语言调整词典顺序
                 * 算法：准备理想的词典顺序容器。
                 * 将查询的结果放置在对应的容器中。
                 * 最后将结果扁平化
                 * 准备字典容器
                * $wordDict = [
                *    "zh"=>[
                *        "0d79e8e8-1430-4c99-a0f1-b74f2b4b26d8"=>[];
                *    ]
                * ]
                 */

                foreach (DictApi::langOrder($userLang) as  $langId) {
                    # code...
                    $dictContainer = [];
                    foreach (DictApi::dictOrder($langId) as $dictId) {
                        $dictContainer[$dictId] = [];
                    }
                    $wordDict[$langId] = $dictContainer;
                }
                $dictList=[
                    'href'=> '#'.$anchor,
                    'title'=> "{$word}",
                    'children' => [],
                ];
                foreach ($result as $key => $value) {
                    # code...
                    $dictInfo= DictInfo::find($value->dict_id);
                    $dict_lang = explode('-',$dictInfo->dest_lang);
                    $anchor = "{$word}-{$dictInfo->shortname}";
                    $currData = [
                            'dictname'=> $dictInfo->name,
                            'shortname'=> $dictInfo->shortname,
                            'dict_id' => $value->dict_id,
                            'lang' => $dict_lang[0],
                            'word'=> $word,
                            'note'=> $this->GrmAbbr($value->note,0),
                            'anchor'=> $anchor,
                    ];
                    if(isset($wordDict[$dict_lang[0]])){
                        if(isset($wordDict[$dict_lang[0]][$value->dict_id])){
                            array_push($wordDict[$dict_lang[0]][$value->dict_id],$currData);
                        }else{
                            array_push($wordDict[$dict_lang[0]]["others"],$currData);
                        }
                    }else{
                        array_push($wordDict['others']['others'],$currData);
                    }
                }
                /**
                 * 把树状数据变为扁平数据
                 */
                foreach ($wordDict as $oneLang) {
                    # code...
                    foreach ($oneLang as $langId => $dictId) {
                        # code...
                        foreach ($dictId as $oneData) {
                            # code...
                            $wordData['dict'][] = $oneData;
                            if(isset($dictList['children']) && count($dictList['children'])>0){
                                $lastHref = end($dictList['children'])['href'];
                            }else{
                                $lastHref = '';
                            }
                            $currHref = '#'.$oneData['anchor'];
                            if($lastHref !== $currHref){
                                $dictList['children'][] = [
                                    'href'=> $currHref,
                                    'title'=> $oneData['shortname'],
                                ];
                            }
                        }
                    }
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
        $replaced = array();
        foreach (GRM_ABBR as $key => $value) {
            if(in_array($value["abbr"],$replaced)){
                continue;
            }else{
                $replaced[] = $value["abbr"];
            }
            if($dictid !== 0){
                if($value["dictid"]=== $dictid && strpos($input,$value["abbr"]."|") == false){
                    $mean = str_ireplace($value["abbr"],"|@{$value["abbr"]}-{$value["replace"]}",$mean);
                }
            }else{
                if( strpos($mean,"|@".$value["abbr"]) == false){
                    $props=base64_encode(\json_encode(['text'=>$value["abbr"],'gid'=>$value["replace"]]));
                    $tpl = "<MdTpl name='grammar-pop' tpl='grammar-pop' props='{$props}'></MdTpl>";
                    $mean = str_ireplace($value["abbr"],$tpl,$mean);
                }
            }
        }
        return $mean;
    }
}
