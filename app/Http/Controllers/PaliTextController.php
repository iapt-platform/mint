<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\PaliText;
use App\Models\BookTitle;
use App\Models\Tag;
use App\Models\TagMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Tools\RedisClusters;
use Illuminate\Support\Facades\Log;

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
        $all_count = 0;
        switch ($request->get('view')) {
            case 'chapter-tag':
                $tm = (new TagMap)->getTable();
                $tg = (new Tag)->getTable();
                $pt = (new PaliText)->getTable();
                if($request->get('tags') && $request->get('tags')!==''){
                    $tags = explode(',',$request->get('tags'));
                    foreach ($tags as $tag) {
                        # code...
                        if(!empty($tag)){
                            $tagNames[] = $tag;
                        }
                    }
                }

                if(isset($tagNames)){
                    $where1 = " where co = ".count($tagNames);
                    $a = implode(",",array_fill(0, count($tagNames), '?')) ;
                    $in1 = "and t.name in ({$a})";
                    $param  = $tagNames;
                }else{
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
                    if(isset($param)){
                        $chapters = DB::select($query,$param);
                    }else{
                        $chapters = DB::select($query);
                    }
                    $all_count = count($chapters);
                break;

            case 'chapter':
                $tm = (new TagMap)->getTable();
                $tg = (new Tag)->getTable();
                $pt = (new PaliText)->getTable();
                if($request->get('tags') && $request->get('tags')!==''){
                    $tags = explode(',',$request->get('tags'));
                    foreach ($tags as $tag) {
                        # code...
                        if(!empty($tag)){
                            $tagNames[] = $tag;
                        }
                    }
                }


                if(isset($tagNames)){
                    $where1 = " where co = ".count($tagNames);
                    $a = implode(",",array_fill(0, count($tagNames), '?')) ;
                    $in1 = "and t.name in ({$a})";
                    $param = $tagNames;
                    $where2 = "where level < 3";
                }else{
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

                    if(isset($param)){
                        $chapters = DB::select($query,$param);
                    }else{
                        $chapters = DB::select($query);
                    }

                $all_count = count($chapters);
                break;
            case 'chapter_children':
                $table = PaliText::where('book',$request->get('book'))
                                ->where('parent',$request->get('para'))
                                ->where('level','<',8);
                $all_count = $table->count();
                $chapters = $table->orderBy('paragraph')->get();
                break;
            case 'paragraph':
                $result = PaliText::where('book',$request->get('book'))
                                  ->where('paragraph',$request->get('para'))
                                  ->first();
                if($result){
                    return $this->ok($result);
                }else{
                    return $this->error("no data");
                }
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
                 */

                if($request->has('series')){
                    $book_title = $request->get('series');
                    //获取丛书书目列表
                    $books = BookTitle::where('title',$request->get('series'))->get();
                }else{
                    //查询这个目录的顶级目录
                    $path = PaliText::where('book',$request->get('book'))
                                    ->where('paragraph',$request->get('para'))
                                    ->select('path')->first();
                    if(!$path){
                        return $this->error("no data");
                    }
                    $json = \json_decode($path->path);
                    $root = null;
                    foreach ($json as $key => $value) {
                        # code...
                        if( $value->level == 1 ){
                            $root = $value;
                            break;
                        }
                    }
                    if($root===null){
                        return $this->error("no data");
                    }
                    //查询书起始段落
                    $rootPara = PaliText::where('book',$root->book)
                                    ->where('paragraph',$root->paragraph)
                                    ->first();
                    //获取丛书书名
                    $book_title = BookTitle::where('book',$rootPara->book)
                                            ->where('paragraph',$rootPara->paragraph)
                                            ->value('title');
                    //获取丛书书目列表
                    $books = BookTitle::where('title',$book_title)->get();
                }


                $chapters = [];
                $chapters[] = ['book'=>0,'paragraph'=>0,'toc'=>$book_title,'level'=>1];
                foreach ($books as  $book) {
                    # code...
                    $rootPara = PaliText::where('book',$book->book)
                                ->where('paragraph',$book->paragraph)
                                ->first();
                    $table = PaliText::where('book',$rootPara->book)
                                    ->whereBetween('paragraph',[$rootPara->paragraph,($rootPara->paragraph+$rootPara->chapter_len-1)])
                                    ->where('level','<',8);
                    $all_count = $table->count();
                    $curr_chapters = $table->select(['book','paragraph','toc','level'])->orderBy('paragraph')->get();
                    foreach ($curr_chapters as  $chapter) {
                        # code...
                        $chapters[] = ['book'=>$chapter->book,'paragraph'=>$chapter->paragraph,'toc'=>$chapter->toc,'level'=>($chapter->level+1)];
                    }
                }

                break;
            }
        if($chapters){
            if($request->get('view') !== 'book-toc'){
                foreach ($chapters as $key => $value) {
                    if(is_object($value)){
                        //TODO $value->book 可能不存在
                        $progress_key="/chapter_dynamic/{$value->book}/{$value->paragraph}/global";
                        $chapters[$key]->progress_line = RedisClusters::get($progress_key);
                    }
                }
            }
            return $this->ok(["rows"=>$chapters,"count"=>$all_count]);
        }else{
            return $this->error("no data");
        }
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        //

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
