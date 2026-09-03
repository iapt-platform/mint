<?php

namespace App\Http\Controllers;

use App\Models\PaliText;
use App\Services\PaliContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TipitakaContentParaController extends Controller
{
    /**
     * 阅读模式段落内容列表。指定 book 段落区间和 channel
     */
    public function index(Request $request, PaliContentService $paliService): JsonResponse
    {
        $data = $request->validate([
            'book' => 'required|integer',
            'para' => 'required|integer',
            'to' => 'integer',
            'channel' => 'required|uuid',
            'format' => 'string|in:html,markdown,react,text',
        ]);

        $from = $data['para'];
        $to = $data['to'] ?? $from;
        if ($to < $from) {
            return $this->error('invalid paragraph range');
        }
        $format = $data['format'] ?? 'html';

        $items = [];
        foreach (range($from, $to) as $para) {
            $paragraph = $paliService->readParagraph(
                (int) $data['book'],
                (int) $para,
                $data['channel'],
                $this->paraLevel((int) $data['book'], (int) $para),
                $format
            );
            if (empty($paragraph['display'])) {
                continue;
            }
            $items[] = $paragraph;
        }

        return $this->ok([
            'items' => $items,
            'pagination' => [
                'page' => 1,
                'pageSize' => $to - $from + 1,
                'total' => count($items),
            ],
        ]);
    }

    /**
     * 单个段落内容。id 格式 {book}-{para}
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
        $book = (int) $arrId[0];
        $para = (int) $arrId[1];

        $paragraph = $paliService->readParagraph(
            $book,
            $para,
            $channel,
            $this->paraLevel($book, $para),
            $request->input('format', 'html')
        );
        if (empty($paragraph['display'])) {
            return $this->error('no data');
        }

        return $this->ok($paragraph);
    }

    /**
     * 段落是章节标题时返回标题级别，否则 0
     */
    protected function paraLevel(int $book, int $para): int
    {
        $level = PaliText::where('book', $book)
            ->where('paragraph', $para)
            ->where('level', '<', 8)
            ->value('level');

        return $level ? (int) $level : 0;
    }
}
