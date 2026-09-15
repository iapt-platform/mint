<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class AssetsController extends Controller
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
     * @param  string  $bucket  $name
     * @return Response
     */
    public function show($bucket, $name)
    {
        //
        $filename = $bucket.'/'.$name;
        if (Storage::missing($filename)) {
            return $this->error('404', 404, 404);
        }
        // header("Content-Type: {$type1}/{$type1}");
        if (App::environment('local')) {
            $url = Storage::url($filename);
        } else {
            $url = Storage::temporaryUrl($filename, now()->addDays(2));
        }

        return redirect($url);
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
