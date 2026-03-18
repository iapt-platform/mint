<?php

namespace App\Services;

use App\Models\PaliText;
use Illuminate\Support\Facades\Log;

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
    public function getBookPara(int $book, int $para)
    {
        $paragraph = PaliText::where('book', $book)
            ->where('paragraph', '<=', $para)
            ->where('level', 1)
            ->orderBy('paragraph', 'asc')->first();
        if ($paragraph) {
            return $paragraph;
        } else {
            Log::error('not found book ', ['book' => $book, 'para' => $para]);
            return null;
        }
    }
    public function getParaCategoryTags(int $book, int $para): array
    {
        $bookPara = self::getBookPara($book, $para);
        if (!$bookPara) {
            return [];
        }
        if (isset($bookPara->uid) && $bookPara->uid) {
            return app(TagService::class)->getTagsName($bookPara->uid);
        } else {
            Log::error('book uid is null', ['book' => $book, 'para' => $para]);
            return [];
        }
    }
    public function getParaInfo(int $book, int $para)
    {
        return PaliText::where('book', $book)
            ->where('paragraph',  $para)
            ->first();
    }
    public function getParaPathTitle(int $book, int $para)
    {
        $para = self::getParaInfo($book, $para);
        return array_map(function ($item) {
            return $item->title;
        }, json_decode($para->path));
    }
}
