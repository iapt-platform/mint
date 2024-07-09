<?php
/**
 * 输出巴利语全文搜索数据
 * 提供给搜索引擎
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BookTitle;
use App\Models\WbwTemplate;

class SearchPaliDataController extends Controller
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

        $maxParagraph = WbwTemplate::where('book',$book)->max('paragraph');
        $pageSize = $request->get('page_size',1000);
        $start = $request->get('start',1);
        $output = array();
        if($start+$pageSize>$maxParagraph){
            $endOfPara = $maxParagraph+1;
        }else{
            $endOfPara = $start+$pageSize;
        }

        for($iPara=$start; $iPara < $endOfPara; $iPara++){
            $content = $this->getContent($book,$iPara);
            //查找黑体字
            $words = WbwTemplate::where('book',$book)
                                ->where('paragraph',$iPara)
                                ->orderBy('wid')->get();
            $bold1 = array();
            $bold2 = array();
            $bold3 = array();
            $currBold = array();
            foreach ($words as $word) {
                if($word->style==='bld'){
                    $currBold[] = $word->real;
                }else{
                    $countBold = count($currBold);
                    if($countBold === 1){
                        $bold1[] = $currBold[0];
                    }else if($countBold === 2){
                        $bold2 = array_merge($bold2,$currBold);
                    }else if($countBold > 0){
                        $bold3 = array_merge($bold3,$currBold);
                    }
                    $currBold = [];
                }
            }
            $pcd_book = BookTitle::where('book',$book)
                    ->where('paragraph','<=',$iPara)
                    ->orderBy('paragraph','desc')
                    ->first();
            if($pcd_book){
                $pcd_book_id = $pcd_book->sn;
            }else{
                $pcd_book_id = BookTitle::where('book',$book)
                                        ->orderBy('paragraph')
                                        ->value('sn');
            }

            $update = ['book'=>$book,
                        'paragraph' => $iPara,
                        'bold1' => implode(' ',$bold1),
                        'bold2' => implode(' ',$bold2),
                        'bold3' => implode(' ',$bold3),
                        'content' => $content,
                        'pcd_book_id' => $pcd_book_id
                    ];
            $output[] = $update;
        }
        return $this->ok(['rows'=>$output,'count'=>$maxParagraph]);
    }
    private function getContent($book,$para){
        $words = WbwTemplate::where('book',$book)
                            ->where('paragraph',$para)
                            ->where('type',"<>",".ctl.")
                            ->orderBy('wid')->get();
        $content = '';
        foreach ($words as  $word) {
            if($word->style === 'bld'){
                if(strpos($word->word,"{")===FALSE){
                    $content .= "**{$word->word}** ";
                }else{
                    $content .= str_replace(['{','}'],['**','** '],$word->word);
                }
            }else if($word->style === 'note'){
                $content .= " _{$word->word}_ ";
            }else{
                $content .= $word->word . " ";
            }
        }
        return $content;
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
