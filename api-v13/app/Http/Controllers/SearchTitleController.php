<?php

namespace App\Http\Controllers;

use App\Http\Resources\SearchTitleIndexResource;
use App\Models\PaliText;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SearchTitleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $key = strtolower($request->input('key'));
        $table = PaliText::where('level', '<', 8)
            ->where(function ($query) use ($key) {
                $query->where('title_en', 'like', "%{$key}%")
                    ->orWhere('title', 'like', "%{$key}%");
            });
        $count = $table->count();
        $table = $table->orderBy('title_en');
        $table = $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 10));

        $result = $table->get();

        return $this->ok(['rows' => SearchTitleIndexResource::collection($result), 'count' => $count]);
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
    public function show(PaliText $paliText)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, PaliText $paliText)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(PaliText $paliText)
    {
        //
    }
}
