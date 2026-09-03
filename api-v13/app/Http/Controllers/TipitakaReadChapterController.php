<?php

namespace App\Http\Controllers;

use App\Models\PaliText;
use App\Services\PaliContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TipitakaReadChapterController extends Controller
{
    /**
     * 阅读模式章节内容。输入章节起始 book/para，按 pagesize 分批返回段落。
     *
     * pagesize 两种写法：
     *   - `20000b` 按字节，累加 pali_texts.lenght 直到超过上限（每页至少一段）
     *   - `10p`    按段落数
     */
    public function index(Request $request, PaliContentService $paliService): JsonResponse
    {
        $data = $request->validate([
            'book' => 'required|integer',
            'para' => 'required|integer',
            'channel' => 'required|uuid',
            'format' => 'string|in:html,markdown,react,text',
            'view' => 'string|in:display,sentences,all',
            'pagesize' => ['string', 'regex:/^\d+[bp]$/'],
            'page' => 'integer|min:1',
        ]);

        return $this->chapter(
            (int) $data['book'],
            (int) $data['para'],
            $data['channel'],
            $data,
            $paliService
        );
    }

    /**
     * 同 index，id 格式 {book}-{para}
     */
    public function show(Request $request, string $id, PaliContentService $paliService): JsonResponse
    {
        $arrId = explode('-', $id);
        if (count($arrId) !== 2 || ! is_numeric($arrId[0]) || ! is_numeric($arrId[1])) {
            return $this->error('invalid id');
        }
        $channel = $request->input('channel');
        if (! Str::isUuid($channel)) {
            return $this->error('invalid channel');
        }

        return $this->chapter(
            (int) $arrId[0],
            (int) $arrId[1],
            $channel,
            $request->only(['format', 'view', 'pagesize', 'page']),
            $paliService
        );
    }

    /**
     * @param  array{format?: string, view?: string, pagesize?: string, page?: int|string}  $param
     */
    protected function chapter(
        int $book,
        int $para,
        string $channel,
        array $param,
        PaliContentService $paliService
    ): JsonResponse {
        $format = $param['format'] ?? 'html';
        $view = $param['view'] ?? 'display';
        $pageSize = $param['pagesize'] ?? '10p';
        $page = max(1, (int) ($param['page'] ?? 1));

        $chapter = PaliText::where('book', $book)->where('paragraph', $para)->first();
        if (! $chapter) {
            return $this->error('chapter not found');
        }
        $to = $para + max(1, (int) $chapter->chapter_len) - 1;

        /** @var array<int, array{paragraph: int, lenght: int}> $paragraphs */
        $paragraphs = PaliText::where('book', $book)
            ->whereBetween('paragraph', [$para, $to])
            ->orderBy('paragraph')
            ->get(['paragraph', 'lenght'])
            ->all();
        $total = count($paragraphs);
        if ($total === 0) {
            return $this->error('chapter is empty');
        }

        $slice = $this->slice($paragraphs, $pageSize, $page);
        if ($slice === null) {
            return $this->error('page out of range');
        }

        $items = [];
        foreach ($slice as $row) {
            $paragraph = $paliService->readParagraph($book, (int) $row->paragraph, $channel, $format);
            if (empty($paragraph['display'])) {
                continue;
            }
            $items[] = $this->filterView($paragraph, $view);
        }

        $first = $slice[0]->paragraph;
        $last = $slice[count($slice) - 1]->paragraph;

        return $this->ok([
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'pageSize' => $pageSize,
                'total' => $total,
                'book' => $book,
                'from' => (int) $first,
                'to' => (int) $last,
                'hasMore' => $last < $paragraphs[$total - 1]->paragraph,
            ],
        ]);
    }

    /**
     * 取第 $page 批段落。按段落数时直接切片，按字节时从头累加 lenght 逐页推进。
     *
     * @param  array<int, PaliText>  $paragraphs
     * @return array<int, PaliText>|null 页码越界时返回 null
     */
    protected function slice(array $paragraphs, string $pageSize, int $page): ?array
    {
        $limit = (int) substr($pageSize, 0, -1);
        $unit = substr($pageSize, -1);
        if ($limit < 1) {
            return null;
        }

        if ($unit === 'p') {
            $offset = ($page - 1) * $limit;
            $slice = array_slice($paragraphs, $offset, $limit);

            return $slice === [] ? null : $slice;
        }

        // 字节模式：每页累加 lenght，超过上限即断页，每页至少一段
        $offset = 0;
        $count = count($paragraphs);
        for ($current = 1; $offset < $count; $current++) {
            $bytes = 0;
            $take = 0;
            while ($offset + $take < $count) {
                $bytes += (int) $paragraphs[$offset + $take]->lenght;
                $take++;
                if ($bytes >= $limit) {
                    break;
                }
            }
            if ($current === $page) {
                return array_slice($paragraphs, $offset, $take);
            }
            $offset += $take;
        }

        return null;
    }

    /**
     * 按 view 裁剪输出。display 只要段落 html，sentences 只要句子列表，all 两者都要。
     *
     * @param  array{para: int, display: string, sentences: array}  $paragraph
     */
    protected function filterView(array $paragraph, string $view): array
    {
        return match ($view) {
            'sentences' => ['para' => $paragraph['para'], 'sentences' => $paragraph['sentences']],
            'all' => $paragraph,
            default => ['para' => $paragraph['para'], 'display' => $paragraph['display']],
        };
    }
}
