<?php

namespace App\Services\RelatedParagraph;

use App\Models\BookTitle;
use App\Models\PaliText;
use App\Models\Tag;
use App\Models\TagMap;
use App\Models\WbwTemplate;
use RuntimeException;

/**
 * 重建 related_paragraphs 的输入：数据库里的段落、锚点、书目，与
 * resources/data/related-paragraphs/ 下的 sn 书名表和每个 book 的补丁表。
 */
class RelatedParagraphSource
{
    /** 层级 tag 名 → 层级。一个 sn 若同时带几个，按此顺序取第一个。 */
    private const LAYER_TAGS = [
        'mūla' => 'mula',
        'aṭṭhakathā' => 'atthakatha',
        'ṭīkā' => 'tika',
        'mūlaṭīkā' => 'tika',
        'anuṭīkā' => 'tika',
        'abhinavaṭīkā' => 'tika',
        'purāṇaṭīkā' => 'tika',
        'añña' => 'anna',
        'pāḷi' => 'mula',
    ];

    private ?array $snNames = null;

    public function __construct(private ?string $dataPath = null)
    {
        $this->dataPath ??= resource_path('data/related-paragraphs');
    }

    /**
     * @return list<int>
     */
    public function books(): array
    {
        return PaliText::query()->distinct()->orderBy('book')->pluck('book')->map(fn ($book) => (int) $book)->all();
    }

    /**
     * @return list<int>
     */
    public function paragraphs(int $book): array
    {
        return PaliText::query()->where('book', $book)->orderBy('paragraph')
            ->pluck('paragraph')->map(fn ($para) => (int) $para)->all();
    }

    /**
     * 该书的 .ctl. 锚点，页码标记已排除。
     *
     * @return array<int, list<string>>
     */
    public function markers(int $book): array
    {
        $markers = [];
        $rows = WbwTemplate::query()
            ->where('book', $book)
            ->where('type', '.ctl.')
            ->orderBy('paragraph')->orderBy('wid')
            ->get(['paragraph', 'word']);
        foreach ($rows as $row) {
            if (AnchorParser::parse($row->word)['kind'] !== AnchorParser::KIND_PAGE) {
                $markers[(int) $row->paragraph][] = $row->word;
            }
        }

        return $markers;
    }

    /**
     * @return array<int, int> 起始段落号 => sn
     */
    public function titles(int $book): array
    {
        return BookTitle::query()->where('book', $book)->orderBy('paragraph')
            ->pluck('sn', 'paragraph')->map(fn ($sn) => (int) $sn)->all();
    }

    /**
     * sn 书名表 book-names.csv（sn,book_name）。
     *
     * @return array<int, string>
     */
    public function snNames(): array
    {
        if ($this->snNames !== null) {
            return $this->snNames;
        }

        $this->snNames = [];
        foreach ($this->readCsv($this->dataPath.'/book-names.csv', ['sn', 'book_name']) as $line => $row) {
            if (! ctype_digit($row['sn']) || $row['book_name'] === '') {
                throw new RuntimeException("book-names.csv 第 {$line} 行格式不对");
            }
            $this->snNames[(int) $row['sn']] = $row['book_name'];
        }

        return $this->snNames;
    }

    /**
     * 补丁表 {book}.csv（para,book_name,begin,end）。没有文件就是没有补丁。
     *
     * @return array<int, array{book_name: string|null, begin: int, end: int}>
     */
    public function patch(int $book): array
    {
        $patch = [];
        $file = "{$this->dataPath}/{$book}.csv";
        foreach ($this->readCsv($file, ['para', 'book_name', 'begin', 'end']) as $line => $row) {
            foreach (['para', 'begin', 'end'] as $column) {
                if (! ctype_digit($row[$column])) {
                    throw new RuntimeException("{$book}.csv 第 {$line} 行 {$column} 不是数字");
                }
            }
            if ((int) $row['end'] < (int) $row['begin']) {
                throw new RuntimeException("{$book}.csv 第 {$line} 行 end 小于 begin");
            }
            if (isset($patch[(int) $row['para']])) {
                throw new RuntimeException("{$book}.csv 第 {$line} 行段落 {$row['para']} 重复");
            }
            $patch[(int) $row['para']] = [
                'book_name' => $row['book_name'] === '' ? null : $row['book_name'],
                'begin' => (int) $row['begin'],
                'end' => (int) $row['end'],
            ];
        }

        return $patch;
    }

    /**
     * 每个 sn 的层级（mula / atthakatha / tika / anna / unknown），取自其 level-1 段落的 tag。
     *
     * @return array<int, string>
     */
    public function layers(): array
    {
        $titles = BookTitle::query()->get(['book', 'paragraph', 'sn']);
        $uids = PaliText::query()->where('level', 1)->get(['book', 'paragraph', 'uid'])
            ->keyBy(fn ($text) => $text->book.'-'.$text->paragraph);

        $tagNames = Tag::query()->whereIn('name', array_keys(self::LAYER_TAGS))->pluck('name', 'id');
        $tagsByUid = [];
        foreach (TagMap::query()->whereIn('tag_id', $tagNames->keys())->get(['anchor_id', 'tag_id']) as $map) {
            $tagsByUid[$map->anchor_id][] = $tagNames[$map->tag_id];
        }

        $layers = [];
        foreach ($titles as $title) {
            $uid = $uids->get($title->book.'-'.$title->paragraph)?->uid;
            $names = $tagsByUid[$uid] ?? [];
            $layers[(int) $title->sn] = 'unknown';
            foreach (self::LAYER_TAGS as $tag => $layer) {
                if (in_array($tag, $names, true)) {
                    $layers[(int) $title->sn] = $layer;
                    break;
                }
            }
        }

        return $layers;
    }

    /**
     * @param  list<string>  $columns
     * @return array<int, array<string, string>> 行号 => 行
     */
    private function readCsv(string $file, array $columns): array
    {
        if (! is_file($file)) {
            return [];
        }

        $rows = [];
        $handle = fopen($file, 'r');
        $header = fgetcsv($handle, escape: '');
        if ($header !== $columns) {
            fclose($handle);
            throw new RuntimeException(basename($file).' 表头应为 '.implode(',', $columns));
        }
        $line = 1;
        while (($data = fgetcsv($handle, escape: '')) !== false) {
            $line++;
            if ($data === [null]) {
                continue;
            }
            if (count($data) !== count($columns)) {
                fclose($handle);
                throw new RuntimeException(basename($file)." 第 {$line} 行列数不对");
            }
            $rows[$line] = array_combine($columns, array_map('trim', $data));
        }
        fclose($handle);

        return $rows;
    }
}
