<?php

namespace App\Http\Requests\V3;

use App\Services\V3\TipitakaPageNumberService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * `GET /v3/tipitaka-page-numbers/{page}`：版本与卷号都是可选过滤。
 */
class IndexTipitakaPageNumberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $editions = [...array_values(TipitakaPageNumberService::EDITIONS), TipitakaPageNumberService::CS];

        return [
            'edition' => ['sometimes', 'string', 'in:'.implode(',', $editions)],
            'volume' => ['sometimes', 'integer', 'min:1', 'max:99'],
        ];
    }

    /**
     * @return array{edition?: string, volume?: int}
     */
    public function filters(): array
    {
        $filters = [];
        if ($this->has('edition')) {
            $filters['edition'] = (string) $this->validated('edition');
        }
        if ($this->has('volume')) {
            $filters['volume'] = (int) $this->validated('volume');
        }

        return $filters;
    }
}
