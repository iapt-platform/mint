<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use Illuminate\Http\Request;

class ChannelIOController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $table = Channel::select(['uid','name','summary',
                                'type','owner_uid','lang',
                                'status','updated_at','created_at']);
        switch ($request->get('view')) {
            case 'public':
                $table->where('status',30)
                      ->where('updated_at','>',$request->get('updated_at','2000-1-1'));
                break;
        }
        $count = $table->count();
        //处理排序
        $table->orderBy('updated_at','asc');
        //处理分页
        $table->skip($request->get("offset",0))
              ->take($request->get("limit",200));
        //获取数据
        $result = $table->get();
        return $this->ok(["rows"=>$result,"count"=>$count]);
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
     * @param  \App\Models\Channel  $channel
     * @return \Illuminate\Http\Response
     */
    public function show(Channel $channel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Channel  $channel
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Channel $channel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Channel  $channel
     * @return \Illuminate\Http\Response
     */
    public function destroy(Channel $channel)
    {
        //
    }
}
