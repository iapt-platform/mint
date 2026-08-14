<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\PaliText;
use App\Models\BookTitle;
use App\Models\Tag;
use App\Models\TagMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\PaliTextResource;
use App\Services\PaliTextService;

class PaliTextController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $request->validate([
            'view' => 'required|in:chapter-tag,chapter,chapter_children,children,paragraph,paragraphs-info,book-toc',
        ]);
        $all_count = 0;
        switch ($request->input('view')) {
            case 'chapter-tag':
                /**
                 * 分面筛选用的 tag 清单：返回每个 tag 及其命中的 pali_texts 条数。
                 *
                 * 传 tags 时是**且**的关系：内层按 anchor 统计它命中了几个指定 tag，
                 * 只留下 co = 指定 tag 个数的，也就是必须同时带上全部指定 tag；
                 * 外层再对这批 anchor 重新按 tag 统计条数，作为下一级筛选的候选项。
                 *
                 * @param  string  $tags  逗号分隔；不传则统计全部带 tag 的 pali_texts
                 */
                $tm = (new TagMap)->getTable();
                $tg = (new Tag)->getTable();
                $pt = (new PaliText)->getTable();
                if ($request->input('tags') && $request->input('tags') !== '') {
                    $tags = explode(',', $request->input('tags'));
                    foreach ($tags as $tag) {
                        # code...
                        if (!empty($tag)) {
                            $tagNames[] = $tag;
                        }
                    }
                }

                if (isset($tagNames)) {
                    $where1 = " where co = " . count($tagNames);
                    $a = implode(",", array_fill(0, count($tagNames), '?'));
                    $in1 = "and t.name in ({$a})";
                    $param  = $tagNames;
                } else {
                    $where1 = " ";
                    $in1 = " ";
                }
                $query = "
                    select tags.id,tags.name,co as count
                        from (
                            select tm.tag_id,count(*) as co from (
                                select anchor_id as id from (
                                    select tm.anchor_id , count(*) as co
                                        from $tm as  tm
                                        left join $tg as t on tm.tag_id = t.id
                                        left join $pt as pc on tm.anchor_id = pc.uid
                                        where tm.table_name  = 'pali_texts'
                                        $in1
                                        group by tm.anchor_id
                                ) T
                                    $where1
                            ) CID
                            left join $tm as tm on tm.anchor_id = CID.id
                            group by tm.tag_id
                        ) tid
                        left join $tg on $tg.id = tid.tag_id
                        order by count desc
                    ";
                if (isset($param)) {
                    $chapters = DB::select($query, $param);
                } else {
                    $chapters = DB::select($query);
                }
                $all_count = count($chapters);
                break;

            case 'chapter':
                /**
                 * 书目/章节清单：返回 uid、book、paragraph、level、toc、chapter_strlen 等。
                 *
                 * tags 同样是**且**的关系。传了 tags 就放宽到 level < 3（书及其下一级），
                 * 不传则只取 level = 1，即 276 本书的顶层。
                 *
                 * 注意不传 tags 时这条 SQL 仍从 tag_maps 出发，所以严格说返回的是
                 * 「至少带一个 tag 的 level 1 记录」。目前 276 本书都带了 tag，两者恰好
                 * 等价；将来有书没打 tag，这里会静默漏掉它。
                 *
                 * @param  string  $tags  逗号分隔
                 */
                if ($request->input('tags') && $request->input('tags') !== '') {
                    $tags = explode(',', $request->input('tags'));
                    foreach ($tags as $tag) {
                        # code...
                        if (!empty($tag)) {
                            $tagNames[] = $tag;
                        }
                    }
                }

                $tm = (new TagMap)->getTable();
                $tg = (new Tag)->getTable();
                $pt = (new PaliText)->getTable();
                if (isset($tagNames)) {
                    $where1 = " where co = " . count($tagNames);
                    $a = implode(",", array_fill(0, count($tagNames), '?'));
                    $in1 = "and t.name in ({$a})";
                    $param = $tagNames;
                    $where2 = "where level < 3";
                } else {
                    $where1 = " ";
                    $in1 = " ";
                    $where2 = "where level = 1";
                }
                $query = "
                        select uid as id,book,paragraph,level,toc as title,chapter_strlen,parent,path from (
                            select anchor_id as cid from (
                                select tm.anchor_id , count(*) as co
                                    from $tm as  tm
                                    left join $tg as t on tm.tag_id = t.id
                                    where tm.table_name  = 'pali_texts'
                                    $in1
                                    group by tm.anchor_id
                            ) T
                                $where1
                        ) CID
                        left join $pt as pt on CID.cid = pt.uid
                        $where2
                        order by book,paragraph";

                if (isset($param)) {
                    $chapters = DB::select($query, $param);
                } else {
                    $chapters = DB::select($query);
                }

                $all_count = count($chapters);
                break;
            case 'chapter_children':
                /**
                 * 直接下级目录：信任 pali_texts.parent 字段，只取 level < 8 的目录节点。
                 *
                 * 与下面的 children 是两种取法：这里按 parent 直连，children 按段落区间
                 * 推算。父节点底下没有目录型子节点时，这里干脆返回空，**不会**退化成
                 * 列出正文段落——需要那种行为的用 children。
                 *
                 * @param  int  $book
                 * @param  int  $para  父节点的段落号
                 */
                $table = PaliText::where('book', $request->input('book'))
                    ->where('parent', $request->input('para'))
                    ->where('level', '<', 8);
                $all_count = $table->count();
                $chapters = $table->orderBy('paragraph')->get();
                break;
            case 'children':
                /**
                 * 下一层目录：不看 parent 字段，改用「段落区间 + 最浅的下一层」推算。
                 *
                 * 在 [para+1, para+chapter_len-1] 区间里找 level 落在 root.level+1 到 7
                 * 之间、最浅的那一层，再取该层的全部节点。这样层级有跳空也取得到——
                 * 实测确有 level 2 直接跳到 level 4 的书（如 63:15006、143:5678）。
                 *
                 * ⚠ **区间里一个目录层都没有时会退化**：改为返回区间内的全部记录，
                 * 其中包含 level 100 的正文段落。大章节因此可能一次返回几百条，
                 * 例如 115:2955（level 7、chapter_len 605）会返回 604 条。
                 * 调用方要么限制取用量，要么改用 chapter_children。
                 *
                 * level >= 8 的节点（level 8 是叶子条目、level 100 是正文段落）没有
                 * 下级，直接返回空。
                 *
                 * @param  string  $id    pali_texts.uid；给了就优先按它定位
                 * @param  int     $book
                 * @param  int     $para
                 */
                if ($request->has('id')) {
                    $root = PaliText::where('uid', $request->input('id'))
                        ->first();
                } else {
                    $root = PaliText::where('book', $request->input('book'))
                        ->where('paragraph', $request->input('para'))
                        ->first();
                }

                if ($root->level >= 8) {
                    $chapters = [];
                    break;
                }
                $start = $root->paragraph + 1;
                $end = $root->paragraph + $root->chapter_len - 1;
                $nextLevelChapter = PaliText::where('book', $root->book)
                    ->whereBetween('paragraph', [$start, $end])
                    ->whereBetween('level', [$root->level + 1, 7])
                    ->orderBy('level', 'asc')
                    ->first();
                if ($nextLevelChapter) {
                    //存在子目录
                    $chapters = PaliText::where('book', $root->book)
                        ->whereBetween('paragraph', [$start, $end])
                        ->where('level', $nextLevelChapter->level)
                        ->orderBy('paragraph', 'asc')
                        ->get();
                } else {
                    $chapters = PaliText::where('book', $root->book)
                        ->whereBetween('paragraph', [$start, $end])
                        ->orderBy('paragraph', 'asc')
                        ->get();
                }
                $all_count = count($chapters);
                break;
            case 'paragraph':
                /**
                 * 取单条 pali_texts 记录，按 (book, para) 精确匹配。
                 *
                 * 这个分支**直接 return**，不走后面统一的 progress_line 补充，也不套
                 * rows/count 的外壳——返回的就是那条记录本身。查不到返回 error。
                 *
                 * @param  int  $book
                 * @param  int  $para
                 */
                $result = PaliText::where('book', $request->input('book'))
                    ->where('paragraph', $request->input('para'))
                    ->first();
                if ($result) {
                    return $this->ok($result);
                } else {
                    return $this->error("no data");
                }
                break;
            case 'paragraphs-info':
                /**
                 * 取章节的 pali_texts 全部段落 记录，按 (book, para) 精确匹配。
                 *
                 *
                 * @param  int  $book
                 * @param  int  $para
                 */
                $root = PaliText::where('book', $request->input('book'))
                    ->where('paragraph', $request->input('para'))
                    ->first();
                if (!$root) {
                    return $this->error("no paragraph");
                }
                $chapters = PaliText::where('book', $request->input('book'))
                    ->whereBetween('paragraph', [$root->paragraph,$root->paragraph+$root->chapter_len-1])
                    ->select(['book','paragraph','toc','level','lenght','chapter_len'])
                    ->orderBy('paragraph','asc')
                    ->get();
                $all_count = count($chapters);
                break;
            case 'book-toc':
                /**
                 * 获取全书目录
                 * 2023-1-25 改进算法
                 * 需求：目录显示丛书以及此丛书下面的所有书。比如，选择清净道论的一个章节。显示清净道论两本书的目录
                 * 算法：
                 * 1. 查询这个目录的顶级目录
                 * 2. 查询book-title 获取丛书名
                 * 3. 根据从书名找到全部的书
                 * 4. 获取全部书的目录
                 *
                 * 输出形状与其它分支不同，有两处：开头插一条 book=0、paragraph=0 的
                 * 合成记录放丛书名；其余每条的 level 都 +1，给那条丛书名让出第 1 层。
                 * 也正因为结构不同，末尾统一补 progress_line 时把 book-toc 排除在外。
                 *
                 * @param  string  $series  丛书名；给了就直接按它取书目列表
                 * @param  int     $book    未给 series 时，与 para 一起定位所属丛书
                 * @param  int     $para
                 */

                if ($request->has('series')) {
                    $book_title = $request->input('series');
                    //获取丛书书目列表
                    $books = BookTitle::where('title', $request->input('series'))->get();
                } else {
                    //查询这个目录的顶级目录
                    //查询书起始段落
                    $rootPara = app(PaliTextService::class)->getBookPara(
                        $request->input('book'),
                        $request->input('para')
                    );
                    if (!$rootPara) {
                        return $this->error('no book', 404, 404);
                    }
                    //获取丛书书名
                    $book_title = BookTitle::where('book', $rootPara->book)
                        ->where('paragraph', $rootPara->paragraph)
                        ->value('title');
                    //获取丛书书目列表
                    $books = BookTitle::where('title', $book_title)->get();
                }


                $chapters = [];
                $chapters[] = ['book' => 0, 'paragraph' => 0, 'toc' => $book_title, 'level' => 1];
                foreach ($books as  $book) {
                    # code...
                    $rootPara = PaliText::where('book', $book->book)
                        ->where('paragraph', $book->paragraph)
                        ->first();
                    $table = PaliText::where('book', $rootPara->book)
                        ->whereBetween('paragraph', [$rootPara->paragraph, ($rootPara->paragraph + $rootPara->chapter_len - 1)])
                        ->where('level', '<', 8);
                    $all_count = $table->count();
                    $curr_chapters = $table->select(['book', 'paragraph', 'toc', 'level'])->orderBy('paragraph')->get();
                    foreach ($curr_chapters as  $chapter) {
                        # code...
                        $chapters[] = ['book' => $chapter->book, 'paragraph' => $chapter->paragraph, 'toc' => $chapter->toc, 'level' => ($chapter->level + 1)];
                    }
                }

                break;
            default:
                return $this->error('unknown view', 400, 400);
        }

        if ($request->input('view') !== 'book-toc' && 
        $request->input('view') !== 'paragraphs-info') {
            foreach ($chapters as $key => $value) {
                if (is_object($value)) {
                    //TODO $value->book 可能不存在
                    $progress_key = "/chapter_dynamic/{$value->book}/{$value->paragraph}/global";
                    $chapters[$key]->progress_line = Cache::get($progress_key);
                }
            }
        }
        return $this->ok(["rows" => $chapters, "count" => $all_count]);
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
        //
        $para = explode('-', $id);
        $paragraph = PaliText::where('book', $para[0])->where('paragraph', $para[1])->first();
        return $this->ok(new PaliTextResource($paragraph));
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
