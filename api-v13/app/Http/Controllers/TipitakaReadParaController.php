<?php

namespace App\Http\Controllers;

use App\Http\Resources\V3Resource;
use App\Services\PaliContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TipitakaReadParaController extends Controller
{
    /**
     * 按段落区间读取译文
     *
     * 阅读模式下按 book + 段落区间取内容，空段落会被跳过。区间由 para 到 to，
     * 不传 to 则只取一段；to 小于 para 返回 422。
     *
     * 这个接口不走 Eloquent 分页，meta 是手工给的：一次请求即一页。
     *
     * @unauthenticated
     *
     * @queryParam book integer required 典籍 id
     * @queryParam para integer required 起始段落号
     * @queryParam to integer 结束段落号（含）。不传则等于 para
     * @queryParam channel string required 译文 channel 的 uuid
     * @queryParam format string 内容格式。Enum: html,markdown,react,text Default: html
     * @queryParam view string 输出口径：display 整段合并，sentences 逐句，all 两者都给。
     *             Enum: display,sentences,all Default: display
     */
    public function index(Request $request, PaliContentService $paliService)
    {
        $data = $request->validate([
            'book' => 'required|integer',
            'para' => 'required|integer',
            'to' => 'integer',
            'channel' => 'required|uuid',
            'format' => 'string|in:html,markdown,react,text',
            'view' => 'string|in:display,sentences,all', // display:整段合并 sentences：逐句
        ]);

        $from = $data['para'];
        $to = $data['to'] ?? $from;
        if ($to < $from) {
            throw ValidationException::withMessages(['to' => __('site.invalid_parameter')]);
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

        return V3Resource::collection($items)->additional(['meta' => [
            'current_page' => 1,
            'per_page' => $to - $from + 1,
            'total' => count($items),
            'last_page' => 1,
        ]]);
    }

    /**
     * 读取单个段落
     *
     * id 是 `{book}-{para}` 复合形式，例如 `9001-1`。格式不对或 channel 不是
     * uuid 返回 422；段落无内容返回 404。
     *
     * @unauthenticated
     *
     * @urlParam tipitaka_read_para string required 段落 id，格式 {book}-{para}。Example: 9001-1
     *
     * @queryParam channel string required 译文 channel 的 uuid
     * @queryParam format string 内容格式。Enum: html,markdown,react,text Default: html
     * @queryParam view string 输出口径。Enum: display,sentences,all Default: display
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
        $book = (int) $arrId[0];
        $para = (int) $arrId[1];

        $paragraph = $paliService->readParagraph(
            $book,
            $para,
            $channel,
            $request->input('format', 'html')
        );
        if (empty($paragraph['display'])) {
            abort(404, __('site.not_found'));
        }

        return V3Resource::make($this->filterView($paragraph, $request->input('view', 'display')));
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
