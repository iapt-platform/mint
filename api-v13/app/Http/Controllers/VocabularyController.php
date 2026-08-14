<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Vocabulary;
use App\Http\Resources\VocabularyResource;

class VocabularyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->input("view")) {
            case 'key':
                $key = (string) $request->input('key', '');
                if ($key === '') {
                    return $this->ok(['rows' => [], 'count' => 0]);
                }

                $payload = Cache::remember(
                    'v13:dict_vocabulary:' . md5($key),
                    config('mint.cache.expire'),
                    function () use ($key) {
                        $rows = Vocabulary::where('word', 'like', $key . '%')
                            ->orWhere('word_en', 'like', $key . '%')
                            ->orderBy('strlen')
                            ->orderBy('word')
                            ->take(50)
                            ->get();

                        return [
                            'rows'  => VocabularyResource::collection($rows)->resolve(),
                            'count' => $rows->count(),
                        ];
                    }
                );

                return $this->ok($payload);
        }
        return $this->ok(['rows'=>[],'count'=>0]);
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
     * @param  \App\Models\Vocabulary  $vocabulary
     * @return \Illuminate\Http\Response
     */
    public function show(Vocabulary $vocabulary)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Vocabulary  $vocabulary
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Vocabulary $vocabulary)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Vocabulary  $vocabulary
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vocabulary $vocabulary)
    {
        //
    }
}
