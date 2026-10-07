<?php

namespace App\Http\Resources;

use App\Models\PageNumber;
use App\Models\PaliText;
use App\Services\PaliSeriesesService;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchPaliWbwResource extends JsonResource
{
    /**
     * page_numbers.type 单字母代号到缩写/名称的映射。
     *
     * @var array<string, string>
     */
    private const TYPE_ABBR = [
        'M' => 'My',
        'P' => 'PTS',
        'V' => 'VRI',
        'T' => 'Thai',
        'O' => 'Other',
    ];

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [
            'book' => $this->book,
            'paragraph' => $this->paragraph,
        ];
        if (isset($this->rank)) {
            $data['rank'] = $this->rank;
        }
        $paliText = PaliText::where('book', $this->book)
            ->where('paragraph', $this->paragraph)
            ->first();
        if ($paliText) {
            $data['path'] = json_decode($paliText->path, true);
            if ($paliText->level < 100) {
                $data['paliTitle'] = $paliText->toc;
                $book = $this->book;
                $para = $this->paragraph;
                $data['link'] = config('app.url')."/library/tipitaka/{$book}-{$para}/read";
            } else {
                $data['paliTitle'] = PaliText::where('book', $this->book)
                    ->where('paragraph', $paliText->parent)
                    ->value('toc');
                $book = end($data['path'])['book'];
                $para = end($data['path'])['paragraph'];
                $data['link'] = config('app.url')."/library/tipitaka/{$book}-{$para}/read#{$this->paragraph}";
            }
            $keyWords = explode(',', $request->input('key'));
            $keyWordsUpper = $keyWords;
            foreach ($keyWords as $key => $word) {
                if (mb_substr($word, -3, null, 'UTF-8') === 'nti') {
                    $keyWordsUpper[] = mb_substr($word, 0, mb_strlen($word, 'UTF-8') - 3, 'UTF-8');
                } elseif (mb_substr($word, -3, null, 'UTF-8') === 'ti') {
                    $keyWordsUpper[] = mb_substr($word, 0, mb_strlen($word, 'UTF-8') - 2, 'UTF-8');
                }
            }
            foreach ($keyWords as $key => $word) {
                $keyWordsUpper[] = mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8').mb_substr($word, 1, null, 'UTF-8');
            }
            $keyReplace = [];
            foreach ($keyWordsUpper as $key => $word) {
                $keyReplace[] = "<span class='hl'>{$word}</span>";
            }
            $data['highlight'] = str_replace($keyWordsUpper, $keyReplace, $paliText->html);
        }

        $series = app(PaliSeriesesService::class)->find((int) $this->book, (int) $this->paragraph);

        $ref = PageNumber::where('book', $this->book)
            ->where('paragraph', $this->paragraph)
            ->orderBy('wid')
            ->get()
            ->unique('type')
            ->map(fn ($pageNumber) => [
                'type' => self::TYPE_ABBR[$pageNumber->type] ?? $pageNumber->type,
                'page' => $pageNumber->page,
                'title' => match ($pageNumber->type) {
                    'M' => $series['abbr_my'] ?? null,
                    'P' => $series['abbr_pts'] ?? null,
                    default => null,
                },
            ])
            ->values()
            ->all();

        $ref[] = [
            'type' => 'WP',
            'page' => $this->paragraph,
            'title' => $series['abbr_wp'] ?? null,
        ];

        $data['ref'] = $ref;

        return $data;
    }
}
