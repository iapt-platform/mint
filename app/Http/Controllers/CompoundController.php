<?php

namespace App\Http\Controllers;

use App\Models\UserDict;
use Illuminate\Http\Request;
use App\Http\Api\DictApi;
use App\Tools\TurboSplit;

class CompoundController extends Controller
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /**
         *
         */
        $dict_id = DictApi::getSysDict('robot_compound');
        if(!$dict_id){
            return $this->error('没有找到 robot_compound 字典');
        }
        //删除旧数据
        $del = UserDict::where('dict_id',$dict_id)
                    ->whereIn('word',$request->get('index'))
                    ->delete();
        foreach ($request->get('words') as $key => $word) {
            $new = new UserDict;
            $new->id = app('snowflake')->id();
            $new->word = $word['word'];
            $new->factors = $word['factors'];
            $new->dict_id = $dict_id;
            $new->source = '_ROBOT_';
            $new->create_time = (int)(microtime(true)*1000);
            $new->type = $word['type'];
            $new->grammar = $word['grammar'];
            $new->parent = $word['parent'];
            $new->confidence = $word['confidence'];
            $new->note = $word['confidence'];
            $new->language = 'cm';
            $new->creator_id = 1;
            $new->flag = 0;//标记为维护状态
            $new->save();
        }
        return $this->ok(count($request->get('words')));
    }

    /**
     * Display the specified resource.
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DhammaTerm  $dhammaTerm
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,string $word)
    {
        //
        $start = microtime(true);
        $dict_id = DictApi::getSysDict('robot_compound');
        if(!$dict_id){
            return $this->error('没有找到 robot_compound 字典');
        }
        $result = UserDict::where('dict_id',$dict_id)
                    ->where('word',$word)
                    ->orderBy('confidence','desc')
                    ->get();
        if(count($result)>0){
            return $this->ok(['rows'=>$result,'count'=>count($result),'mode'=>'dict']);
        }else if(mb_strlen($word,'UTF-8')<60){
            $ts = new TurboSplit();
            $parts = $ts->splitA($word);
            $time = microtime(true) - $start;
            return $this->ok(['rows'=>$parts,'count'=>count($parts),'mode'=>'realtime','time'=>$time]);
        }else{
            return $this->ok(['rows'=>[],'count'=>0]);
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DhammaTerm  $dhammaTerm
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DhammaTerm $dhammaTerm)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DhammaTerm  $dhammaTerm
     * @return \Illuminate\Http\Response
     */
    public function destroy(DhammaTerm $dhammaTerm)
    {
        //
    }
}
