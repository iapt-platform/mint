<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

use App\Http\Api\AuthApi;
use App\Http\Api\Mq;
use App\Tools\RedisClusters;
use App\Tools\ExportDownload;

class ExportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $queryId = Str::uuid();
        $token = AuthApi::getToken($request);
        switch ($request->get('type','chapter')) {
            case 'chapter':
                $data = [
                    'book'=>$request->get('book'),
                    'para'=>$request->get('par'),
                    'channel'=>$request->get('channel'),
                    'format'=>$request->get('format'),
                    'origin'=>$request->get('origin'),
                    'translation'=>$request->get('translation'),
                    'queryId'=>$queryId,
                ];
                if($token){
                    $data['token'] = $token;
                }
                Mq::publish('export_pali_chapter',$data);
                break;
            case 'article':
                $data = [
                    'id'=>$request->get('id'),
                    'channel'=>$request->get('channel'),
                    'format'=>$request->get('format'),
                    'origin'=>$request->get('origin'),
                    'translation'=>$request->get('translation'),
                    'queryId'=>$queryId,
                    'anthology'=>$request->get('anthology'),
                    'channel'=>$request->get('channel'),
                ];
                if($token){
                    $data['token'] = $token;
                }
                Mq::publish('export_article',$data);
                break;
            default:
                return $this->error('unknown type '.$request->get('type'),400,400);
                break;
        }

        return $this->ok($queryId);
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
    public function show($filename)
    {
        //
        $exportChapter = new ExportDownload(['queryId'=>$filename]);
        $exportStatus = $exportChapter->getStatus();
        if(empty($exportStatus)){
            return $this->error('no file',200,200);
        };

        $output = array();
        $output['status'] = $exportStatus;
        if($exportStatus['progress']===1){
            $output['url'] = $exportStatus['url'];
        }
        return $this->ok($output);

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
