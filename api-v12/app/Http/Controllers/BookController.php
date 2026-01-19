<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ProgressChapter;
use App\Models\PaliText;
use App\Models\Sentence;

class BookController extends Controller
{
    protected $maxChapterLen = 50000;
    protected $minChapterLen = 100;
    public function show($id)
    {
        $bookRaw = $this->loadBook($id);

        if (!$bookRaw) {
            abort(404);
        }

        //查询章节
        $channelId = $bookRaw->channel_id; // 替换为具体的 channel_id 值

        $book = $this->getBookInfo($bookRaw);
        $book['contents'] = $this->getBookToc($bookRaw->book, $bookRaw->para, $channelId);

        // 获取其他版本
        $others = ProgressChapter::with('channel.owner')
            ->where('book', $bookRaw->book)
            ->where('para', $bookRaw->para)
            ->whereHas('channel', function ($query) {
                $query->where('status', 30);
            })
            ->where('progress', '>', 0.2)
            ->get();

        $otherVersions = [];
        $others->each(function ($book) use (&$otherVersions, $id) {
            $otherVersions[] = $this->getBookInfo($book);
        });

        return view('library.book.show', compact('book', 'otherVersions'));
    }


    public function read($id)
    {
        $bookRaw = $this->loadBook($id);

        if (!$bookRaw) {
            abort(404);
        }
        $channelId = $bookRaw->channel_id; // 替换为具体的 channel_id 值

        $book = $this->getBookInfo($bookRaw);
        $book['toc'] = $this->getBookToc($bookRaw->book, $bookRaw->para, $channelId, 2, 7);
        $book['categories'] = $this->getBookCategory($bookRaw->book, $bookRaw->para);
        $book['tags'] = [];
        $book['pagination'] = $this->pagination($bookRaw);
        $book['content'] = $this->getBookContent($id);
        return view('library.book.read', compact('book'));
    }

    private function loadBook($id)
    {
        $book = ProgressChapter::with('channel.owner')->find($id);
        return $book;
    }

    public function toggleTheme(Request $request)
    {
        $theme = $request->input('theme', 'light');
        session(['theme' => $theme]);
        return response()->json(['status' => 'success']);
    }

    private function getBookInfo($book)
    {
        $title = $book->title;
        if (empty($title)) {
            $title = PaliText::where('book', $book->book)
                ->where('paragraph', $book->para)->first()->toc;
        }
        return [
            "id" => $book->uid,
            "title" => $title,
            "author" => $book->channel->name,
            "publisher" => $book->channel->owner,
            "type" => __('label.' . $book->channel->type),
            "category_id" => 11,
            "cover" => "/assets/images/cover/1/214.jpg",
            "description" => $book->summary ?? "",
            "language" => __('language.' . $book->channel->lang),
        ];
    }

    private function getBookToc(int $book, int $paragraph, string $channelId, $minLevel = 2, $maxLevel = 2)
    {
        //先找到书的起始（书名）章节
        //一个book 里面可以有多本书
        $currBook = $this->bookStart($book, $paragraph);
        $start = $currBook->paragraph;
        $end = $currBook->paragraph + $currBook->chapter_len - 1;
        $paliTexts = PaliText::where('book', $book)
            ->whereBetween('paragraph',  [$start, $end])
            ->whereBetween('level', [$minLevel, $maxLevel])->orderBy('paragraph')->get();

        $chapters = ProgressChapter::where('book', $book)
            ->whereBetween('para', [$start, $end])
            ->where('channel_id', $channelId)->orderBy('para')->get();

        $toc = $paliTexts->map(function ($paliText) use ($chapters) {
            $title = $paliText->toc;
            if (count($chapters) > 0) {
                $found = array_filter($chapters->toArray(), function ($chapter) use ($paliText) {
                    return $chapter['book'] == $paliText->book && $chapter['para'] == $paliText->paragraph;
                });
                if (count($found) > 0) {
                    $chapter = array_shift($found);
                    if (!empty($chapter['title'])) {
                        $title = $chapter['title'];
                    }
                    if (!empty($chapter['summary'])) {
                        $summary = $chapter['summary'];
                    }
                    $progress = (int)($chapter['progress'] * 100);
                    $id = $chapter['uid'];
                }
            }
            return [
                "id" => $id ?? '',
                "title" => $title,
                "summary" => $summary ?? "",
                "progress" => $progress ?? 0,
                "level" => (int)$paliText->level,
                "disabled" => !isset($progress),
            ];
        })->toArray();
        return $toc;
    }

