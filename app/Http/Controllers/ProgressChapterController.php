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
        $selectCol = ['progress_chapters.book','progress_chapters.para','progress_chapters.channel_id','progress_chapters.title','pali_texts.toc','progress','progress_chapters.created_at','progress_chapters.updated_at'];
        $chapters=false;
        switch ($request->get('view')) {
			case 'studio':
                #查询该studio的channel
                $channels = Channel::where('owner_uid',$request->get('id'))->select('uid')->get();
                $aChannel = [];
                foreach ($channels as $channel) {
                    # code...
                    $aChannel[] = $channel->uid;
                }
                $chapters = ProgressChapter::select($selectCol)
                                        ->whereIn('progress_chapters.channel_id', $aChannel)
                                        ->leftJoin('pali_texts', function($join)
                                                {
                                                    $join->on('progress_chapters.book', '=', 'pali_texts.book');
                                                    $join->on('progress_chapters.para','=','pali_texts.paragraph');
                                                })
                                        ->where('progress','>',0.85)
                                        ->orderby('progress_chapters.created_at','desc')
                                        ->get();
                break;
            case 'tag':
                $aChannel = [67,68,69,70];
                $chapters = ProgressChapter::select($selectCol)
                                        ->whereIn('progress_chapters.book', $aChannel)
                                        ->leftJoin('pali_texts', function($join)
                                                {
                                                    $join->on('progress_chapters.book', '=', 'pali_texts.book');
                                                    $join->on('progress_chapters.para','=','pali_texts.paragraph');
                                                })
                                        ->orderby('progress','desc')
                                        ->get();
                break;
            case 'done':
                $chapters = ProgressChapter::select($selectCol)
                                        ->where('progress','>',0.85)
                                        ->leftJoin('pali_texts', function($join)
                                                {
                                                    $join->on('progress_chapters.book', '=', 'pali_texts.book');
                                                    $join->on('progress_chapters.para','=','pali_texts.paragraph');
                                                })
                                        ->orderby('progress_chapters.created_at','desc')
                                        ->get();
                break;
        }
        if($chapters){
            return $this->ok(["rows"=>$chapters,"count"=>count($chapters)]);
        }else{
            return $this->error("没有查询到数据");
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
