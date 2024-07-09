<?php

namespace App\Http\Controllers;

use App\Models\UserDict;
use App\Models\DictInfo;
use Illuminate\Http\Request;
use App\Http\Resources\DictVocabularyResource;


class DictVocabularyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->get("view")) {
            case 'dict_name':
                $id = DictInfo::where('name',$request->get("name"))->value('id');
                if(!$id){
                    return $this->error('name:'.$request->get("name").' can not found.',200,200);
                }
                $table = UserDict::where('dict_id',$id)
                                ->groupBy('word')
                                ->selectRaw('word,count(*)');
                break;
            case 'dict_short_name':
                    $id = DictInfo::where('shortname',$request->get("name"))->value('id');
                    if(!$id){
                        return $this->error('name:'.$request->get("name").' can not found.',200,200);
                    }
                    $table = UserDict::where('dict_id',$id)
                                    ->groupBy('word')
                                    ->selectRaw('word,count(*)');
                    break;

        }
        if($request->get("stream") === 'true'){
            return response()->streamDownload(function () use ($table) {
                $result = $table->get();
                echo json_encode($result);
            },'dict.txt');
        }
        $count = 2;
        $table = $table->orderBy('word',$request->get('dir','asc'));

        $table = $table->skip($request->get('offset',0))
                       ->take($request->get('limit',1000));

        $result = $table->get();
        return $this->ok([
                            "rows"=>DictVocabularyResource::collection($result),
                            "count"=>$count
                        ]);
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
