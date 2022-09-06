<?php

namespace App\Http\Controllers;

use App\Models\UserDict;
use Illuminate\Http\Request;
use App\Tools\CaseMan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;



class WbwLookupController extends Controller
{
	private $dictList = [
		'57afac99-0887-455c-b18e-67c8682158b0',// system regular
		'4d3a0d92-0adc-4052-80f5-512a2603d0e8',// system irregular
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
		$caseman = new CaseMan();
		$output  = array();
		$wordPool = array();
		$input = \explode(',',$request->get("word")); 
		foreach ($input as $word) {
			$wordPool[$word] = ['base' => false,'done' => false,'apply' => false]; 
		}
		Log::info("query start ".$request->get("word"));
		if(empty($request->get("deep"))){
			$deep = 2;
		}else{
			$deep = $request->get("deep");
		}
		for ($i=0; $i < $deep; $i++) { 
			# code...
			foreach ($wordPool as $word => $info) {
				# code...
				if($info['done'] == false){
					$wordPool[$word]['done'] = true;
					foreach ($this->dictList as  $dictId) {
						# code...
					}
					$result = Cache::remember("dict/basic/".$word,60,function() use($word){
						return UserDict::where('word',$word)->where('source','<>','_USER_WBW_')->where('source','<>','_PAPER_')->orderBy('confidence','desc')->get();
					});
					Log::info("query {$word} ".((microtime(true)-$startAt)*1000)."s.");
					if(count($result)>0){
						foreach ($result as  $dictword) {
							# code...
							array_push($output,$dictword);
							if(!empty($dictword->factors)){
								if(!isset($wordPool[$word]['factors'])){
									//将第一个拆分作为最佳拆分存储
									$wordPool[$word]['factors'] = $dictword->factors;
									Log::info("best factor:{$dictword->factors}");
								}
							}
						}
					}else{
						//没查到 去尾查
						Log::info("没查到 去尾查");
						$newBase = array();
						$parents = $caseman->WordToBase($word);
						foreach ($parents as $base => $rows) {
							Log::info("found:{$value['type']}-{$value['grammar']}-{$value['parent']}");
							array_push($output,$rows);
						}
						Log::info("去尾查结束");
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
			Log::info("loop {$i} ".((microtime(true)-$startAt)*1000)."s.");
		}

		return $this->ok(["rows"=>$output]);
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
}
