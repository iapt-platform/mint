<?php
namespace App\Http\Api;

use App\Models\SentPr;
use App\Http\Api\PaliTextApi;

class SuggestionApi{
    public static function getCountBySent($book,$para,$start,$end,$channel,$type="suggestion"){
        $count['suggestion'] = SentPr::where('book_id',$book)
                                    ->where('paragraph',$para)
                                    ->where('word_start',$start)
                                    ->where('word_end',$end)
                                    ->where('channel_uid',$channel)
                                    ->count();
        return $count;
    }
}
