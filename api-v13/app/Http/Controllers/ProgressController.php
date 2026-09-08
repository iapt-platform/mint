<?php

namespace App\Http\Controllers;

use App\Models\ProgressChapter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProgressController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $select = [
            'progress_chapters.book',
            'progress_chapters.para',
            'progress_chapters.lang',
            'progress_chapters.progress',
            'progress_chapters.channel_id',
            'progress_chapters.title',
            'progress_chapters.last_chapter_completed_at',
            'progress_chapters.completed_at',
            'progress_chapters.updated_at',
        ];
        switch ($request->input('view')) {
            case 'channel':
                $table = ProgressChapter::select($select)
                    ->whereIn('progress_chapters.channel_id', explode('_', $request->input('channels', '')));
                break;
            default:
                return $this->error('invalid view', 400, 400);
                break;
        }

        if ($request->filled('level')) {
            $table = $table->leftJoin('pali_texts', function ($join) {
                $join->on('progress_chapters.book', '=', 'pali_texts.book')
                    ->on('progress_chapters.para', '=', 'pali_texts.paragraph');
            })->where('pali_texts.level', '<=', (int) $request->input('level'));
        }

        if ($request->has('lang')) {
            $table = $table->where('progress_chapters.lang', $request->input('lang'));
        }
        if ($request->has('book')) {
            $table = $table->where('progress_chapters.book', $request->input('book'));
        }
        $count = $table->count();

        $table = $table->orderBy(
            'progress_chapters.'.$request->input('order', 'completed_at'),
            $request->input('dir', 'desc')
        );

        $table = $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 10));

        $result = $table->get();

        return $this->ok(
            [
                'rows' => $result->toArray(),
                'total' => $count,
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
