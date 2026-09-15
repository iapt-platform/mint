<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Sentence;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SentenceIOController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $table = Sentence::select([
            'uid',
            'book_id',
            'paragraph',
            'word_start',
            'word_end',
            'content',
            'content_type',
            'channel_uid',
            'editor_uid',
            'language',
            'updated_at',
            'created_at',
        ]);
        switch ($request->input('view')) {
            case 'public':
                $channels = Channel::where('status', 30)
                    ->where('type', $request->input('type', 'translation'))
                    ->select('uid')->get();
                $table->whereIn('channel_uid', $channels)
                    ->where('updated_at', '>', $request->input('updated_at', '2000-1-1'));
                break;
        }
        $count = $table->count();
        // 处理排序
        $table->orderBy('updated_at', 'asc');
        // 处理分页
        $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 200));
        // 获取数据
        $result = $table->get();

        return $this->ok(['rows' => $result, 'count' => $count]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Sentence $sentence)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, Sentence $sentence)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Sentence $sentence)
    {
        //
    }
}
