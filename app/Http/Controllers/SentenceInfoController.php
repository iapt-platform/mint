<?php

namespace App\Http\Controllers;

use App\Models\Sentence;
use App\Models\PaliSentence;
use App\Models\PaliText;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Tools\RedisClusters;
use Illuminate\Support\Facades\DB;

class SentenceInfoController extends Controller
{
    protected $_endParagraph;
    protected $_startParagraph;
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
        if ($request->has('to')){
            $to = $request->get('to');
        }else{
            $to = $this->_endParagraph;
        }


        #默认完成度显示字符数
        # strlen
        # palistrlen 巴利语等效字符数
        # page
        # percent
        $view = 'strlen';
        if($request->has('view')){
            $view =$request->get('view');
        }else if($request->has('type')){
            $view =$request->get('type');
        }


        #一页书中的字符数
        $pageStrLen = 2000;
        if($request->has('strlen')){
            $pageStrLen =$request->get('strlen');
        }
        if($request->has('pagelen')){
            $pageStrLen = $request->get('pagelen');
        }

        # 页数
        $pageNumber = 300;
        if($request->has('pages')){
            $pageNumber =$request->get('pages');
        }

        $db = Sentence::where('sentences.channel_uid',$request->get('channel'))
                    ->where('sentences.book_id','>=',$request->get('book'))
                    ->where('sentences.paragraph','>=',$request->get('from'))
                    ->where('sentences.paragraph','<=',$to);
        if($view==="palistrlen"){
            $db = $db->leftJoin('pali_texts', function($join)
                    {
                        $join->on('sentences.book_id', '=', 'pali_texts.book');
                        $join->on('sentences.paragraph','=','pali_texts.paragraph');
                    });
        }
        if(!empty($date)){
            $db = $db->whereDate('sentences.created_at','=',$date);
        }
        if($view==="palistrlen"){
            return $db->sum('pali_texts.lenght');
        }
        $strlen = $db->sum('sentences.strlen');

