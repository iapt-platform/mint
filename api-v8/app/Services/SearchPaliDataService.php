<?php

namespace App\Services;

use App\Models\BookTitle;
use App\Models\WbwTemplate;
use App\Models\PaliText;
use App\Models\PaliSentence;

class SearchPaliDataService
{
    /**
     * Retrieve paginated Pali data for search.
     *
     * @param int $book
     * @param int $start
     * @param int $pageSize
     * @return array
     */
    public function getPaliData($book, $start = 1, $pageSize = null)
    {
        $maxParagraph = WbwTemplate::where('book', $book)->max('paragraph');
        $output = [];
        $pageSize = $pageSize === null ? $maxParagraph : $pageSize;
        // Calculate end paragraph for pagination
        $endOfPara = min($start + $pageSize, $maxParagraph + 1);

        for ($iPara = $start; $iPara < $endOfPara; $iPara++) {
            $content = $this->getParaContent($book, $iPara);
            // Retrieve bold words
            $words = WbwTemplate::where('book', $book)
                ->where('paragraph', $iPara)
                ->orderBy('wid')
                ->get();

            $bold1 = [];
            $bold2 = [];
            $bold3 = [];
            $currBold = [];

            foreach ($words as $word) {
                if ($word->style === 'bld') {
                    $currBold[] = $word->real;
                } else {
                    $countBold = count($currBold);
                    if ($countBold === 1) {
                        $bold1[] = $currBold[0];
                    } elseif ($countBold === 2) {
                        $bold2 = array_merge($bold2, $currBold);
                    } elseif ($countBold > 0) {
                        $bold3 = array_merge($bold3, $currBold);
                    }
                    $currBold = [];
                }
            }

            // Handle any remaining bold words
            $countBold = count($currBold);
            if ($countBold === 1) {
                $bold1[] = $currBold[0];
            } elseif ($countBold === 2) {
                $bold2 = array_merge($bold2, $currBold);
            } elseif ($countBold > 0) {
                $bold3 = array_merge($bold3, $currBold);
            }

            // Retrieve book ID
            $pcd_book = BookTitle::where('book', $book)
                ->where('paragraph', '<=', $iPara)
                ->orderBy('paragraph', 'desc')
                ->first();

            $pcd_book_id = $pcd_book ? $pcd_book->sn : BookTitle::where('book', $book)
                ->orderBy('paragraph')
                ->value('sn');

            $output[] = [
                'uid' => PaliText::where('book', $book)->where('paragraph', $iPara)->value('uid'),
                'book' => $book,
                'paragraph' => $iPara,
                'bold1' => implode(' ', $bold1),
                'bold2' => implode(' ', $bold2),
                'bold3' => implode(' ', $bold3),
                'content' => $content['markdown'],
                'markdown' => $content['markdown'],
                'text' => $content['text'],
                'pcd_book_id' => $pcd_book_id
            ];
        }

        return ['rows' => $output, 'count' => $maxParagraph];
    }

    /**
     * Generate content string for a given book and paragraph.
     *
     * @param int $book
     * @param int $para
     * @return string
     */
    private function getContent($book, $para)
    {
        $words = WbwTemplate::where('book', $book)
            ->where('paragraph', $para)
            ->where('type', '<>', '.ctl.')
            ->orderBy('wid')
            ->get();

        $content = '';
        foreach ($words as $word) {
            if ($word->style === 'bld') {
                if (strpos($word->word, '{') === false) {
                    $content .= "**{$word->word}** ";
                } else {
                    $content .= str_replace(['{', '}'], ['**', '** '], $word->word);
                }
            } elseif ($word->style === 'note') {
                $content .= " _{$word->word}_ ";
            } else {
                $content .= $word->word . ' ';
            }
        }

        return trim($content);
    }

    /**
     * Generate paragraph sentence list for a given book and paragraph.
     *
     * @param int $book
     * @param int $para
     * @return array $sentences
     */
    public function getParaContent($book, $para)
    {
        $sentences = PaliSentence::where('book', $book)
            ->where('paragraph', $para)
            ->orderBy('word_begin')
            ->get();
        if (!$sentences) {
            return null;
        }
        $markdown = [];
        $text = [];
        foreach ($sentences as $key => $sentence) {
            $content = $this->getSentenceText($book, $para, $sentence->word_begin, $sentence->word_end);
            $id = "{$book}-{$para}-{$sentence->word_begin}-{$sentence->word_end}";
            $markdown[] = $content['markdown'];
            $text[] = $content['text'];
        }
        return [
            'markdown' => implode("\n", $markdown),
            'text' => implode("", $text),
        ];
    }

    /**
     * Generate paragraph sentence list for a given book and paragraph.
     *
     * @param int $book
     * @param int $para
     * @return array $sentence
     */
    private function getSentenceText($book, $para, $start, $end)
    {
        $words = WbwTemplate::where('book', $book)
            ->where('paragraph', $para)
            ->where('type', '<>', '.ctl.')
            ->whereBetween('wid', [$start, $end])
            ->orderBy('wid')
            ->get();

        $text = [];
        $markdown = '';
        foreach ($words as $word) {
            $text[] = str_replace(['{', '}'], ['', ''], $word->word);
            if ($word->style === 'bld') {
                if (strpos($word->word, '{') === false) {
                    $markdown .= "**{$word->word}** ";
                } else {
                    $markdown .= str_replace(['{', '}'], ['**', '** '], $word->word);
                }
            } elseif ($word->style === 'note') {
                $markdown .= " ~~{$word->word}~~ ";
            } else {
                $markdown .= $word->word . ' ';
            }
        }

        return [
            'markdown' => $this->abbrReplace(trim(str_replace(['~~  ~~', '** **'], [' ', ' '], $markdown))),
            'text' => $this->abbrReplace(implode(' ', $text)),
        ];
    }
    private function abbrReplace($input)
    {
        $abbr = ['sī .', 'syā .', 'kaṃ .', 'pī .'];
        $abbrTo = ['sī.', 'syā.', 'kaṃ.', 'pī.'];
        return str_replace($abbr, $abbrTo, $input);
    }
}
