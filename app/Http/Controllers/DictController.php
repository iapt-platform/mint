<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use App\Models\UserDict;
use App\Models\DictInfo;
use App\Models\GroupMember;
use Illuminate\Http\Request;
use App\Tools\CaseMan;
use Illuminate\Support\Facades\Log;
use App\Http\Api\DictApi;
use App\Http\Api\AuthApi;

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
        $startAt = microtime(true);

        $output = [];
        $wordDataOutput = [];
        $dictListOutput = [];
        $caseListOutput = [];
        $wordDataPass = [];
		$indexCol = ['word','note','dict_id'];
        $words = [];
        $word_base = [];
        $searched = [];
        $words[$request->get('word')] = [];
        $userLang = $request->get('lang',"zh");

        /**
         * 临时代码判断是否在缅汉字典群里面。在群里的用户可以产看缅汉字典pdf
         */
        $user = AuthApi::current($request);
        if($user){
            $inMyHanGroup = GroupMember::where('group_id','905af467-1bde-4d2c-8dc7-49cfb74e0b09')
                                       ->where('user_id',$user['user_uid'])->exists();
        }else{
            $inMyHanGroup = false;
        }

        if (App::environment('local')) {
            // The environment is local
            $inMyHanGroup = true;
        }
        $resultCount=0;
        for ($i=0; $i < 2; $i++) {
            # code...
            $word_base = [];
            $wordDataOutput = [];
            foreach ($words as $word => $case) {
                # code...
                $searched[] = $word;
                $table = UserDict::select($indexCol)
                                ->where('word',$word)
                                ->where('source','_PAPER_');
                if(!$inMyHanGroup){
                    $table = $table->where('dict_id','<>','8ae6e0f5-f04c-49fc-a355-4885cc08b4b3');
                    //测试代码
                    //$table = $table->where('dict_id','<>','ac9b7b73-b9c0-4d31-a5c9-7c6dc5a2c187');
                }
                $result = $table->get();
                $resultCount += count($result);
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
                            'description'=>$dictInfo->description,
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

            $wordDataPass[] = ['pass'=>$i+1,'words'=>$wordDataOutput];

            if(count($word_base)===0){
                break;
            }else{
                $words = $word_base;
            }

        }

        if($resultCount<2){
            //查询内文
            $wordDataOutput = [];
            $table = UserDict::select($indexCol)
                                ->where('note','like','%'.$word.'%')
                                ->where('language','<>','my')
                                ->take(5)
                                ->get();
            $resultCount += count($table);
            $wordData=[
                'word'=> $word,
                'factors'=> "",
                'parents'=> "",
                'case'=> [],
                'grammar'=>[],
                'anchor'=> $anchor,
                'dict' => [],
            ];
            foreach ($table as $key => $value) {
                $dictInfo= DictInfo::find($value->dict_id);
                    $dict_lang = explode('-',$dictInfo->dest_lang);
                    $anchor = "{$word}-{$dictInfo->shortname}";
                $currData = [
                    'dictname'=> $dictInfo->name,
                    'shortname'=> $dictInfo->shortname,
                    'description'=>$dictInfo->description,
                    'dict_id' => $value->dict_id,
                    'lang' => $dict_lang[0],
                    'word'=> $word,
                    'note'=> $this->GrmAbbr($value->note,0),
                    'anchor'=> $anchor,
                ];
                $wordData['dict'][] = $currData;
            }
            $wordDataOutput[] = $wordData;
            $wordDataPass[] = ['pass'=>0,'words'=>$wordDataOutput];
        }


        $output['words'] = $wordDataPass;
        $output['dictlist'] = $dictListOutput;
        $output['caselist'] = $caseListOutput;

        $output['time'] = microtime(true) - $startAt;
        $output['count'] = $resultCount;

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
