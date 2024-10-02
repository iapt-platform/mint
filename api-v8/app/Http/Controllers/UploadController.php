<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;


class UploadController extends Controller
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
        $request->validate([
            'file' => 'required',
        ]);
        $file = $request->file('file');

       //Move Uploaded File
       if($request->get('is_tmp',false) == false){
        $bucket = config('mint.attachments.bucket_name.permanent');
       }else{
        $bucket = config('mint.attachments.bucket_name.temporary');
       }

       $uploadFilename = Str::uuid().'.'.$file->extension();
        $filename = $file->storeAs($bucket,$uploadFilename);

        $json = array(
            'filename' => $bucket.'/'.$uploadFilename,
            'size' => $file->getSize(),
            'type' => $file->getMimeType(),
            'url' => storage_path('app/'.$filename),
            'uid' => Str::uuid(),
            );
        return $this->ok($json);
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
