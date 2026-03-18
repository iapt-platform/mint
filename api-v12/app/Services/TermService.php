<?php

namespace App\Services;

use App\Models\DhammaTerm;
use App\Http\Api\ChannelApi;


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
}
