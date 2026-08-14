<?php

namespace App\Http\Controllers;

use App\Models\UserDict;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redis;

class PgPaliDictDownloadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $currPage = $request->input('page', 1);
        $path = 'export/fts/pali';
        $filename = $path."/pali-{$currPage}.syn";
        if (Redis::exists($filename)) {
            $content = Redis::get($filename);

            return $this->ok($content);
        } else {
            return $this->error('no file', 200, 200);
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
     * @return Response
     */
    public function show(UserDict $userDict)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, UserDict $userDict)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(UserDict $userDict)
    {
        //
    }
}