    public function getBookCategory($book, $paragraph)
    {
        $tags = PaliText::with('tagMaps.tags')
            ->where('book', $book)
            ->where('paragraph', $paragraph)
            ->first()->tagMaps->map(function ($tagMap) {
                return $tagMap->tags;
            })->toArray();
        return $tags;
    }

    private function bookStart($book, $paragraph)
    {
        $currBook = PaliText::where('book', $book)
            ->where('paragraph', '<=', $paragraph)
            ->where('level', 1)
            ->orderBy('paragraph', 'desc')
            ->first();
        return $currBook;
    }
    public function getBookContent($id)
    {
        //查询book信息
        $book = $this->loadBook($id);
        $currBook = $this->bookStart($book->book, $book->para);
        $start = $currBook->paragraph;
        $end = $currBook->paragraph + $currBook->chapter_len - 1;
        // 查询起始段落
        $paragraphs = PaliText::where('book', $book->book)
            ->whereBetween('paragraph', [$start, $end])
            ->where('level', '<', 8)
            ->orderBy('paragraph')
            ->get();
        $curr = $paragraphs->firstWhere('paragraph', $book->para);
        $endParagraph = $curr->paragraph + $curr->chapter_len - 1;
        if ($curr->chapter_strlen > $this->maxChapterLen) {
            //太大了，修改结束位置 找到下一级
            foreach ($paragraphs as $key => $paragraph) {
                if ($paragraph->paragraph > $curr->paragraph) {
                    if ($paragraph->chapter_strlen <= $this->maxChapterLen) {
                        $endParagraph = $paragraph->paragraph + $paragraph->chapter_len - 1;
                        break;
                    }
                    if ($paragraph->level <= $curr->level) {
                        //不能往下走了，就是它了
                        $endParagraph = $paragraphs[$key - 1]->paragraph + $paragraphs[$key - 1]->chapter_len - 1;
                        break;
                    }
                }
            }
        }

        //获取句子数据
        $sentences = Sentence::where('book_id', $book->book)
            ->whereBetween('paragraph', [$curr->paragraph, $endParagraph])
            ->where('channel_uid', $book->channel_id)
            ->orderBy('paragraph')
            ->orderBy('word_start')
            ->get();
        $pali = PaliText::where('book', $book->book)
            ->whereBetween('paragraph', [$curr->paragraph, $endParagraph])
            ->orderBy('paragraph')
            ->get();
        $result = [];
        for ($i = $curr->paragraph; $i <= $endParagraph; $i++) {
            $texts = array_filter($sentences->toArray(), function ($sentence) use ($i) {
                return $sentence['paragraph'] == $i;
            });
            $contents = array_map(function ($text) {
                return $text['content'];
            }, $texts);
            $currPali = $pali->firstWhere('paragraph', $i);
            $paragraph = [
                'id' => $i,
                'level' => $currPali->level,
                'text' => [[implode('', $contents)]],
            ];
            $result[] = $paragraph;
        }

        return $result;
    }

