<?php

namespace App\Http\Controllers;

use App\Models\UserDict;
use App\Models\DictInfo;
use App\Models\WbwTemplate;
use App\Models\Channel;
use App\Models\WbwAnalysis;
use Illuminate\Http\Request;
use App\Tools\CaseMan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Api\DictApi;



class WbwLookupController extends Controller
{
	private $dictList = [
		'85dcc61c-c9e1-4ae0-9b44-cd6d9d9f0d01',//社区汇总
		'4d3a0d92-0adc-4052-80f5-512a2603d0e8',// system irregular
		'8359757e-9575-455b-a772-cc6f036caea0',// system sandhi
		'61f23efb-b526-4a8e-999e-076965034e60',// pali myanmar grammar
		'eae9fd6f-7bac-4940-b80d-ad6cd6f433bf',// Concise P-E Dict
		'2f93d0fe-3d68-46ee-a80b-11fa445a29c6',// unity
		'beb45062-7c20-4047-bcd4-1f636ba443d1',// U Hau Sein
		'8833de18-0978-434c-b281-a2e7387f69be',// 巴汉增订
		'3acf0c0f-59a7-4d25-a3d9-bf394a266ebd',// 汉译パーリ语辞典-黃秉榮
	];
    /**
     * Create a new command instance.
     *
     * @return void
     */
    private function initSysDict()
    {
        // system regular
        $this->dictList[] = DictApi::getSysDict('system_regular');
        $this->dictList[] = DictApi::getSysDict('robot_compound');
        $this->dictList[] = DictApi::getSysDict('community');
        $this->dictList[] = DictApi::getSysDict('community_extract');
    }

    /**
     * Display a listing of the resource.
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        //
		$startAt = microtime(true)*1000;

        $this->initSysDict();

		$words = \explode(',',$request->get("word"));
        $bases = \explode(',',$request->get("base"));
        # 查询深度
		$deep = $request->get("deep",2);
        $result = $this->lookup($words,$bases,$deep);
        $endAt = microtime(true)*1000;


		return $this->ok(["rows"=>$result,
                          "count"=>count($result),
                          "time"=>(int)($endAt-$startAt)]);
    }

    public function lookup($words,$bases,$deep){
		$wordPool = array();
		$output  = array();
        foreach ($words as $word) {
			$wordPool[$word] = ['base' => false,'done' => false,'apply' => false];
		}
		foreach ($bases as $base) {
			$wordPool[$base] = ['base' => true,'done' => false,'apply' => false];
		}
        /**
         * 先查询字典名称
         */
        $dict_info = DictInfo::whereIn('id',$this->dictList)->select('id','shortname')->get();
        $dict_name = [];
        foreach ($dict_info as $key => $value) {
            # code...
            $dict_name[$value->id] = $value->shortname;
        }
        $caseman = new CaseMan();
		for ($i=0; $i < $deep; $i++) {
            $newBase = array();

            $newWords = [];
            foreach ($wordPool as $word => $info) {
                # code...
                if($info['done'] === false){
                    $newWords[] = $word;
                    $wordPool[$word]['done'] = true;
                }
            }
            $data = UserDict::whereIn('word',$newWords)
                            ->whereIn('dict_id',$this->dictList)
                            ->leftJoin('dict_infos', 'user_dicts.dict_id', '=', 'dict_infos.id')
                            ->orderBy('confidence','desc')
                            ->get();
            foreach ($data as $row) {
                # code...
                array_push($output,$row);
                if(!empty($row->parent) && !isset($wordPool[$row->parent]) ){
                    //将parent 插入待查询列表
                    $wordPool[$row->parent] = ['base' => true,'done' => false,'apply' => false];
                }
            }

			//处理查询结果中的拆分信息
			$newWordPart = array();
			foreach ($wordPool as $word => $info) {
				if(!empty($info['factors'])){
					$factors = \explode('+',$info['factors']);
					foreach ($factors as $factor) {
						# 将没有的拆分放入单词查询列表
						if(!isset($wordPool[$factor])){
							$wordPool[$factor] = ['base' => true,'done' => false,'apply' => false];
						}
					}
				}
			}
		}

