<?php

namespace App\Http\Controllers;

use App\Models\UserDict;
use App\Models\DictInfo;
use App\Models\WbwTemplate;
use Illuminate\Http\Request;
use App\Tools\CaseMan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;



class WbwLookupController extends Controller
{
	private $dictList = [
		'85dcc61c-c9e1-4ae0-9b44-cd6d9d9f0d01',//社区汇总
		'4d3a0d92-0adc-4052-80f5-512a2603d0e8',// system irregular
		'57afac99-0887-455c-b18e-67c8682158b0',// system regular
		'ef620a93-a55d-4756-89c5-e188ab009e45',//社区字典
		'8359757e-9575-455b-a772-cc6f036caea0',// system sandhi
		'c42980f0-5967-4833-b695-84183344f68f',// robot compound
		'61f23efb-b526-4a8e-999e-076965034e60',// pali myanmar grammar
		'eae9fd6f-7bac-4940-b80d-ad6cd6f433bf',// Concise P-E Dict
		'2f93d0fe-3d68-46ee-a80b-11fa445a29c6',// unity
		'beb45062-7c20-4047-bcd4-1f636ba443d1',// U Hau Sein
		'8833de18-0978-434c-b281-a2e7387f69be',// 巴汉增订
		'3acf0c0f-59a7-4d25-a3d9-bf394a266ebd',// 汉译パーリ语辞典-黃秉榮
	];
    /**
     * Display a listing of the resource.
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
		$startAt = microtime(true);

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
		$output  = array();
		$wordPool = array();
		$input = \explode(',',$request->get("word"));
		foreach ($input as $word) {
			$wordPool[$word] = ['base' => false,'done' => false,'apply' => false];
		}

		if(empty($request->get("deep"))){
			$deep = 2;
		}else{
			$deep = $request->get("deep");
		}
		for ($i=0; $i < $deep; $i++) {
			# 查询深度
			foreach ($wordPool as $word => $info) {
				# code...
				if($info['done'] === false){
					$wordPool[$word]['done'] = true;
					$count = 0;
					foreach ($this->dictList as  $dictId) {
						# code...
						$result = Cache::remember("dict/{$dictId}/".$word,10,function() use($word,$dictId,$dict_name){
                            $data = UserDict::where('word',$word)->where('dict_id',$dictId)->orderBy('confidence','desc')->get();
                            foreach ($data as $key => $value) {
                                # code...
                                $value->dict_shortname  = $dict_name[$dictId];
                            }
							return $data;
						});
						$count += count($result);
						if(count($result)>0){
							foreach ($result as  $dictword) {
								# code...
								array_push($output,$dictword);
								if(!isset($wordPool[$word]['factors']) && !empty($dictword->factors)){
									//将第一个拆分作为最佳拆分存储
									$wordPool[$word]['factors'] = $dictword->factors;
								}
							}
						}
					}

					if($count == 0){
						//没查到 去尾查
						$newBase = array();
						$parents = $caseman->WordToBase($word);
						foreach ($parents as $base => $rows) {
							array_push($output,$rows);
						}
					}
				}
			}

			//查询结果中的拆分信息
			$newWordPart = array();
			foreach ($wordPool as $word => $info) {
				if(!empty($info['factors'])){
					$factors = \explode('+',$info['factors']);
					foreach ($factors as $factor) {
						# 将没有的拆分放入单词查询列表
						if(!isset($wordPool[$factor])){
							$newWordPart[$factor] = 0;
						}
					}
				}
			}
			foreach ($newWordPart as $part => $value) {
				# 将拆分放入池中
				$wordPool[$part] = ['base' => false,'done' => false,'apply' => false];
			}
		}

		return $this->ok(["rows"=>$output,'count'=>count($output)]);
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
     * Display the words best match in specified sentence .
     *
     * @param  string  $sentId
     * @return \Illuminate\Http\Response
     */
    public function show(string $sentId)
    {
        //查询句子中的单词
        $sent = \explode('-',$sentId);
        WbwTemplate::where('book',$sent[0])
                ->where('paragraph',$sent[1])
                ->whereBetween('wid',[$sent[2],$sent[3]])
                ->orderBy('wid')
                ->get();


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
