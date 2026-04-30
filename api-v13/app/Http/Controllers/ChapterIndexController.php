<?php

namespace App\Http\Controllers;

use App\Models\ProgressChapter;
use App\Models\Channel;
use Illuminate\Http\Request;

class ChapterIndexController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->input('view')) {
            case 'public':
                $channels = Channel::where('status', 30)->select('uid');
                $table = ProgressChapter::whereIn('channel_id', $channels);
                break;
        }
        if ($request->has("updated_at")) {
            $table = $table->where('updated_at', '>', $request->input("updated_at"));
        }
        if ($request->has("created_at")) {
            $table = $table->where('created_at', '>', $request->input("created_at"));
        }
        //获取记录总条数
        $count = $table->count();
        //处理排序
        $table = $table->orderBy(
            $request->input("order", 'created_at'),
            $request->input("dir", 'desc')
        );
        //处理分页
        $table = $table->skip($request->input("offset", 0))
            ->take($request->input("limit", 200));
        //获取数据
        $result = $table->get();
        return $this->ok(["rows" => $result, "count" => $count]);
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
