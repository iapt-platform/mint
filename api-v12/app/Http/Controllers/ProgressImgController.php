<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Tools\RedisClusters;
use Illuminate\Support\Facades\Log;

class ProgressImgController extends Controller
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
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        return response()->stream(function () use ($id) {
            $key = str_replace('-','/',$id);
            $svg = RedisClusters::get('svg/'.$key, function () use ($key) {
                $viewHeight = 60;
                $svg = "<svg xmlns='http://www.w3.org/2000/svg'  fill='currentColor' viewBox='0 0 300 60'>";
                $data = RedisClusters::get($key);
                if(is_array($data)){
                    $point = [];
                    foreach ($data as $key => $value) {
                        $point[] = ($key*10) . ',' . $viewHeight-($value/20)-3;
                    }
                    $svg .= '<polyline points="'. implode(' ',$point) . '"';
                    $svg .= ' style="fill:none;stroke:green;stroke-width:3" /></svg>';
                }else{
                    $svg .= '<polyline points="0,0 1,0" /></svg>';
                }
                return $svg;
            } , config('mint.cache.expire') );
            echo $svg;
        }, 200, ['Content-Type' => 'image/svg+xml']);
    /*
    ————————————————
    原文作者：Summer
    转自链接：https://learnku.com/laravel/wikis/25600
    版权声明：著作权归作者所有。商业转载请联系作者获得授权，非商业转载请保留以上作者信息和原文链接。
    */
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
