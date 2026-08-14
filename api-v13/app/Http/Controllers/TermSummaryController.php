<?php

namespace App\Http\Controllers;

use App\Http\Api\ChannelApi;
use App\Models\DhammaTerm;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TermSummaryController extends Controller
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
     * @return Response
     */
    public function show(string $id)
    {
        //
        $term = DhammaTerm::where('guid', $id)->first();
        if (! $term) {
            return $this->error('no id');
        }
        if (empty($term->note)) {
            $community_channel = ChannelApi::getSysChannel('_community_term_zh-hans_');
            // 查找社区解释
            $note = DhammaTerm::where('word', $term->word)
                ->where('channal', $community_channel)
                ->value('note');
        } else {
            $note = $term->note;
        }
        // 替换术语
        $pattern = "/\[\[(.+?)\]\]/";
        $replacement = '$1';
        $html = preg_replace($pattern, $replacement, $note);

        $pattern = "/\{\{(.+?)\}\}/";
        $replacement = '';
        $html = preg_replace($pattern, $replacement, $html);

        $html = mb_substr($html, 0, 500, 'UTF-8');

        return $this->ok($html);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, DhammaTerm $dhammaTerm)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(DhammaTerm $dhammaTerm)
    {
        //
    }
}
