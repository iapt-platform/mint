<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * `GET /v3/search` 与 `POST /v3/search`（两者等价，POST 把同样的参数放进请求体）。
 *
 * 枚举取的是索引里真实存在的值（IndexTipitaka / TermIndexService 写入的），
 * 不是旧文档里那套对不上号的名字。
 */
class IndexSearchRequest extends FormRequest
{
    public const MODES = ['fuzzy', 'exact', 'semantic', 'hybrid', 'definition'];

    public const RESOURCE_TYPES = ['tipitaka', 'original_text', 'term'];

    public const GRANULARITIES = ['book', 'chapter', 'session', 'paragraph'];

    /** OpenSearch 的 index.max_result_window：from + size 超过它直接报错 */
    public const MAX_WINDOW = 10000;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * JSON 请求体里的 true / false 是布尔值，查询串里是字符串，统一成字符串再校验。
     */
    protected function prepareForValidation(): void
    {
        if (is_bool($this->input('facets'))) {
            $this->merge(['facets' => $this->input('facets') ? 'true' : 'false']);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'max:500'],
            'search_mode' => ['sometimes', 'string', 'in:'.implode(',', self::MODES)],
            'resource_type' => ['sometimes', 'string', 'in:'.implode(',', self::RESOURCE_TYPES)],
            'resource_id' => ['sometimes', 'string', 'max:255'],
            'granularity' => ['sometimes', 'string', 'in:'.implode(',', self::GRANULARITIES)],
            'language' => ['sometimes', 'string', 'max:16'],
            'category' => ['sometimes', 'string', 'max:500'],
            'tags' => ['sometimes', 'string', 'max:500'],
            'page_refs' => ['sometimes', 'array', 'max:50'],
            'page_refs.*' => ['string', 'max:64'],
            'related_id' => ['sometimes', 'array', 'max:50'],
            'related_id.*' => ['string', 'max:255'],
            'author' => ['sometimes', 'string', 'max:255'],
            'channel' => ['sometimes', 'uuid'],
            'facets' => ['sometimes', 'in:true,false,1,0'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * 翻页深度受 OpenSearch 限制：超出时在这里给 422，而不是让 OpenSearch 报错变成 500。
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }
                $page = (int) $this->input('page', 1);
                $perPage = (int) $this->input('per_page', 20);
                if ($page * $perPage > self::MAX_WINDOW) {
                    $validator->errors()->add('page', __('validation.max.numeric', [
                        'attribute' => 'page',
                        'max' => intdiv(self::MAX_WINDOW, $perPage),
                    ]));
                }
            },
        ];
    }
}
