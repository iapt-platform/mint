<?php

namespace App\Http\Controllers;

use App\Models\DictInfo;
use Illuminate\Http\Request;
use App\Http\Resources\DictInfoResource;

class DictInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->input('view')) {
            case 'name':
                $table = DictInfo::where('name', $request->input('name'));
                break;

            default:
                # code...
                break;
        }

        $table = $table->orderBy(
            $request->input('order', 'updated_at'),
            $request->input('dir', 'desc')
        );

        $table = $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 100));

        $result = $table->get();
        $count = count($result);
        return $this->ok([
            "rows" => DictInfoResource::collection($result),
            "count" => $count
        ]);
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
     * @param  \App\Models\DictInfo  $dictInfo
     * @return \Illuminate\Http\Response
     */
    public function show(DictInfo $dictInfo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DictInfo  $dictInfo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DictInfo $dictInfo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DictInfo  $dictInfo
     * @return \Illuminate\Http\Response
     */
    public function destroy(DictInfo $dictInfo)
    {
        //
    }
}
