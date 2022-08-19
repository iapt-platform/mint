<?php

namespace App\Http\Controllers;

use App\Models\UserDict;
use Illuminate\Http\Request;

class WbwLookupController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
		$output  = array();
		$wordPool = array();
		$input = \explode(',',$request->get("words")); 
		foreach ($input as $word) {
			# 0 未处理 1 已处理
			$wordPool[$word] = 0; 
		}
		for ($i=0; $i < 3; $i++) { 
			# code...
			foreach ($wordPool as $word => $status) {
				# code...
				$wordPool[$word] = 1;
				$result = UserDict::where('word',$word)->orderBy('confidence','desc')->get();
				if(count($result)>0){
					array_push($output,$result);
				}else{
					//没查到
					if($i == 1){
						//去尾查
					}
				}
				foreach ($result as $word2) {
					# 将拆分放入池中
					if(!empty($word2->factors)){
						$factors = \explode('+',$word2->factors);
						foreach ($factors as $factor) {
							# code...
							if(!isset($wordPool[$factor])){
								$wordPool[$factor] = 0;
							}
						}
					}
				}
			}
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
