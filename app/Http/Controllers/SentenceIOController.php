<?php

namespace App\Http\Controllers;

use App\Models\Sentence;
use App\Models\Channel;
use Illuminate\Http\Request;

class SentenceIOController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $table = Sentence::select(['uid','book_id','paragraph',
                                    'word_start','word_end',
                                    'content','content_type',
                                    'channel_uid','editor_uid','language',
                                    'updated_at','created_at']);
        switch ($request->get('view')) {
            case 'public':
                $channels = Channel::where('status',30)
                                ->where('type',$request->get('type','translation'))
                                ->select('uid')->get();
                $table->whereIn('channel_uid',$channels)
                      ->where('updated_at','>',$request->get('updated_at','2000-1-1'));
            break;
        }
        $count = $table->count();
        //处理排序
        $table->orderBy('updated_at','asc');
        //处理分页
        $table->skip($request->get("offset",0))
              ->take($request->get("limit",200));
        //获取数据
        $result = $table->get();
        return $this->ok(["rows"=>$result,"count"=>$count]);
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
     * @param  \App\Models\Sentence  $sentence
     * @return \Illuminate\Http\Response
     */
    public function show(Sentence $sentence)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sentence  $sentence
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sentence $sentence)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sentence  $sentence
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sentence $sentence)
    {
        //
    }
}
