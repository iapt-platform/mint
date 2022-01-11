<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WbwTemplate;
use App\Http\Resources\WbwTemplateResource;


class WbwTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
		switch($request->get('view')){
			case "para":
				$wbw = WbwTemplate::where("book",$request->get('book'))
									->where("paragraph",$request->get('paragraph'))
									->get(['wid','word','real']);
				return $this->sendResponse(WbwTemplateResource::collection($wbw),"ok");
				break;
			case "word":
				$wbw = WbwTemplate::where("word",$request->get('word'))->get(['book','paragraph']);
				return $this->sendResponse(WbwTemplateResource::collection($wbw),"ok");
				break;
			case "page":
				$wbw = WbwTemplate::where("word","like","%".$request->get('num'))->get(['book','paragraph']);
				return $this->sendResponse(WbwTemplateResource::collection($wbw),"ok");
				break;
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
		$para = explode('-',$id);
		$wbw = WbwTemplate::where("book",$para[0])
		->where("paragraph",$para[1])
		->where("wid",$para[2])
		->get();
		return $this->sendResponse(WbwTemplateResource::collection($wbw),"ok");

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