        if(is_null($strlen) || $strlen===0){
            return 0;
        }
        #计算已完成百分比
        $percent = 0;
        if(($view==='page' && !empty($request->get('pages'))) || $view==='percent' ){
            #计算完成的句子在巴利语句子表中的字符串长度百分比
            $db = Sentence::select(['book_id','paragraph','word_start','word_end'])
                ->where('channel_uid',$request->get('channel'))
                ->where('book_id','>=',$request->get('book'))
                ->where('paragraph','>=',$request->get('from'))
                ->where('paragraph','<=',$to);
            if(!empty($date)){
                $db = $db->whereDate('created_at','=',$date);
            }
            $sentFinished = $db->get();
            #查询这些句子的总共等效巴利语字符数
            $allStrLen = PaliSentence::where('book',$request->get('book'))
                            ->where('paragraph','>=',$request->get('from'))
                            ->where('paragraph','<=',$to)
                            ->sum('length');
            $para_strlen = 0;

            foreach ($sentFinished as $sent) {
                # code...
				$key_sent_id = $sent->book_id.'-'.$sent->paragraph.'-'.$sent->word_start.'-'.$sent->word_end;
				$para_strlen += RedisClusters::remember('pali-sent/strlen/'.$key_sent_id,
                                    config('mint.cache.expire') ,
                                    function() use($sent) {
                                        return PaliSentence::where('book',$sent->book_id)
                                                ->where('paragraph',$sent->paragraph)
                                                ->where('word_begin',$sent->word_start)
                                                ->where('word_end',$sent->word_end)
                                                ->value('length');
				});
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
            case 'percent': //百分比
                $resulte = sprintf('%.2f',$percent);
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
     * http://127.0.0.1:8000/api/sentence/progress/image?channel=00ae2c48-c204-4082-ae79-79ba2740d506&&book=168&from=916&to=926&view=page&pages=400
     */
    public function showprogress(Request $request)
    {
        $resulte = $this->getSentProgress($request);
        $svg = "<svg  xmlns='http://www.w3.org/2000/svg'  xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 100 25'>";

        switch ($request->get('view')) {
            case 'percent':
                # code...
                $resulte = $resulte*100;
                $svg .= "<rect id='frontground' x='0' y='0' width='100' height='25' fill='#cccccc' ></rect>";
                $svg .= "<text id='bg_text'  x='5' y='21' fill='#006600' style='font-size:25px;'>$resulte%</text>";
                $svg .= "<rect id='background' x='0' y='0' width='100' height='25' fill='#006600' clip-path='url(#textClipPath)'></rect>";
                $svg .= "<text id='bg_text'  x='5' y='21' fill='#ffffff' style='font-size:25px;' clip-path='url(#textClipPath)'>$resulte%</text>";
                $svg .= "<clipPath id='textClipPath'>";
                $svg .= "    <rect x='0' y='0' width='$resulte' height='25'></rect>";
                $svg .= "</clipPath>";
                break;
            case 'strlen':
            case 'page':
            default:
                $svg .= "<text id='bg_text'  x='5' y='21' fill='#006600' style='font-size:25px;'>$resulte</text>";
                break;
        }
        $svg .= "</svg>";

        return response($svg,200,[
            'Content-Type'=>'image/svg+xml'
        ]);
    }

    //http://127.0.0.1:8000/api/sentence/progress/daily/image?channel=00ae2c48-c204-4082-ae79-79ba2740d506&&book=168&from=916&to=926&view=page
    public function showprogressdaily(Request $request)
    {
        $imgWidth = 300;
        $imgHeight = 100;
        $xAxisOffset = 16;
        $yAxisOffset = 25;
        $maxDay = 20;
        $maxPage = 20;
        $yLineSpace = 5;

        $yMin = 20; //y轴满刻度数值 最小

        #默认完成度显示字符数
        # strlen
        # page
        # percent
        $view = 'strlen';
        if($request->has('view')){
            $view =$request->get('view');
        }
        if($request->has('type')){
            $view =$request->get('type');
        }



        $pagePix = ($imgHeight-$xAxisOffset)/$maxPage;
        $dayPix = ($imgWidth-$yAxisOffset)/$maxDay;

        ob_clean();
        ob_start();
        $channel = $request->get('channel');
        $from = $request->get('from');
        if ($request->has('to')){
            $to = $request->get('to');
        }else{
            $chapterLen = PaliText::where('book',$request->get('book'))->where('paragraph',$from)->value('chapter_len');
            $to =  $from + $chapterLen - 1;
            $this->_endParagraph = $to;
        }

        $img = imagecreate($imgWidth,$imgHeight) or die('create image fail ');

        #颜色定义
        //background color
        imagecolorallocate($img,255,255,255);
        $color = imagecolorallocate($img,0,0,0);
        $gray = imagecolorallocate($img,180,180,180);
        $dataLineColor = imagecolorallocate($img,50,50,255);



        $max=0;
        $values = [];
        #按天获取数据
        for($i = 1; $i <= $maxDay; $i++){
            $day = strtotime("today -{$i} day");
            $date = date("Y-m-d",$day);
            $current = $this->getSentProgress($request,$date);
            $values[] = $current;
            if($max < $current){
                $max = $current;
            }
        }
        /*
        * 计算Y 轴满刻度值
        * 算法 不足 20 按 20 算 小于100 满刻度是是50的整倍数
        * 小于1000 满刻度是是500的整倍数
        */

        if($max < $yMin){
            $yMax = $yMin;
        }else{
            $len = strlen($max);
            $yMax = pow(10,$len);
            if($max < $yMax/2){
                $yMax = $yMax / 2;
            }
        }
        //根据满刻度像素数 计算缩放比例
        $yPix = $imgHeight - $xAxisOffset;//y轴实际像素数
        $rate = $yPix / $yMax;

        $svg = "<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"currentColor\" class=\"bi bi-alarm-fill\" viewBox=\"0 0 $imgWidth $imgHeight\">";

        //绘制坐标轴
        imageline($img,0,$imgHeight-$xAxisOffset,$imgWidth,$imgHeight-$xAxisOffset,$color);
        imageline($img,$yAxisOffset,$imgHeight,$yAxisOffset,0,$color);
        // x 轴
        $y=$imgHeight-$xAxisOffset+1;
        $svg .= "<line x1='$yAxisOffset'  y1='$y' x2='$imgWidth'   y2='$y' style='stroke:#666666;'></line>";
        // y 轴
        $x = $yAxisOffset - 1;
        $svg .= "<line x1='$x'  y1='0' x2='$x'   y2='".($imgHeight-$xAxisOffset)."' style='stroke:#666666;'></line>";
        //绘制x轴刻度线
        for($i=0; $i<$maxDay; $i++){
            $space= ($imgWidth-$yAxisOffset)/$maxDay;
            $x=$imgWidth-$i*$space - $space/2;
            $dayOffset = $maxDay-$i;
            $date = strtotime("today -{$i} day");
            $day = date("d",$date);
            imageline($img,$x,($imgHeight-$xAxisOffset),$x,($imgHeight-$xAxisOffset+5),$gray);
            imagestring($img,5,$x,($imgHeight-$xAxisOffset-2),$day,$color);

            $y = $imgHeight-$xAxisOffset+1;
            $height = 5;
            $svg .= "<line x1='$x'  y1='$y' x2='$x'   y2='".($y+$height)."' style='stroke:#666666;'></line>";
            $svg .= "<text x='".($x-5)."' y='".($y+12)."' style='font-size:8px;'>$day</text>";
        }


        //绘制y轴刻度线 将y轴五等分
        $step = $yMax / 5 * $rate;
        for ($i=1; $i < 5; $i++) {
            # code...
            $yValue = $yMax / 5 * $i;
            if($yValue>=1000000){
                $yValue = ($yValue / 1000000 ).'m';
            }else if($yValue>=1000){
                $yValue = ($yValue / 1000 ).'k';
            }
            $x = $yAxisOffset;
            $y = $imgHeight-$yAxisOffset-$i*$step;
            $svg .= "<line x1='$x'  y1='$y' x2='".($x - 5)."'   y2='$y' style='stroke:#666666;'></line>";
            $svg .= "<text x='".($x-18)."' y='".($y+4)."' style='font-size:8px;'>$yValue</text>";
        }
        for($i=1;$i < $maxPage/$yLineSpace;$i++){
            $space= ($imgHeight-$xAxisOffset)/$maxPage*$yLineSpace;
            $y=$imgHeight-$yAxisOffset-$i*$space;
            imageline($img,$yAxisOffset,$y,$imgWidth,$y,$gray);
            imagestring($img,5,0,$y-5,$i*$yLineSpace,$color);
        }
// 绘制柱状图
        $rectWidth = $dayPix*0.9;
        $last = 0;
        foreach ($values as $key => $value) {
            # code...
            $value = $value*$rate;
            $x = $imgWidth - ($dayPix * $key + $yAxisOffset);
            $y = $imgHeight - $xAxisOffset - $value;
            $svg .= "<rect x='$x' y='$y' height='{$value}' width='{$rectWidth}' style='stroke:#006600; fill: #006600'/>";
            if($key>0){
                imageline($img,($imgWidth - $key * $dayPix),$imgHeight - $xAxisOffset - $value,($imgWidth-($key - 1)*$dayPix),$imgHeight-$xAxisOffset-$last,$dataLineColor);
            }
            $last = $value;
        }

    $svg .= "</svg>";

        imagegif($img);
        imagedestroy($img);

        $content = ob_get_clean();
        return response($svg,200,[
            'Content-Type'=>'image/svg+xml'
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
