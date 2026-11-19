<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `GET /v3/tipitaka-word-paragraphs/aggregate`：按书统计命中。
 *
 * 也是列表端点 `IndexTipitakaWordParagraphRequest` 的基类——两者的检索条件相同，
 * 列表另有书的过滤与分页。
 */
class AggregateTipitakaWordParagraphRequest extends FormRequest
{
    /** 一次最多查多少个词形（一个常见词根展开后通常几十个） */
    public const MAX_WORDS = 200;

    public const BOLD = ['only', 'exclude'];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'words' => ['required', 'string', 'max:6000', function (string $attribute, mixed $value, \Closure $fail): void {
                $count = count(self::splitWords((string) $value));
                if ($count === 0) {
                    $fail(__('validation.required', ['attribute' => $attribute]));
                } elseif ($count > self::MAX_WORDS) {
                    $fail(__('validation.max.array', ['attribute' => $attribute, 'max' => self::MAX_WORDS]));
                }
            }],
            'bold' => ['sometimes', 'string', 'in:'.implode(',', self::BOLD)],
        ];
    }

    /**
     * 逗号分隔的词形 → 去空白、去空项、去重。
     *
     * 空串绝不能进查询：`real` 为空的行有四百多万，覆盖几十万个段落。
     *
     * @return list<string>
     */
    public static function splitWords(string $value): array
    {
        $words = array_map('trim', explode(',', $value));

        return array_values(array_unique(array_filter($words, fn (string $w): bool => $w !== '')));
    }

    /**
     * Service 用的检索条件。
     *
     * @return array{words: list<string>, bold: string|null}
     */
    public function criteria(): array
    {
        return [
            'words' => self::splitWords((string) $this->validated('words')),
            'bold' => $this->validated('bold'),
        ];
    }
}