        return $output;
    }
    /**
     * 自动查词
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $startAt = microtime(true)*1000;

        // system regular
        $this->initSysDict();

        $channel = Channel::find($request->get('channel_id'));
        $orgData = $request->get('data');
        //句子中的单词
        $words = [];
        foreach ($orgData as  $word) {
            # code...
            if( isset($word['type']) && $word['type']['value'] === '.ctl.'){
                continue;
            }
            if(!empty($word['real']['value'])){
                $words[] = $word['real']['value'];
            }
        }

        $result = $this->lookup($words,[],2);
        $indexed = $this->toIndexed($result);

        foreach ($orgData as  $key => $word) {
            if( isset($word['type']) && $word['type']['value'] === '.ctl.'){
                continue;
            }
            if(empty($word['real']['value'])){
                continue;
            }
            {
                $data = $word;
                if(isset($indexed[$word['real']['value']])){
                    //parent
                    $case = [];
                    $parent = [];
                    $factors = [];
                    $factorMeaning = [];
                    $meaning = [];
                    $parent2 = [];
                    $case2 = [];
                    foreach ($indexed[$word['real']['value']] as $value) {
                        //非base优先
                        if(strstr($value->type,'base') === FALSE){
                            $increment = 10;
                        }else{
                            $increment = 1;
                        }
                        //将全部结果加上得分放入数组
                        $parent = $this->insertValue([$value->parent],$parent,$increment);
                        if(!empty($value->type) && $value->type !== ".cp."){
                            $case = $this->insertValue([$value->type."#".$value->grammar],$case,$increment);
                        }
                        $factors = $this->insertValue([$value->factors],$factors,$increment);
                        $factorMeaning = $this->insertValue([$value->factormean],$factorMeaning,$increment);
                        $meaning = $this->insertValue(explode('$',$value->mean),$meaning,$increment,false);
                    }
                    if(count($case)>0){
                        arsort($case);
                        $first = array_keys($case)[0];
                        $data['case'] = ['value'=>$first==="_null"?"":$first,'status'=>3];
                    }
                    if(count($parent)>0){
                        arsort($parent);
                        $first = array_keys($parent)[0];
                        $data['parent'] = ['value'=>$first==="_null"?"":$first,'status'=>3];
                    }
                    if(count($factors)>0){
                        arsort($factors);
                        $first = array_keys($factors)[0];
                        $data['factors'] = ['value'=>$first==="_null"?"":$first,'status'=>3];
                    }
                    //拆分意思
                    if(count($factorMeaning)>0){
                        arsort($factorMeaning);
                        $first = array_keys($factorMeaning)[0];
                        $data['factorMeaning'] = ['value'=>$first==="_null"?"":$first,'status'=>3];
                    }
                    $wbwFactorMeaning = [];
                    if(!empty($data['factors']['value'])){
                        foreach (explode("+",$data['factors']['value']) as  $factor) {
                            # code...
                            $wbwAnalyses = WbwAnalysis::where('wbw_word',$factor)
                                                      ->where('type',7)
                                                      ->selectRaw('data,count(*)')
                                                      ->groupBy("data")
                                                      ->orderBy("count", "desc")
                                                      ->first();
                            if($wbwAnalyses){
                                $wbwFactorMeaning[]=$wbwAnalyses->data;
                            }else{
                                $wbwFactorMeaning[]="";
                            }
                        }
                    }
                    $data['factorMeaning'] = ['value'=>implode('+',$wbwFactorMeaning),'status'=>3];

                    if(!empty($data['parent'])){
                        if(isset($indexed[$data['parent']['value']])){
                            foreach ($indexed[$data['parent']['value']] as $value) {
                                //根据base 查找词意
                                //非base优先
                                $increment = 10;
                                $meaning = $this->insertValue(explode('$',$value->mean),$meaning,$increment,false);
                                //查找词源
                                if(!empty($value->parent) && $value->parent !== $value->word && strstr($value->type,"base") !== FALSE ){
                                    $parent2 = $this->insertValue([$value->grammar."$".$value->parent],$parent2,1,false);
                                }
                            }
                        }
                    }
                    if(count($meaning)>0){
                        arsort($meaning);
                        $first = array_keys($meaning)[0];
                        $data['meaning'] = ['value'=>$first==="_null"?"":$first,'status'=>3];
                    }
                    if(count($parent2)>0){
                        arsort($parent2);
                        $first = explode("$",array_keys($parent2)[0]);
                        $data['parent2'] = ['value'=>$first[1],'status'=>3];
                        $data['grammar2'] = ['value'=>$first[0],'status'=>3];
                    }
                }
                $orgData[$key] = $data;
            }
        }
        return $this->ok($orgData);
    }

    /**
     * 自动查词
     *
     * @param  string  $sentId
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,string $sentId)
    {
        $startAt = microtime(true)*1000;

        $channel = Channel::find($request->get('channel_id'));

        //查询句子中的单词
        $sent = \explode('-',$sentId);
        $wbw = WbwTemplate::where('book',$sent[0])
                ->where('paragraph',$sent[1])
                ->whereBetween('wid',[$sent[2],$sent[3]])
                ->orderBy('wid')
                ->get();
        $words = [];
        foreach ($wbw as  $row) {
            if($row->type !== '.ctl.' && !empty($row->real)){
                $words[] = $row->real;
            }
        }
        $result = $this->lookup($words,[],2);
        $indexed = $this->toIndexed($result);

        //生成自动填充结果
        $wbwContent = [];
        foreach ($wbw as  $row) {
            $type = $row->type=='?'? '':$row->type;
            $grammar = $row->gramma=='?'? '':$row->gramma;
            $part = $row->part=='?'? '':$row->part;
            if(!empty($type) || !empty($grammar)){
                $case = "{$type}#$grammar";
            }else{
                $case = "";
            }
            $data = [
                    'sn'=>[$row->wid],
                    'word'=>['value'=>$row->word,'status'=>3],
                    'real'=> ['value'=>$row->real,'status'=>3],
                    'meaning'=> ['value'=>[],'status'=>3],
                    'type'=> ['value'=>$type,'status'=>3],
                    'grammar'=> ['value'=>$grammar,'status'=>3],
                    'case'=> ['value'=>$case,'status'=>3],
                    'style'=> ['value'=>$row->style,'status'=>3],
                    'factors'=> ['value'=>$part,'status'=>3],
                    'factorMeaning'=> ['value'=>'','status'=>3],
                    'confidence'=> 0.5
                ];
            if($row->type !== '.ctl.' && !empty($row->real)){
                if(isset($indexed[$row->real])){
                    //parent
                    $case = [];
                    $parent = [];
                    $factors = [];
                    $factorMeaning = [];
                    $meaning = [];
                    $parent2 = [];
                    $case2 = [];
                    foreach ($indexed[$row->real] as $value) {
                        //非base优先
                        if(strstr($value->type,'base') === FALSE){
                            $increment = 10;
                        }else{
                            $increment = 1;
                        }
                        //将全部结果加上得分放入数组
                        $parent = $this->insertValue([$value->parent],$parent,$increment);
                        $case = $this->insertValue([$value->type."#".$value->grammar],$case,$increment);
                        $factors = $this->insertValue([$value->factors],$factors,$increment);
                        $factorMeaning = $this->insertValue([$value->factormean],$factorMeaning,$increment);
                        $meaning = $this->insertValue(explode('$',$value->mean),$meaning,$increment,false);
                    }
                    if(count($case)>0){
                        arsort($case);
                        $first = array_keys($case)[0];
                        $data['case'] = ['value'=>$first==="_null"?"":$first,'status'=>3];
                    }
                    if(count($parent)>0){
                        arsort($parent);
                        $first = array_keys($parent)[0];
                        $data['parent'] = ['value'=>$first==="_null"?"":$first,'status'=>3];
                    }
                    if(count($factors)>0){
                        arsort($factors);
                        $first = array_keys($factors)[0];
                        $data['factors'] = ['value'=>$first==="_null"?"":$first,'status'=>3];
                    }
                    if(count($factorMeaning)>0){
                        arsort($factorMeaning);
                        $first = array_keys($factorMeaning)[0];
                        $data['factorMeaning'] = ['value'=>$first==="_null"?"":$first,'status'=>3];
                    }

                    //根据base 查找词意
                    if(!empty($data['parent'])){
                        if(isset($indexed[$data['parent']['value']])){
                            Log::info($data['parent']['value']."=".count($indexed[$data['parent']['value']]));
                            foreach ($indexed[$data['parent']['value']] as $value) {
                                //非base优先
                                $increment = 10;
                                $meaning = $this->insertValue(explode('$',$value->mean),$meaning,$increment,false);
                            }
                        }else{
                            Log::error("no set parent".$data['parent']['value']);
                        }
                    }
                    if(count($meaning)>0){
                        arsort($meaning);
                        Log::info('meanings=');
                        Log::info(array_keys($meaning));
                        $first = array_keys($meaning)[0];
                        $data['meaning'] = ['value'=>$first==="_null"?"":$first,'status'=>3];
                    }

                }
            }
            $wbwContent[]  = $data;
        }
        $endAt = microtime(true)*1000;
        return $this->ok(["rows"=>$wbwContent,
                        "count"=>count($wbwContent),
                        "time"=>(int)($endAt-$startAt)]);
    }

    private function toIndexed($words){
        //转成索引数组
        $indexed = [];
        foreach ($words as $key => $value) {
            # code...
            $indexed[$value->word][] = $value;
        }
        return $indexed;
    }

    private function insertValue($value,$container,$increment,$empty=true){
        foreach ($value as $one) {
            if($empty === false){
                if(empty($one)){
                    break;
                }
            }
            $one=trim($one);
            $key = $one;
            if(empty($key)){
                $key = '_null';
            }
            if(isset($container[$key])){
                $container[$key] += $increment;
            }else{
                $container[$key] = $increment;
            }
        }
        return $container;
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
}
