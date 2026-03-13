<?php

namespace App\Http\Controllers;

use App\Models\PaliText;
use Illuminate\Http\Request;
use App\Http\Resources\ChapterResource;
use App\Services\PaliTextService;

class ChapterController extends Controller
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
            case 'toc':
                $chapter = PaliText::where('book', $request->get('book'))
                    ->where('paragraph', $request->get('para'))
                    ->first();
                $start = $request->get('para');
                $end = $request->get('para') + $chapter->chapter_len - 1;
                $table = PaliText::where('book', $request->get('book'))
                    ->whereBetween('paragraph', [$start, $end])
                    ->where('level', '<', 100)
                    ->select(['book', 'paragraph', 'level', 'text', 'chapter_len', 'chapter_strlen', 'parent']);
                break;
        }
        //获取记录总条数
        $count = $table->count();
        //处理排序
        $table = $table->orderBy(
            $request->get("order", 'paragraph'),
            $request->get("dir", 'asc')
        );
        //处理分页
        $table = $table->skip($request->get("offset", 0))
            ->take($request->get("limit", 1000));
        $result = $table->get();
        return $this->ok([
            "rows" => ChapterResource::collection($result),
            "count" => $count
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
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show(string $id)
    {
        $para = explode('-', $id);
        if (count($para) < 2) {
            return $this->error('参数错误', 400, 400);
        }
        $paliTextService = app(PaliTextService::class);
        $paragraph = $paliTextService->getCurrChapter($para[0], $para[1]);
        return $this->ok(new ChapterResource($paragraph));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PaliText  $paliText
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PaliText $paliText)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PaliText  $paliText
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaliText $paliText)
    {
        //
    }
}
