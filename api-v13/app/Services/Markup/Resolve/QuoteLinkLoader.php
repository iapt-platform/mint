<?php

namespace App\Services\Markup\Resolve;

use App\Http\Api\BookTitle;
use App\Models\PageNumber;
use App\Models\PaliText;
use App\Services\Markup\Ir\Call;

/**
 * ql（quote-link，引用某版本的页码）的取数。口径同 v2 TemplateRender::render_quote_link()：
 *
 * - 给了书名 + 册 + 页：直接用
 * - 给了 book + para：`type=c` 按章节路径取书名与章名，其余按 page_numbers 表取该版本的册页
 * - 只给了缅文 title：按缅文书名缩写表拆出书名与页码
 *
 * 书名再按 `:quote:` tag 查一次术语，得到本地化书名（登记给 {@see TermLoader}，下一轮一起查）。
 * {@see BookTitle} 是 v2 的静态数据表，只读引用。
 */
final class QuoteLinkLoader extends Loader
{
    /** @var array<string, Call> */
    private array $pending = [];

    /** @var array<string, array{props: array<string, mixed>, text: string, termKey: ?string}> */
    private array $results = [];

    public function request(Call $call): string
    {
        $key = $call->raw."\x1F".implode(',', $call->scope->channels);
        if (! isset($this->results[$key])) {
            $this->pending[$key] = $call;
        }

        return $key;
    }

    /**
     * @return array{props: array<string, mixed>, text: string, termKey: ?string}|null
     */
    public function result(string $key): ?array
    {
        return $this->results[$key] ?? null;
    }

    public function hasPending(): bool
    {
        return $this->pending !== [];
    }

    public function load(): void
    {
        $batch = $this->pending;
        $this->pending = [];
        foreach ($batch as $key => $call) {
            $this->results[$key] = $this->resolve($call);
        }
    }

    /**
     * @return array{props: array<string, mixed>, text: string, termKey: ?string}
     */
    private function resolve(Call $call): array
    {
        $type = (string) $call->param('type');
        $title = (string) $call->param('title');
        $bookName = (string) $call->param('bookname');
        $volume = (string) $call->param('volume');
        $page = (string) $call->param('page');
        $book = (string) $call->param('book');
        $para = (string) $call->param('para');

        $props = ['type' => $type, 'style' => (string) $call->param('style'), 'found' => true];

        if ($bookName !== '' && $volume !== '' && $page !== '') {
            $props += ['bookName' => $bookName, 'volume' => (int) $volume, 'page' => $page];
        } elseif ($book !== '' && $para !== '') {
            $props = array_merge($props, $type === 'c'
                ? $this->chapterInfo((int) $book, (int) $para)
                : $this->pageInfo($type, (int) $book, (int) $para));
        } elseif ($title !== '') {
            $props = array_merge($props, $this->myanmarTitle($title));
        } else {
            $props['found'] = false;
        }

        if ($book !== '' && $para !== '') {
            $props['book'] = $book;
            $props['para'] = $para;
        }
        if ($title !== '') {
            $props['title'] = $title;
        }

        $text = '';
        $termKey = null;
        if (isset($props['bookName'])) {
            $field = match ($type) {
                'm' => 'm_title',
                'p' => 'p_title',
                default => null,
            };
            // v2 口径：只有 m / p 两个版本能在书名表里核对，其余版本一律记作未找到
            if ($field === null || ! in_array($props['bookName'], array_column(BookTitle::get(), $field), true)) {
                $props['found'] = false;
            }
            $termKey = $this->resolver->loader(TermLoader::class)
                ->request((string) $props['bookName'], ':quote:', $call->scope->firstChannel(), $call->scope);
        }
        // v2 这里误用了 volume / page 参数本身：按 book+para 或缅文标题查到的册页从不显示（输出「 .」）。
        // 这里改用查到的值；直接给参数时与 v2 相同
        if (isset($props['volume'], $props['page'])) {
            $text = " {$props['volume']}.{$props['page']}";
        }

        return ['props' => $props, 'text' => $text, 'termKey' => $termKey];
    }

    /**
     * @return array<string, mixed>
     */
    private function chapterInfo(int $book, int $para): array
    {
        $path = json_decode((string) PaliText::where('book', $book)->where('paragraph', $para)->value('path'), true);
        if (! is_array($path) || count($path) <= 2) {
            return ['found' => false];
        }

        return [
            'bookName' => strtolower((string) ($path[0]['title'] ?? '')),
            'chapter' => strtolower((string) (end($path)['title'] ?? '')),
            'found' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function pageInfo(string $type, int $book, int $para): array
    {
        $pageInfo = PageNumber::where('type', strtoupper($type))
            ->where('book', $book)
            ->where('paragraph', '<=', $para)
            ->orderByDesc('paragraph')
            ->first(['pcd_book_id', 'volume', 'page']);
        if ($pageInfo === null) {
            return ['found' => false];
        }
        $field = match (strtoupper($type)) {
            'M' => 'm_title',
            'P' => 'p_title',
            'V' => 'v_title',
            default => 'term',
        };
        $info = ['volume' => $pageInfo->volume, 'page' => $pageInfo->page, 'found' => true];
        foreach (BookTitle::get() as $row) {
            if ($row['id'] === $pageInfo->pcd_book_id) {
                $info['bookName'] = $row[$field];
                break;
            }
        }

        return $info;
    }

    /**
     * 缅文标题（书名缩写 + 页码，如「ဝိ၊၁၊၂၃」）→ 书名 / 册 / 页。
     *
     * @return array<string, mixed>
     */
    private function myanmarTitle(string $title): array
    {
        $titles = array_column(BookTitle::my(), 'title2');
        for ($i = mb_strlen($title, 'UTF-8'); $i > 0; $i--) {
            $prefix = mb_substr($title, 0, $i);
            $found = array_search($prefix, $titles, true);
            if ($found === false) {
                continue;
            }
            $page = (int) str_replace(
                ['၁', '၂', '၃', '၄', '၅', '၆', '၇', '၈', '၉', '၀'],
                ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'],
                (string) preg_replace('/^[၊။\s]+|[၊။\s]+$/u', '', mb_substr($title, $i)),
            );
            $row = BookTitle::my()[$found];

            return [
                'bookName' => $row['bookname'],
                'volume' => $row['volume'],
                'page' => $page,
                'found' => $row['bookname'] !== '',
            ];
        }

        return [];
    }
}
