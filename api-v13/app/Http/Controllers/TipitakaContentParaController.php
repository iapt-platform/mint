<?php

namespace App\Http\Controllers;

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
            'view' => 'string|in:display,sentences,all',
        ]);

        $from = $data['para'];
        $to = $data['to'] ?? $from;
        if ($to < $from) {
            return $this->error('invalid paragraph range');
        }
        $format = $data['format'] ?? 'html';
        $view = $data['view'] ?? 'display';

        $items = [];
        foreach (range($from, $to) as $para) {
            $paragraph = $paliService->readParagraph(
                (int) $data['book'],
                (int) $para,
                $data['channel'],
                $format
            );
            if (empty($paragraph['display'])) {
                continue;
            }
            $items[] = $this->filterView($paragraph, $view);
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
            $request->input('format', 'html')
        );
        if (empty($paragraph['display'])) {
            return $this->error('no data');
        }

        return $this->ok($this->filterView($paragraph, $request->input('view', 'display')));
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
