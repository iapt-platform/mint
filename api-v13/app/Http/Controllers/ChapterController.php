<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChapterResource;
use App\Models\PaliText;
use App\Services\PaliTextService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ChapterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->input('view')) {
            case 'toc':
                $chapter = PaliText::where('book', $request->input('book'))
                    ->where('paragraph', $request->input('para'))
                    ->first();
                $start = $request->input('para');
                $end = $request->input('para') + $chapter->chapter_len - 1;
                $table = PaliText::where('book', $request->input('book'))
                    ->whereBetween('paragraph', [$start, $end])
                    ->where('level', '<', 100)
                    ->select(['book', 'paragraph', 'level', 'text', 'chapter_len', 'chapter_strlen', 'parent']);
                break;
        }
        // 获取记录总条数
        $count = $table->count();
        // 处理排序
        $table = $table->orderBy(
            $request->input('order', 'paragraph'),
            $request->input('dir', 'asc')
        );
        // 处理分页
        $table = $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 1000));
        $result = $table->get();

        return $this->ok([
            'rows' => ChapterResource::collection($result),
            'count' => $count,
        ]);
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
     * @return Response
     */
    public function update(Request $request, PaliText $paliText)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(PaliText $paliText)
    {
        //
    }
}
