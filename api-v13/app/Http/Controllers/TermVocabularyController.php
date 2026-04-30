<?php

namespace App\Http\Controllers;

use App\Models\DhammaTerm;
use Illuminate\Http\Request;
use App\Http\Resources\TermVocabularyResource;
use App\Services\TermService;

class TermVocabularyController extends Controller
{
    protected TermService $termService;

    public function __construct(TermService $termService)
    {
        $this->termService = $termService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // ✅ 数据验证
        $validated = $request->validate([
            'view' => ['required', 'string'],
            'lang' => ['nullable', 'string'],
        ]);

        $view = $validated['view'];
        $lang = $validated['lang'] ?? null;

        // ✅ 使用 match 替代 switch
        $data = match ($view) {
            'grammar'   => $this->termService->getGrammarGlossary($lang),
            'community' => $this->termService->getCommunityGlossary($lang),
            'studio', 'user' => throw new \Exception('not implemented'),
            default     => throw new \InvalidArgumentException('invalid view'),
        };

        return $this->ok([
            'rows'  => TermVocabularyResource::collection($data['items']),
            'count' => $data['total'],
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
     * @param  \App\Models\DhammaTerm  $dhammaTerm
     * @return \Illuminate\Http\Response
     */
    public function show(DhammaTerm $dhammaTerm)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DhammaTerm  $dhammaTerm
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DhammaTerm $dhammaTerm)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DhammaTerm  $dhammaTerm
     * @return \Illuminate\Http\Response
     */
    public function destroy(DhammaTerm $dhammaTerm)
    {
        //
    }
}
