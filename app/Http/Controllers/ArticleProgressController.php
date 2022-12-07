<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Sentence;
use App\Models\PaliSentence;
use App\Http\Api\PaliTextApi;
use Illuminate\Support\Arr;

use Illuminate\Http\Request;

class ArticleProgressController extends Controller
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
            case 'chapter':
                $chapter = PaliTextApi::getChapterStartEnd($request->get('book'),$request->get('para'));
                $channels = Sentence::where('book_id',$request->get('book'))
                                    ->whereBetween('paragraph',$chapter)
                                    ->where('strlen','>',0)
                                    ->groupBy('channel_uid')
                                    ->select('channel_uid')
                                    ->get();
                //获取单句长度
                $sentLen = PaliSentence::where('book',$request->get('book'))
                            ->whereBetween('paragraph',$chapter)
                            ->orderBy('word_begin')
                            ->select(['book','paragraph','word_begin','word_end','length'])
                            ->get();
                //获取每个channel的完成度
                foreach ($channels as $key => $value) {
                    # code...
                    $finished = Sentence::where('book_id',$request->get('book'))
                    ->whereBetween('paragraph',$chapter)
                    ->where('channel_uid',$value->channel_uid)
                    ->where('strlen','>',0)
                    ->select(['strlen','book_id','paragraph','word_start','word_end'])
                    ->get();
                    $final=[];
                    foreach ($sentLen as  $sent) {
                        # code...
                        $first = Arr::first($finished, function ($value, $key) use($sent) {
                            return ($value->book_id==$sent->book &&
                                    $value->paragraph==$sent->paragraph &&
                                    $value->word_start==$sent->word_begin &&
                                    $value->word_end==$sent->word_end);
                        });
                        $final[] = [$sent->length,$first?true:false];
                    }
                    $value['final'] = $final;
                }
                return $this->ok($channels);
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
     * @param  \App\Models\Channel  $channel
     * @return \Illuminate\Http\Response
     */
    public function show(Channel $channel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Channel  $channel
     * @return \Illuminate\Http\Response
     */
    public function edit(Channel $channel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Channel  $channel
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Channel $channel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Channel  $channel
     * @return \Illuminate\Http\Response
     */
    public function destroy(Channel $channel)
    {
        //
    }
}
