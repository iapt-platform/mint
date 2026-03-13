<?php

namespace App\Services;

use App\Models\PaliText;

class PaliTextService
{
    public function getParent(int $book, int $para) {}
    public function getCurrChapter(int $book, int $para)
    {
        $paragraph = PaliText::where('book', $book)
            ->where('paragraph', '<=', $para)
            ->where('level', '<', 8)
            ->orderBy('paragraph', 'desc')->first();
        if ($paragraph) {
            return $paragraph;
        } else {
            return null;
        }
    }
}
