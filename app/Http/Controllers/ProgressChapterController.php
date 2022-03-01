<?php

namespace App\Http\Controllers;

use App\Models\ProgressChapter;
use App\Models\Channel;
use Illuminate\Http\Request;

class ProgressChapterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->get('view')) {
			case 'studio':
            #查询该studio的channel
            $channels = Channel::where('owner_uid',$request->get('id'))->select('uid')->get();
            $aChannel = [];
            foreach ($channels as $channel) {
                # code...
                $aChannel[] = $channel->uid;
            }
            $chapters = ProgressChapter::select('book','para','channel_id','title','progress','created_at','updated_at')
                                       ->whereIn('channel_id', $aChannel)
                                       ->where('progress','>',0.85)
                                       ->orderby('created_at','desc')
                                       ->get();
            if($chapters){
                return $this->ok(["rows"=>$chapters,"count"=>count($chapters)]);
            }else{
                return $this->error("没有查询到数据");
            }
            break;
        }
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
     * @param  \App\Models\ProgressChapter  $progressChapter
     * @return \Illuminate\Http\Response
     */
    public function show(ProgressChapter $progressChapter)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProgressChapter  $progressChapter
     * @return \Illuminate\Http\Response
     */
    public function edit(ProgressChapter $progressChapter)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProgressChapter  $progressChapter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProgressChapter $progressChapter)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProgressChapter  $progressChapter
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProgressChapter $progressChapter)
    {
        //
    }
}
