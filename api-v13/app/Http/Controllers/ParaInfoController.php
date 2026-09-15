<?php

namespace App\Http\Controllers;

use App\Models\PaliText;
use App\Models\RelatedParagraph;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class ParaInfoController extends Controller
{
    /**
     * 取一个章节内 pali_texts 的全部段落记录。
     *
     * 以 (book, para) 定位章节起始段，再按 chapter_len 取出该章节覆盖的整段区间。
     * 每条记录附带 related_paragraphs 里的 CS6 锚点（book_name + cs_para），
     * 没有锚点的段落这两个字段为 null——约 2% 的段落没有锚点，属正常情况。
     *
     * @param  int  $book  书号
     * @param  int  $para  章节起始段落号
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'book' => ['required', 'integer'],
            'para' => ['required', 'integer'],
        ]);

        $root = PaliText::where('book', $validated['book'])
            ->where('paragraph', $validated['para'])
            ->first();
        if (! $root) {
            return $this->error('no paragraph');
        }

        $paraFrom = $root->paragraph;
        $paraTo = $root->paragraph + $root->chapter_len - 1;

        $chapters = PaliText::where('book', $root->book)
            ->whereBetween('paragraph', [$paraFrom, $paraTo])
            ->select(['book', 'paragraph', 'toc', 'level', 'lenght', 'chapter_len'])
            ->orderBy('paragraph', 'asc')
            ->get();

        $anchors = $this->relatedAnchors($root->book, $paraFrom, $paraTo);

        $rows = [];
        foreach ($chapters as $chapter) {
            $anchor = $anchors[$chapter->paragraph] ?? null;
            $rows[] = [
                'book' => $chapter->book,
                'paragraph' => $chapter->paragraph,
                'toc' => $chapter->toc,
                'level' => $chapter->level,
                // pali_texts 的列名拼错成 lenght，输出用正确拼写
                'length' => $chapter->lenght,
                'chapter_len' => $chapter->chapter_len,
                'book_name' => $anchor->book_name ?? null,
                'cs_para' => $anchor->cs_para ?? null,
            ];
        }

        return $this->ok(['rows' => $rows, 'count' => count($rows)]);
    }

    /**
     * 批量取段落区间的 CS6 锚点，按 paragraph 索引。
     *
     * 一次聚合查询取完，不能按段落逐条查——chapter_len 最大到 15943，
     * 逐条查会打出上万次 SQL。
     *
     * 一个段落可能对应多个 cs_para（约 1.6% 的段落如此，通常是连续区间），
     * 取最小值作为该段的起始锚点；book_name 在同一 (book, para) 内唯一，
     * min() 只是为了配合 GROUP BY。cs_para = 0 表示没有锚点，直接排除。
     *
     * @return Collection<int, object{book_name: string, cs_para: int}>
     */
    private function relatedAnchors(int $book, int $paraFrom, int $paraTo)
    {
        return RelatedParagraph::where('book', $book)
            ->whereBetween('para', [$paraFrom, $paraTo])
            ->where('cs_para', '>', 0)
            ->groupBy('para')
            ->selectRaw('para, min(book_name) as book_name, min(cs_para) as cs_para')
            ->get()
            ->keyBy('para');
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(string $id)
    {
        //
    }
}
