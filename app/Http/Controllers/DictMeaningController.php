<?php

namespace App\Http\Controllers;

use App\Models\UserDict;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Tools\RedisClusters;

class DictMeaningController extends Controller
{
    protected $langOrder = [
        "zh-Hans"=>[
            "zh-Hans","zh-Hant","jp","en","my","vi"
        ],
        "zh-Hant"=>[
            "zh-Hant","zh-Hans","jp","en","my","vi"
        ],
        "en"=>[
            "en","my","zh-Hant","zh-Hans","jp","vi"
        ],
        "jp"=>[
            "jp","en","my","zh-Hant","zh-Hans","vi"
        ],
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $words = explode("-",$request->get('word'));
        $lang = $request->get('lang');
        $key = "dict_first_mean/";
        $meaning = [];
        foreach ($words as $key => $word) {
            # code...
            $meaning[] = ['word'=>$word,'meaning'=>$this->get($word,$lang)];
        }

        return $this->ok($meaning);
    }

    public function get(string $word,string $lang){
        $currMeaning = "";
        if(isset($this->langOrder[$lang])){
            foreach ($this->langOrder[$lang] as $key => $value) {
                # 遍历每种语言。找到返回
                $cacheKey = "dict_first_mean/{$value}/{$word}";
                $meaning = RedisClusters::get($cacheKey);
                if(!empty($meaning)){
                    $currMeaning = $meaning;
                    break;
                }
            }
        }
        return $currMeaning;
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
     * @param  \App\Models\UserDict  $userDict
     * @return \Illuminate\Http\Response
     */
    public function show(UserDict $userDict)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserDict  $userDict
     * @return \Illuminate\Http\Response
     */
    public function edit(UserDict $userDict)
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