    public function pagination($book)
    {
        $currBook = $this->bookStart($book->book, $book->para);
        $start = $currBook->paragraph;
        $end = $currBook->paragraph + $currBook->chapter_len - 1;
        // 查询起始段落
        $paragraphs = PaliText::where('book', $book->book)
            ->whereBetween('paragraph', [$start, $end])
            ->where('level', '<', 8)
            ->orderBy('paragraph')
            ->get();
        $curr = $paragraphs->firstWhere('paragraph', $book->para);
        $current = $curr; //实际显示的段落
        $endParagraph = $curr->paragraph + $curr->chapter_len - 1;
        if ($curr->chapter_strlen > $this->maxChapterLen) {
            //太大了，修改结束位置 找到下一级
            foreach ($paragraphs as $key => $paragraph) {
                if ($paragraph->paragraph > $curr->paragraph) {
                    if ($paragraph->chapter_strlen <= $this->maxChapterLen) {
                        $endParagraph = $paragraph->paragraph + $paragraph->chapter_len - 1;
                        $current = $paragraph;
                        break;
                    }
                    if ($paragraph->level <= $curr->level) {
                        //不能往下走了，就是它了
                        $endParagraph = $paragraphs[$key - 1]->paragraph + $paragraphs[$key - 1]->chapter_len - 1;
                        $current = $paragraph;
                        break;
                    }
                }
            }
        }
        $start = $curr->paragraph;
        $end = $endParagraph;
        $nextPali = $this->next($current->book, $current->paragraph, $current->level);
        $prevPali = $this->prev($current->book, $current->paragraph, $current->level);

        $next = null;
        if ($nextPali) {
            $nextTranslation = ProgressChapter::with('channel.owner')
                ->where('book', $nextPali->book)
                ->where('para', $nextPali->paragraph)
                ->where('channel_id', $book->channel_id)
                ->first();
            if ($nextTranslation) {
                if (!empty($nextTranslation->title)) {
                    $next['title'] = $nextTranslation->title;
                } else {
                    $next['title'] = $nextPali->toc;
                }
                $next['id'] = $nextTranslation->uid;
            }
        }

        $prev = null;
        if ($prevPali) {
            $prevTranslation = ProgressChapter::with('channel.owner')
                ->where('book', $prevPali->book)
                ->where('para', $prevPali->paragraph)
                ->where('channel_id', $book->channel_id)
                ->first();
            if ($prevTranslation) {
                if (!empty($prevTranslation->title)) {
                    $prev['title'] = $prevTranslation->title;
                } else {
                    $prev['title'] = $prevPali->toc;
                }
                $prev['id'] = $prevTranslation->uid;
            }
        }

        return compact('start', 'end', 'next', 'prev');
    }

    public function next($book, $paragraph, $level)
    {
        $next = PaliText::where('book', $book)
            ->where('paragraph', '>', $paragraph)
            ->where('level', $level)
            ->orderBy('paragraph')
            ->first();
        return $next ?? null;
    }
    public function prev($book, $paragraph, $level)
    {
        $prev = PaliText::where('book', $book)
            ->where('paragraph', '<', $paragraph)
            ->where('level', $level)
            ->orderBy('paragraph', 'desc')
            ->first();

        return $prev ?? null;
    }
    public function show2($id)
    {
        // Sample book data (replace with database query)
        $book = [
            'title' => 'Sample Book Title',
            'author' => 'John Doe',
            'category' => 'Fiction',
            'tags' => ['Adventure', 'Mystery', 'Bestseller'],
            'toc' => ['Introduction', 'Chapter 1', 'Chapter 2', 'Conclusion'],
            'content' => [
                'This is the introduction to the book...',
                'Chapter 1 content goes here...',
                'Chapter 2 content goes here...',
                'Conclusion of the book...',
            ],
            'downloads' => [
                ['format' => 'PDF', 'url' => '#'],
                ['format' => 'EPUB', 'url' => '#'],
                ['format' => 'MOBI', 'url' => '#'],
            ],
        ];

        // Sample related books (replace with database query)
        $relatedBooks = [
            [
                'title' => 'Related Book 1',
                'description' => 'A thrilling adventure...',
                'image' => 'https://via.placeholder.com/300x200',
                'link' => '#',
            ],
            [
                'title' => 'Related Book 2',
                'description' => 'A mystery novel...',
                'image' => 'https://via.placeholder.com/300x200',
                'link' => '#',
            ],
            [
                'title' => 'Related Book 3',
                'description' => 'A bestseller...',
                'image' => 'https://via.placeholder.com/300x200',
                'link' => '#',
            ],
        ];

        return view('library.book.read2', compact('book', 'relatedBooks'));
    }
}
