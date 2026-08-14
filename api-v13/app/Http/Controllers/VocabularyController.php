<?php

namespace App\Http\Controllers;

use App\Http\Resources\VocabularyResource;
use App\Models\Vocabulary;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class VocabularyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->input('view')) {
            case 'key':
                $key = (string) $request->input('key', '');
                if ($key === '') {
                    return $this->ok(['rows' => [], 'count' => 0]);
                }

                $payload = Cache::remember(
                    'v13:dict_vocabulary:'.md5($key),
                    config('mint.cache.expire'),
                    function () use ($key) {
                        $rows = Vocabulary::where('word', 'like', $key.'%')
                            ->orWhere('word_en', 'like', $key.'%')
                            ->orderBy('strlen')
                            ->orderBy('word')
                            ->take(50)
                            ->get();

                        return [
                            'rows' => VocabularyResource::collection($rows)->resolve(),
                            'count' => $rows->count(),
                        ];
                    }
                );

                return $this->ok($payload);
        }

        return $this->ok(['rows' => [], 'count' => 0]);
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
    public function show(Vocabulary $vocabulary)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, Vocabulary $vocabulary)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Vocabulary $vocabulary)
    {
        //
    }
}
