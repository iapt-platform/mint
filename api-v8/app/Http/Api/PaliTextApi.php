<?php
namespace App\Http\Api;

use App\Models\PaliText;

class PaliTextApi{
    public static function getChapterStartEnd($book,$para){
        $chapter = PaliText::where('book',$book)
                        ->where('paragraph',$para)
                        ->first();
        if(!$chapter){
            return false;
        }
        $start = $para;
        $end = $para + $chapter->chapter_len -1;
        return [$start,$end];
    }

    public static function getChapterPath($book,$para){
        $path = PaliText::where('book',$book)
                        ->where('paragraph',$para)
                        ->value('path');
        return $path;
    }
}
