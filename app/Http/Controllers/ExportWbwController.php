<?php

namespace App\Http\Controllers;

use App\Models\Wbw;
use App\Models\WbwBlock;
use Illuminate\Http\Request;

class ExportWbwController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $sent = explode("\n",$request->get("sent"));
        $output = [];
        foreach ($sent as $key => $value) {
            # code...
            $sent = [];
            $value = trim($value);
            $sentId = explode("-",$value);
            //先查wbw block 拿到block id
            $block = WbwBlock::where('book_id',$sentId[0])
                        ->where('paragraph',$sentId[1])
                        ->select('uid')
                        ->where('channel_uid',$request->get("channel"))->first();
            if(!$block){
                continue;
            }
            $wbwdata = Wbw::where('book_id',$sentId[0])
                        ->where('paragraph',$sentId[1])
                        ->where('wid','>=',$sentId[2])
                        ->where('wid','<=',$sentId[3])
                        ->where('block_uid',$block->uid)
                        ->get();
            $sent['sid']=$value;
            $sent['data']=[];
            foreach ($wbwdata as  $wbw) {
                # code...
                $data = str_replace("&nbsp;",' ',$wbw->data);
                $data = str_replace("<br>",' ',$data);

                $xmlString = "<root>" . $data . "</root>";
                try{
                    $xmlWord = simplexml_load_string($xmlString);
                }catch(Exception $e){
                    continue;
                }

                $wordsList = $xmlWord->xpath('//word');
                foreach ($wordsList as $word) {
                    $type = $word->type->__toString();
                    $style = $word->style->__toString();
                    if($type !== '.ctl.' && $style !== 'note'){
                        $sent['data'][]=[
                            'pali'=>$word->real->__toString(),
                            'mean' => $word->mean->__toString(),
                            'type' => ltrim($type,'.'),
                            'grammar' => ltrim(str_replace('$.',',',$word->gramma->__toString()),'.') ,
                            'case' => ltrim(str_replace(['$.','#.'],[' ',' '],$word->case->__toString()),'.') ,
                            'parent' => $word->parent->__toString(),
                            'factors' => $word->org->__toString(),
                            'factormeaning' => $word->om->__toString()
                        ];
                    }

                }
            }
            $output[]=$sent;
        }
        return view('export_wbw',['sentences' => $output] );
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
     * @param  \App\Models\Wbw  $wbw
     * @return \Illuminate\Http\Response
     */
    public function show(Wbw $wbw)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Wbw  $wbw
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Wbw $wbw)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Wbw  $wbw
     * @return \Illuminate\Http\Response
     */
    public function destroy(Wbw $wbw)
    {
        //
    }
}
