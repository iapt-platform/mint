<?php
/**
 * 输出某章节的句子列表。算法跟随章节显示功能
 */
namespace App\Http\Controllers;

use App\Models\PaliText;
use App\Models\PaliSentence;
use Illuminate\Http\Request;

class SentencesInChapterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $book = $request->get('book');
        $para = $request->get('para');
        $chapter = PaliText::where('book',$book)
                           ->where('paragraph',$para)
                           ->first();
        if(!$chapter){
            return $this->error("no chapter data");
        }
        $paraFrom = $para;
        $paraTo = $para+$chapter->chapter_len-1;

        //1. 计算 标题和下一级第一个标题之间 是否有间隔
        $nextChapter =  PaliText::where('book',$book)
                        ->where('paragraph',">",$para)
                        ->where('level','<',8)
                        ->orderBy('paragraph')
                        ->value('paragraph');
        $between = $nextChapter - $para;
        //查找子目录
        $chapterLen = $chapter->chapter_len;
        $toc = PaliText::where('book',$book)
                ->whereBetween('paragraph',[$paraFrom+1,$paraFrom+$chapterLen-1])
                ->where('level','<',8)
                ->orderBy('paragraph')
                ->select(['book','paragraph','level','toc'])
                ->get();

        if($between > 1){
        //有间隔
            $paraTo = $nextChapter - 1;
        }else{
            if($chapter->chapter_strlen>2000){
                if(count($toc)>0){
                    //有子目录只输出标题和目录
                    $paraTo = $paraFrom;
                }else{
                    //没有子目录 全部输出
                }
            }else{
                //章节小。全部输出 不输出子目录
                $toc = [];
            }
        }

        $sent = PaliSentence::where('book',$book)
                            ->whereBetween('paragraph',[$paraFrom,$paraTo])
                            ->select(['book','paragraph','word_begin','word_end'])
                            ->orderBy('paragraph')
                            ->orderBy('word_begin')
                            ->get();
        return $this->ok(['rows'=>$sent,'count'=>count($sent)]);
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
     * @param  \App\Models\PaliText  $paliText
     * @return \Illuminate\Http\Response
     */
    public function show(PaliText $paliText)
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
