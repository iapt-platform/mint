<?php

namespace App\Http\Controllers;

use App\Models\WbwTemplate;
use App\Models\PaliText;
use App\Models\RelatedParagraph;
use App\Http\Resources\NavCSParaResource;
use Illuminate\Http\Request;

class NavCSParaController extends Controller
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
     * @param  \App\Models\WbwTemplate  $wbwTemplate
     * @return \Illuminate\Http\Response
     */
    public function show(string $paraNumber)
    {
        //99_5_37-38
        $id = explode('_',$paraNumber);
        if(count($id) !== 3){
            return $this->error('参数错误。参数应为3 实际得到'.count($id),400,400);
        }
        //查询段落起始
        $para = PaliText::where('book',$id[0])
                        ->where('paragraph',$id[1])
                        ->first();
        if(!$para){
            return $this->error('没有找到段落起始'.$id,404,404);
        }
        //cs 段落号列表
        $csPara = explode('-',$id[2]);
        $csList = [];
        for ($i=(int)$csPara[0]; $i <=(int)end($csPara) ; $i++) {
            $csList[] = $i;
        }
        //段落区间
        $begin = $id[1];
        $end = (int)$id[1] + $para->chapter_len;
        $curr = WbwTemplate::where('book',$id[0])
                                ->where('style','paranum')
                                ->whereIn('word',$csList)
                                ->whereBetween('paragraph',[$begin,$end])
                                ->orderBy('paragraph')
                                ->select('book','paragraph')->get();
        if(!$curr){
            return $this->error('没有找到段落'.$id,404,404);
        }

        $data = [];
        $data['curr'] = new NavCSParaResource($curr[0]);
        $next = WbwTemplate::where('book',$id[0])
                ->where('style','paranum')
                ->where('word',(int)end($csPara)+1)
                ->whereBetween('paragraph',[$begin,$end])
                ->select('book','paragraph')->first();
        if($next){
            $data['next'] = new NavCSParaResource($next);
            $data['end'] = $next->paragraph -1;
        }else{
            $data['end'] = $end;
        }
        $prev = WbwTemplate::where('book',$id[0])
                            ->where('style','paranum')
                            ->where('word',(int)$csPara[0]-1)
                            ->whereBetween('paragraph',[$begin,$end])
                            ->select('book','paragraph')->first();
        if($prev){
            $data['prev'] = new NavCSParaResource($prev);
        }
        return $this->ok($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WbwTemplate  $wbwTemplate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WbwTemplate $wbwTemplate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WbwTemplate  $wbwTemplate
     * @return \Illuminate\Http\Response
     */
    public function destroy(WbwTemplate $wbwTemplate)
    {
        //
    }
}
