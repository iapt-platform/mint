<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redis;

class ApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show(Request $request, $id)
    {
        //
        $times = $id;
        $currTime = time();
        $key = 'pref-s/';
        $begin = $currTime - $times - 1;
        $value = 0;
        for ($i = $begin; $i <= $currTime; $i++) {
            $keyApi = $key.$request->input('api', 'all').'/'.$i;
            if (! empty(Redis::get($keyApi.'/delay'))) {
                if ($request->input('item') === 'average') {
                    $value += intval(Redis::get($keyApi.'/delay') / Redis::get($keyApi.'/count'));
                } else {
                    $value += (int) Redis::get($keyApi.'/'.$request->input('item'));
                }
            }
        }
        $value = $value / $times;

        return $this->ok($value);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
        $currMinute = intval(time() / 60);
        $key = 'pref-m/';
        $begin = $currMinute - 60;
        $output = [];
        for ($i = $begin; $i <= $currMinute; $i++) {
            $value = 0;
            $keyApi = $key.$request->input('api', 'all').'/'.$i;
            if (! empty(Redis::get($keyApi.'/delay'))) {
                if ($request->input('item') === 'average') {
                    $value += intval(Redis::get($keyApi.'/delay') / Redis::get($keyApi.'/count'));
                } else {
                    $value += (int) Redis::get($keyApi.'/'.$request->input('item'));
                }
            } else {
                $value = 0;
            }
            $time = date('H:i:s', $i);
            $output[] = ['date' => $time, 'value' => $value];
        }

        return $this->ok($output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
