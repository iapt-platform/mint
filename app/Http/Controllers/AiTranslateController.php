<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiTranslateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

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
        $url = 'https://api.moonshot.cn/v1/chat/completions';
        $param = [
                "model" => "moonshot-v1-8k",
                "messages" => [
                    ["role" => "system","content" => "你是 Kimi，由 Moonshot AI 提供的人工智能助手，你更擅长中文和英文的对话。你会为用户提供安全，有帮助，准确的回答。同时，你会拒绝一切涉及恐怖主义，种族歧视，黄色暴力等问题的回答。Moonshot AI 为专有名词，不可翻译成其他语言。"],
                    ["role" => "user","content" => strip_tags($request->get('origin'))."\n请翻译上述巴利文。"],
                ],
                "temperature" => 0.3,
        ];
        $response = Http::withToken('sk-kwjHIMh3PoWwUwQyKdT3KHvNe8Es19SUiujGrxtH09uDQCui')
                        ->post($url,$param);
        if($response->failed()){
            $this->error('http request error'.$response->json('message'));
            Log::error('http request error', ['data'=>$response->json()]);
            return $this->error($response->json(),[],500);
        }else{
            return $this->ok($response->json());
        }
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
