<?php

namespace App\Http\Controllers;

use App\Models\UserOperationDaily;
use App\Models\UserOperationLog;
use App\Models\Wbw;
use App\Models\Sentence;
use App\Models\DhammaTerm;
use App\Models\UserDict;
use Illuminate\Http\Request;
use App\Http\Api\AuthApi;
use App\Http\Api\UserApi;
use Illuminate\Support\Facades\Cache;
use App\Tools\RedisClusters;

class UserStatisticController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
     * @param  \App\Models\UserOperationDaily  $userOperationDaily
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,string $userName)
    {
        //
        $queryUserId = UserApi::getIntIdByName($userName);
        $queryUserUuid = UserApi::getIdByName($userName);
        $cacheExpiry = config('mint.cache.expire');

        $expSum = 0;
        $wbwCount = 0;
        $lookupCount = 0;
        $translationCount = 0;
        $translationCountPub = 0;
        $termCount = 0;
        $termCountWithNote = 0;
        $myDictCount = 0;
        //总经验值
        if(!$request->has('view') || $request->get('view') === 'exp-sum'){
            $expSum = RedisClusters::remember("user/{$userName}/exp/sum",$cacheExpiry,
                    function() use($queryUserId){
                        return UserOperationDaily::where('user_id',$queryUserId)
                                ->sum('duration');
            });
        }

        //逐词解析
        if(!$request->has('view') || $request->get('view') === 'wbw-count'){
            $wbwCount = RedisClusters::remember("user/{$userName}/wbw/count",$cacheExpiry,
                        function() use($queryUserId){
                            return Wbw::where('editor_id',$queryUserId)
                                ->count();
                            });
        }

        //查字典次数
        if(!$request->has('view') || $request->get('view') === 'lookup-count'){
            $lookupCount = RedisClusters::remember("user/{$userName}/lookup/count",$cacheExpiry,
                            function() use($queryUserId){
                                return UserOperationLog::where('user_id',$queryUserId)
                                                        ->where('op_type','dict_lookup')
                                                        ->count();
                                    });
        }
        //译文
        //TODO 判断是否是译文channel
        if(!$request->has('view') || $request->get('view') === 'translation-count'){
            $translationCount = RedisClusters::remember("user/{$userName}/translation/count",$cacheExpiry,
                                function() use($queryUserUuid){
                                return Sentence::where('editor_uid',$queryUserUuid)
                                            ->count();
                                });
            $translationCountPub = RedisClusters::remember("user/{$userName}/translation/count-pub",$cacheExpiry,
                                    function() use($queryUserUuid){
                                        return Sentence::where('editor_uid',$queryUserUuid)
                                        ->where('status',30)
                                        ->count();
                                    });
        }
        //术语
        if(!$request->has('view') || $request->get('view') === 'term-count'){
            $termCount = RedisClusters::remember("user/{$userName}/term/count",$cacheExpiry,
                        function() use($queryUserId){
                            return DhammaTerm::where('editor_id',$queryUserId)
                                        ->count();
                        });
            $termCountWithNote = RedisClusters::remember("user/{$userName}/term/count-note",$cacheExpiry,
                                function() use($queryUserId){
                                    return DhammaTerm::where('editor_id',$queryUserId)
                                                        ->where('note',"<>","")
                                                        ->count();
                                    });
        }
        //单词本
        if(!$request->has('view') || $request->get('view') === 'my-dict-count'){
            $myDictCount = RedisClusters::remember("user/{$userName}/dict/count",$cacheExpiry,
                            function() use($queryUserId){
                                return UserDict::where('creator_id',$queryUserId)
                                            ->count();
                            });
        }
        return $this->ok([
            "exp" => ["sum"=>(int)$expSum],
            "wbw" => ["count"=>(int)$wbwCount],
            "lookup" => ["count"=>(int)$lookupCount],
            "translation" =>["count"=>(int)$translationCount,
                             "count_pub"=>(int)$translationCountPub],
            "term" => ["count"=>(int)$termCount,
                      "count_with_note"=>(int)$termCountWithNote],
            "dict" => ["count"=>(int)$myDictCount],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserOperationDaily  $userOperationDaily
     * @return \Illuminate\Http\Response
     */
    public function edit(UserOperationDaily $userOperationDaily)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserOperationDaily  $userOperationDaily
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserOperationDaily $userOperationDaily)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserOperationDaily  $userOperationDaily
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserOperationDaily $userOperationDaily)
    {
        //
    }
}
