<?php

namespace App\Http\Controllers;

use App\Http\Resources\V3Resource;
use App\Models\PaliText;
use App\Services\PaliContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TipitakaReadChapterController extends Controller
{
    /**
     * 按章节读取译文
     *
     * 给定 book + 段落号，定位它所属的章节，返回该章节的段落内容并分页。
     * 章节不存在或没有内容返回 404。
     *
     * 分页是手工做的：按字节时从头累加 `pali_texts.lenght` 逐页推进，所以 meta
     * 里用 `page_size`（领域语法，非整数）而不是框架的 `per_page`，并附带
     * `first_para` / `last_para` / `has_more`。
     *
     * @unauthenticated
     *
     * @queryParam book integer required 典籍 id
     * @queryParam para integer required 章节内任一段落号，用于定位章节
     * @queryParam channel string required 译文 channel 的 uuid
     * @queryParam format string 内容格式。Enum: html,markdown,react,text Default: html
     * @queryParam view string 输出口径：display 整段合并，sentences 逐句，all 两者都给。
     *             Enum: display,sentences,all Default: display
     * @queryParam pagesize string 每页大小，两种写法：`20000b` 按字节累加段落长度
     *             （每页至少一段），`10p` 按段落数。Default: 20000b
     * @queryParam page integer 页码，从 1 开始。超出范围返回 422。Default: 1
     */
    public function index(Request $request, PaliContentService $paliService)
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
     * 按 id 读取章节
     *
     * 与 index 等价，只是把 book 与 para 合成一个路径参数。格式不对或 channel
     * 不是 uuid 返回 422。
     *
     * @unauthenticated
     *
     * @urlParam tipitaka_read_chapter string required 章节 id，格式 {book}-{para}。Example: 9002-1
     *
     * @queryParam channel string required 译文 channel 的 uuid
     * @queryParam format string 内容格式。Enum: html,markdown,react,text Default: html
     * @queryParam view string 输出口径：display 整段合并，sentences 逐句，all 两者都给。
     *             Enum: display,sentences,all Default: display
     * @queryParam pagesize string 每页大小，两种写法：`20000b` 按字节累加段落长度
     *             （每页至少一段），`10p` 按段落数。Default: 20000b
     * @queryParam page integer 页码，从 1 开始。超出范围返回 422。Default: 1
     */
    public function show(Request $request, string $id, PaliContentService $paliService)
    {
        $arrId = explode('-', $id);
        if (count($arrId) !== 2 || ! is_numeric($arrId[0]) || ! is_numeric($arrId[1])) {
            throw ValidationException::withMessages(['id' => __('site.invalid_parameter')]);
        }
        $channel = $request->input('channel');
        if (! Str::isUuid($channel)) {
            throw ValidationException::withMessages(['channel' => __('site.invalid_parameter')]);
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
    ) {
        $format = $param['format'] ?? 'html';
        $view = $param['view'] ?? 'display';
        $pageSize = $param['pagesize'] ?? '10p';
        $page = max(1, (int) ($param['page'] ?? 1));

        $chapter = PaliText::where('book', $book)->where('paragraph', $para)->first();
        if (! $chapter) {
            abort(404, __('site.not_found'));
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
            abort(404, __('site.not_found'));
        }

        $slice = $this->slice($paragraphs, $pageSize, $page);
        if ($slice === null) {
            throw ValidationException::withMessages(['page' => __('site.invalid_parameter')]);
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

        return V3Resource::collection($items)->additional(['meta' => [
            'current_page' => $page,
            'total' => $total,
            // pagesize 是领域特有语法（"2p" = 2 个段落，纯数字 = 字节数），
            // 不是 Laravel 的整数 per_page，所以单列一个键
            'page_size' => $pageSize,
            'book' => $book,
            'first_para' => (int) $first,
            'last_para' => (int) $last,
            'has_more' => $last < $paragraphs[$total - 1]->paragraph,
        ]]);
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
