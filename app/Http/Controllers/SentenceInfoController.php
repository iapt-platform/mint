<?php

namespace App\Http\Controllers;

use App\Models\Sentence;
use App\Models\PaliSentence;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SentenceInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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

    private function getSentProgress(Request $request,$date=''){
        $channel = $request->get('channel');
        $from = $request->get('from');
        $to = $request->get('to');

        #默认完成度显示字符数
        # strlen 
        # page
        # percent 
        $view = 'strlen';
        if(!empty($request->get('view'))){
            $view =$request->get('view');
        }
        if(!empty($request->get('type'))){
            $view =$request->get('type');
        }
        #一页书中的字符数
        $pageStrLen = 2000;
        if(!empty($request->get('strlen'))){
            $pageStrLen =$request->get('strlen');
        }
        if(!empty($request->get('pagelen'))){
            $pageStrLen =$request->get('pagelen');
        }

        # 页数
        $pageNumber = 300;
        if(!empty($request->get('pages'))){
            $pageNumber =$request->get('pages');
        }

        $db = Sentence::where('channel_uid',$request->get('channel'))
                ->where('book_id','>=',$request->get('book'))
                ->where('paragraph','>=',$request->get('from'))
                ->where('paragraph','<=',$request->get('to'));
        if(!empty($date)){
            $db = $db->whereDate('created_at','=',$date);
        }
        $strlen =$db->sum('strlen');

        if(is_null($strlen) || $strlen===0){
            return 0;
        }
        #计算已完成百分比
        $percent = 0;
        if(($view==='page' && !empty($request->get('pages'))) || $view==='percent' ){
            #计算完成的句子在巴利语句子表中的字符串长度百分比
            $db = Sentence::select(['paragraph','word_start'])
                ->where('channel_uid',$request->get('channel'))
                ->where('book_id','>=',$request->get('book'))
                ->where('paragraph','>=',$request->get('from'))
                ->where('paragraph','<=',$request->get('to'));
            if(!empty($date)){
                $db = $db->whereDate('created_at','=',$date);
            }
            $sentFinished = $db->get();
            #查询这些句子的总共等效巴利语字符数
            $allStrLen = PaliSentence::where('book',$request->get('book'))
                            ->where('paragraph','>=',$request->get('from'))
                            ->where('paragraph','<=',$request->get('to'))
                            ->sum('length');            
            $para_strlen = 0;
            foreach ($sentFinished as $sent) {
                # code...
                $para_strlen += PaliSentence::where('book',$request->get('book'))
                            ->where('paragraph',$sent->paragraph)
                            ->where('word_begin',$sent->word_start)
                            ->value('length');
            }

            $percent = $para_strlen / $allStrLen;
        }
        switch ($view) {
            case 'page':
                # 输出已经完成的页数
                if(!empty($request->get('pages'))){
                    #给了页码，用百分比计算
                    $resulte = $percent * $request->get('pages');
                }else{
                    #没给页码，用每页字符数计算
                    $resulte = $strlen / $pageStrLen;
                }
                break;
            case 'percent':
                $resulte = $percent;
                break;
            case 'strlen':
            default:
                # code...
                $resulte = $strlen;
                break;
        }
        #保留小数点后两位
        $resulte = sprintf('%.2f',$resulte);
        return $resulte;
    }
    /**
    * 输出一张图片显示进度
     * Display the specified resource.
     *
     * @param  \App\Models\Sentence  $sentence
     * @return \Illuminate\Http\Response
     * http://127.0.0.1:8000/api/sentence/progress/image?channel=00ae2c48-c204-4082-ae79-79ba2740d506&&book=168&from=916&to=926&view=page
     */
    public function showprogress(Request $request)
    {
        ob_clean();
        ob_start();
        $resulte = $this->getSentProgress($request);
        $img = imagecreate(strlen($resulte)*10,22) or die('create image fail ');
        imagecolorallocate($img,255,255,255);
        $color = imagecolorallocate($img,0,0,0);
        imagestring($img,5,0,0,$resulte,$color);
        imagegif($img);
        imagedestroy($img);

        $content = ob_get_clean();
        return response($content,200,[
            'Content-Type'=>'image/gif'
        ]);
    }

    //http://127.0.0.1:8000/api/sentence/progress/daily/image?channel=00ae2c48-c204-4082-ae79-79ba2740d506&&book=168&from=916&to=926&view=page
    public function showprogressdaily(Request $request)
    {
        $imgWidth = 300;
        $imgHeight = 100;
        $xAxisOffset = 16;
        $yAxisOffset = 16;
        $maxDay = 10;
        $maxPage = 20;
        $yLineSpace = 5;

        #默认完成度显示字符数
        # strlen 
        # page
        # percent 
        $view = 'strlen';
        if(!empty($request->get('view'))){
            $view =$request->get('view');
        }
        if(!empty($request->get('type'))){
            $view =$request->get('type');
        }



        $pagePix = ($imgHeight-$xAxisOffset)/$maxPage;
        $dayPix = ($imgWidth-$yAxisOffset)/$maxDay;

        ob_clean();
        ob_start();
        $channel = $request->get('channel');
        $from = $request->get('from');
        $to = $request->get('to');

        $img = imagecreate($imgWidth,$imgHeight) or die('create image fail ');

        #颜色定义
        //background color
        imagecolorallocate($img,255,255,255);
        $color = imagecolorallocate($img,0,0,0);
        $gray = imagecolorallocate($img,180,180,180);
        $dataLineColor = imagecolorallocate($img,50,50,255);

        //绘制坐标轴
        imageline($img,0,$imgHeight-$xAxisOffset,$imgWidth,$imgHeight-$xAxisOffset,$color);
        imageline($img,$yAxisOffset,$imgHeight,$yAxisOffset,0,$color);
        //绘制y轴网格线
        for($i=1;$i<$maxPage/$yLineSpace;$i++){
            $space= ($imgHeight-$xAxisOffset)/$maxPage*$yLineSpace;
            $y=$imgHeight-$yAxisOffset-$i*$space;
            imageline($img,$yAxisOffset,$y,$imgWidth,$y,$gray);
            imagestring($img,5,0,$y-5,$i*$yLineSpace,$color);
        }
        //绘制x轴网格线
        for($i=0; $i<$maxDay; $i++){
            $space= ($imgWidth-$yAxisOffset)/$maxDay;
            $x=$imgWidth-$yAxisOffset-$i*$space;
            $dayOffset = $maxDay-$i;
            $date = strtotime("today -{$i} day");
            $day = date("d",$date);
            imageline($img,$x,($imgHeight-$xAxisOffset),$x,($imgHeight-$xAxisOffset+5),$gray);
            imagestring($img,5,$x,($imgHeight-$xAxisOffset),$day,$color);
        }

        $last=0;
        for($i = 1; $i <= $maxDay; $i++){
            $day = strtotime("today -{$i} day");
            $date = date("Y-m-d",$day);

            $resulte = $this->getSentProgress($request,$date)*$pagePix;
            if($i>0){
                imageline($img,($imgWidth-$i*$dayPix),$imgHeight-$xAxisOffset-$resulte,($imgWidth-($i-1)*$dayPix),$imgHeight-$xAxisOffset-$last,$dataLineColor);
            }
            $last = $resulte;
        }

        imagegif($img);
        imagedestroy($img);

        $content = ob_get_clean();
        return response($content,200,[
            'Content-Type'=>'image/gif'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sentence  $sentence
     * @return \Illuminate\Http\Response
     */
    public function show(Sentence $sentence)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sentence  $sentence
     * @return \Illuminate\Http\Response
     */
    public function edit(Sentence $sentence)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sentence  $sentence
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sentence $sentence)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sentence  $sentence
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sentence $sentence)
    {
        //
    }
}
