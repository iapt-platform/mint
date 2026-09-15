<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaliBookCategoryController extends Controller
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
     * @param  string  $file
     * @return Response
     */
    public function show($file)
    {
        $data = file_get_contents(public_path("data/category/{$file}.json"));
        if ($data === false) {
            return $this->error('no file');
        }
        $response = json_decode($data);

        return response()->json(
            $response,
            200,
            [
                'Content-Type' => 'application/json;charset=UTF-8',
                'Charset' => 'utf-8',
            ],
            JSON_UNESCAPED_UNICODE
        );
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
