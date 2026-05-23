<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProgressChapter;

class ProgressController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->input('view')) {
            case 'channel':
                $table = ProgressChapter::whereIn('channel_id', explode('_', $request->get('channels', '')));
                break;
            default:
                return $this->error('invalid view', 400, 400);
                break;
        }

        if ($request->has('lang')) {
            $table = $table->where('lang', $request->get('lang'));
        }
        $count = $table->count();

        $table = $table->orderBy(
            $request->input('order', 'completed_at'),
            $request->input('dir', 'desc')
        );

        $table = $table->skip($request->input("offset", 0))
            ->take($request->input('limit', 10));

        $result = $table->get();

        return $this->ok(
            [
                "rows" => $result->toArray(),
                "total" => $count,
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
