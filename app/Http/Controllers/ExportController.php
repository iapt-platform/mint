<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Api\Mq;
use Illuminate\Support\Facades\Log;
use App\Tools\RedisClusters;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;


class ExportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $filename = Str::uuid().'.'.$request->get('format');
        Mq::publish('export',[
            'book'=>$request->get('book'),
            'para'=>$request->get('par'),
            'channel'=>$request->get('channel'),
            'format'=>$request->get('format'),
            'filename'=>$filename,
        ]);
        return $this->ok($filename);
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
        if(RedisClusters::has('export/done/'.$filename)){
            if (App::environment('local')) {
                $s3Link = Storage::url('export/'.$filename);
            }else{
                try{
                    $s3Link = Storage::temporaryUrl('export/'.$filename, now()->addDays(1));
                }catch(\Exception $e){
                    Log::error('export {Exception}',['exception'=>$e]);
                    return $this->error('temporaryUrl fail',404,404);
                }
            }
            Log::info('export: link='.$s3Link);

        }else{
            return $this->error('no file',200,200);
        }
        return $this->ok(['link'=>$s3Link]);
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
