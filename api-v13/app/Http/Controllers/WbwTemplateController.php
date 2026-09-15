<?php

namespace App\Http\Controllers;

use App\Http\Resources\WbwTemplateResource;
use App\Models\WbwTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WbwTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->input('view')) {
            case 'para':
                $wbw = WbwTemplate::where('book', $request->input('book'))
                    ->where('paragraph', $request->input('paragraph'))
                    ->get(['wid', 'word', 'real']);

                return $this->sendResponse(WbwTemplateResource::collection($wbw), 'ok');
                break;
            case 'word':
                $wbw = WbwTemplate::where('word', $request->input('word'))->get(['book', 'paragraph']);

                return $this->sendResponse(WbwTemplateResource::collection($wbw), 'ok');
                break;
            case 'page':
                $wbw = WbwTemplate::where('word', 'like', '%'.$request->input('num'))->get(['book', 'paragraph']);

                return $this->sendResponse(WbwTemplateResource::collection($wbw), 'ok');
                break;
        }
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
    public function show($id)
    {
        //
        $para = explode('-', $id);
        $wbw = WbwTemplate::where('book', $para[0])
            ->where('paragraph', $para[1])
            ->where('wid', $para[2])
            ->get();

        return $this->sendResponse(WbwTemplateResource::collection($wbw), 'ok');
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
