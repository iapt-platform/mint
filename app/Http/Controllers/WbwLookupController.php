<?php

namespace App\Http\Controllers;

use App\Models\UserDict;
use App\Models\DictInfo;
use App\Models\WbwTemplate;
use App\Models\Channel;
use Illuminate\Http\Request;
use App\Tools\CaseMan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Tools\RedisClusters;
use App\Http\Api\DictApi;
use App\Http\Api\AuthApi;



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
        '9ce6a53b-e28f-4fb7-b69d-b35fd5d76a24',//缅英字典
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

    //查用户字典获取全部结果
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
     *
     */
    private function langCheck($query,$lang){
        if($query===[]){
            return true;
        }else{
            if(in_array(strtolower($lang),$query)){
                return true;
            }else{
                $langFamily = explode('-',$lang)[0];
                foreach ($query as $value) {
                    if(strpos($value,$langFamily) !== false){
                        return true;
                    }
                }
            }
        }
        return false;
    }
    private function wbwPreference($word,$field,$userId){
        $prefix = 'wbw-preference';
        $fieldMap = [
                    'type'=>1,
                    'grammar'=>2,
                    'meaning'=>3,
                    'factors'=>4,
                    'factorMeaning'=>5,
                    'parent'=>6,
                    'part'=>7,
                    'case'=>8,
                ];
        $fieldId = $fieldMap[$field];
        $myPreference = RedisClusters::get("{$prefix}/{$word}/{$fieldId}/{$userId}");
        if(!empty($myPreference)){
            Log::debug($word.'命中我的wbw-'.$field,['data'=>$myPreference]);
            return ['value'=>$myPreference,'status'=>5];
        }else{
            $myPreference = RedisClusters::get("{$prefix}/{$word}/3/0");
            if(!empty($myPreference)){
                Log::debug($word.'命中社区wbw-'.$field,['data'=>$myPreference]);
                return ['value'=>$myPreference,'status'=>5];
            }
        }
        //Log::debug($word.'未命中'.$field);
        return false;
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
        $user = AuthApi::current($request);
        if(!$user ){
            //未登录用户
            return $this->error(__('auth.failed'),401,401);
        }

        $startAt = microtime(true)*1000;

        // system regular
        $this->initSysDict();

        $channel = Channel::find($request->get('channel_id'));
        $orgData = $request->get('data');
        $lang = [];
        foreach ($request->get('lang',[]) as $value) {
            $lang[] = strtolower($value);
        }
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
            $data = $word;

            $preference = $this->wbwPreference($word['real']['value'],'meaning',$user['user_id']);
            if($preference!==false){
                $data['meaning'] = $preference;
            }
            $preference = $this->wbwPreference($word['real']['value'],'factors',$user['user_id']);
            if($preference!==false){
                $data['factors'] = $preference;
            }
            $preference = $this->wbwPreference($word['real']['value'],'factorMeaning',$user['user_id']);
            if($preference!==false){
                $data['factorMeaning'] = $preference;
            }
            $preference = $this->wbwPreference($word['real']['value'],'case',$user['user_id']);
            if($preference!==false){
                $data['case'] = $preference;
            }
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
                    if($value->type !== '.cp.'){
                        $parent = $this->insertValue([$value->parent],$parent,$increment);
                    }
                    if(isset($data['case']) && $data['case']['status']<5){
                        if(!empty($value->type) && $value->type !== ".cp."){
                            $case = $this->insertValue([$value->type."#".$value->grammar],$case,$increment);
                        }
                    }
                    if($data['factors']['status'] < 50){
                        $factors = $this->insertValue([$value->factors],$factors,$increment);
                    }
                    if(isset($data['factorMeaning']) && $data['factorMeaning']['status'] < 50){
                        $factorMeaning = $this->insertValue([$value->factormean],$factorMeaning,$increment,false);
                    }

                    if($data['meaning']['status'] < 50){
                        if($this->langCheck($lang,$value->language)){
                            $meaning = $this->insertValue(explode('$',$value->mean),$meaning,$increment,false);
                        }
                    }
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
                if(count($factors)>0 && empty($data['factors']['value'])){
                    arsort($factors);
                    $first = array_keys($factors)[0];
                    $data['factors'] = ['value'=>$first==="_null"?"":$first,'status'=>3];
                }

                if(count($factorMeaning)>0){
                    arsort($factorMeaning);
                    $first = array_keys($factorMeaning)[0];
                    $data['factorMeaning'] = ['value'=>$first==="_null"?"":$first,'status'=>5];
                }
                if(isset($data['factorMeaning']) && $data['factorMeaning']['status']<5){
                    $wbwFactorMeaning = [];
                    if(!empty($data['factors']['value'])){
                        foreach (explode("+",$data['factors']['value']) as  $factor) {
                            $preference = $this->wbwPreference($factor,'meaning',$user['user_id']);
                            if($preference!==false){
                                $wbwFactorMeaning[] = $preference['value'];
                            }else{
                                $wbwFactorMeaning[] = $factor;
                            }
                        }
                    }
                    $data['factorMeaning'] = ['value'=>implode('+',$wbwFactorMeaning),'status'=>3];
                }

                if(empty($data['meaning']['value']) && !empty($data['parent']['value'])){
                    if(isset($indexed[$data['parent']['value']])){
                        foreach ($indexed[$data['parent']['value']] as $value) {
                            //根据base 查找词意
                            //非base优先
                            $increment = 10;
                            if($this->langCheck($lang,$value->language)){
                                $meaning = $this->insertValue(explode('$',$value->mean),$meaning,$increment,false);
                            }
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

            if(!isset($data['factorMeaning']['value']) ||
                $this->fmEmpty($data['factorMeaning']['value'])){
                $factorMeaning = [];
                //生成自动的拆分意思
                $autoMeaning = '';
                $currFactors = explode('+',$data['factors']['value']) ;
                $autoFM = [];
                foreach ($currFactors as $factor) {
                    $subFactors = explode('-',$factor) ;
                    $autoSubFM = [];
                    foreach ($subFactors as $subFactor) {
                        $preference = $this->wbwPreference($subFactor,'factorMeaning',$user['user_id']);
                        if($preference !== false){
                            $autoSubFM[] = $preference['value'];
                        }else{
                            $preference = $this->wbwPreference($subFactor,'meaning',$user['user_id']);
                            if($preference !== false){
                                $autoSubFM[] = $preference['value'];
                            }else{
                                $autoSubFM[] = '';
                            }

                        }
                    }
                    $autoFM[] = implode('-',$autoSubFM);
                    $autoMeaning .= implode('',$autoSubFM);
                }
                $autoMeaning .= implode('',$autoFM);
                if(count($autoFM) > 0){
                    $data['factorMeaning'] = ['value'=>implode('+',$autoFM),'status'=>3];
                    if(empty($data['meaning']['value']) && !empty($autoMeaning)){
                        $data['meaning'] = ['value'=>$autoMeaning,'status'=>3];
                    }
                }
            }


            $orgData[$key] = $data;
        }
        return $this->ok($orgData);
    }

    private function fmEmpty($value){
        if(str_replace(['+','-',' '],'',$value) === ''){
            return true;
        }else{
            return false;
        }
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
                            foreach ($indexed[$data['parent']['value']] as $value) {
                                //非base优先
                                $increment = 10;
                                $meaning = $this->insertValue(explode('$',$value->mean),$meaning,$increment,false);
                            }
                        }else{
                            //Log::error("no set parent".$data['parent']['value']);
                        }
                    }
                    if(count($meaning)>0){
                        arsort($meaning);
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

    /**
     * $empty：是否允许空值
     */
    private function insertValue($value,$container,$increment,$empty=true){
        foreach ($value as $one) {
            if($empty === false){
                if($this->fmEmpty($one)){
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
