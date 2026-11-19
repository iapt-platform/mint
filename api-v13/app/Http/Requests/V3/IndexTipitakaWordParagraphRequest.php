<?php

namespace App\Http\Requests\V3;

/**
 * `GET /v3/tipitaka-word-paragraphs`：按词形找段落。检索条件同聚合端点，另加书的过滤与分页。
 */
class IndexTipitakaWordParagraphRequest extends AggregateTipitakaWordParagraphRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return parent::rules() + [
            'book_id' => ['sometimes', 'string', 'regex:/^\d+(,\d+)*$/', 'max:2000'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array{words: list<string>, bold: string|null, book_ids: list<int>}
     */
    public function criteria(): array
    {
        $bookIds = $this->validated('book_id');

        return parent::criteria() + [
            'book_ids' => $bookIds === null ? [] : array_values(array_unique(array_map('intval', explode(',', $bookIds)))),
        ];
    }
}
