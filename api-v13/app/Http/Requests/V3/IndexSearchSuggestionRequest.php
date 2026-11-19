<?php

namespace App\Http\Requests\V3;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

/**
 * `GET /v3/search-suggestions`：搜索框的前缀补全。
 */
class IndexSearchSuggestionRequest extends FormRequest
{
    /** 可补全的字段（OpenSearchService::suggest 的字段标识符） */
    public const FIELDS = ['title_pali', 'title_zh', 'content_pali', 'content_zh'];

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
            'q' => ['required', 'string', 'max:100'],
            'fields' => ['sometimes', 'string', 'max:100', function (string $attribute, mixed $value, Closure $fail): void {
                $unknown = array_diff(self::splitFields((string) $value), self::FIELDS);
                if ($unknown !== [] || self::splitFields((string) $value) === []) {
                    $fail(__('validation.in', ['attribute' => $attribute]));
                }
            }],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }

    /**
     * 逗号分隔的字段列表，去空白、去空项、去重。
     *
     * @return list<string>
     */
    public static function splitFields(string $value): array
    {
        return array_values(array_unique(array_filter(
            array_map('trim', explode(',', $value)),
            fn (string $field) => $field !== '',
        )));
    }
}
