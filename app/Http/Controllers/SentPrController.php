<?php

namespace App\Http\Controllers;

use App\Models\SentPr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SentPrController extends Controller
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
        if(!isset($_COOKIE['user_uid'])){
            return $this->error('not login');
        }
        $data = $request->all();
        if($data['channel'] == '7fea264d-7a26-40f8-bef7-bc95102760fb' && $data['book']==65 && $data['para']>2056 && $data['para']<2192){
            $url = "https://oapi.dingtalk.com/robot/send?access_token=34143dbec80a8fc09c1cb5897a5639ee3a9a32ecfe31835ad29bf7013bdb9fdf";
            $param = [
            "actionCard"=> [
                "title"=> "说慧地品", 
                "text"=> " wikipali: 来自{$_COOKIE['user_uid']}的修改建议：{$data['text']}", 
                "btnOrientation"=> "0", 
                "singleTitle" => "详情",
                "singleURL"=>"https://www-hk.wikipali.org/app/artical/?view=para&book={$data['book']}&par={$data['para']}&channel={$data['channel']}"
            ], 
            "msgtype"=>"actionCard"
            ];

            $response = Http::post($url, $param);
            if($response->successful()){
                return $this->ok($response->body);
            }else{
                return $this->error($response->body);
            }            
        }else{
            return $this->ok();
        }

        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SentPr  $sentPr
     * @return \Illuminate\Http\Response
     */
    public function show(SentPr $sentPr)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SentPr  $sentPr
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SentPr $sentPr)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SentPr  $sentPr
     * @return \Illuminate\Http\Response
     */
    public function destroy(SentPr $sentPr)
    {
        //
    }
}
