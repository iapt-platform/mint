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
        //
        $url = 'https://api.moonshot.cn/v1/chat/completions';
        $param = [
                "model" => "moonshot-v1-8k",
                "messages" => [
                    "role" => "user",
                     "content" => "你好，我叫李雷，1+1等于多少？",
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
