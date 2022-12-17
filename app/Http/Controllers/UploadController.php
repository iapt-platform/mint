<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

    public function uploadToServer(Request $request)
    {
        $request->validate([
            'file' => 'required',
        ]);

       $filename = time().'.'.request()->file->getClientOriginalExtension();

       $request->file->move(public_path('uploads'), $filename);
/*
       $file = new FileUpload;
       $file->name = $name;
       $file->save();

*/
    $json['files'][] = array(
        'name' => $filename,
        'size' => $request->file->getSize(),
        'type' => $request->file->getMimeType(),
        'url' => '/uploads/files/'.$filename,
        'deleteType' => 'DELETE',
        'deleteUrl' => self::$route.'/deleteFile/'.$filename,
        );
        return Response::json($json);
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
