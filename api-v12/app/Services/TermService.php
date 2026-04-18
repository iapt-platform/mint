<?php

namespace App\Services;

use App\Models\DhammaTerm;
use App\Http\Api\ChannelApi;
use App\Http\Resources\TermResource;


class TermService
{
    public function getCommunityGlossary($lang)
    {
        $localTermChannel = ChannelApi::getSysChannel(
            "_community_term_" . strtolower($lang) . "_",
            "_community_term_en_"
        );
        $result = DhammaTerm::select(['word', 'tag', 'meaning', 'other_meaning'])
            ->where('channal', $localTermChannel)
            ->get();
        return ['items' => $result, 'total' => count($result)];
    }
    public function getGrammarGlossary($lang)
    {
        $localTermChannel = ChannelApi::getSysChannel(
            "_System_Grammar_Term_" . strtolower($lang) . "_",
            "_System_Grammar_Term_en_"
        );
        $result = DhammaTerm::select(['word', 'tag', 'meaning', 'other_meaning'])
            ->where('channal', $localTermChannel)
            ->get();
        return ['items' => $result, 'total' => count($result)];
    }

    public function getRaw($id)
    {
        $result = DhammaTerm::find($id);
        return $result;
    }

    public function communityTerm(string $word, string $lang)
    {
        $localTermChannel = ChannelApi::getSysChannel(
            "_community_term_" . strtolower($lang) . "_"
        );
        $result = DhammaTerm::where('word', $word)
            ->where('channal', $localTermChannel)
            ->first();
        if ($result) {
            return new TermResource($result);
        } else {
            return null;
        }
    }
    public function communityTerms(string $lang)
    {
        $localTermChannel = ChannelApi::getSysChannel(
            "_community_term_" . strtolower($lang) . "_"
        );
        $result = DhammaTerm::where('channal', $localTermChannel)
            ->whereNotNull('note')
            ->where('note', '<>', '')
            ->take(10)
            ->orderBy('updated_at', 'desc')
            ->get();
        return [
            "data" => TermResource::collection($result),
            "count" => 10
        ];
    }
}
