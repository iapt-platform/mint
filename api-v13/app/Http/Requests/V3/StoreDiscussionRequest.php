<?php

namespace App\Http\Requests\V3;

use App\Models\Discussion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `POST /v3/discussions` 的入参：发起话题，或新建一条批注。
 *
 * 不收 content_type：v3 只写 markdown（v6 已没有 html 编辑器）。
 * severity 的取值随 type 而定，见 {@see Discussion::SEVERITIES}；不收 severity 的类型传了就 422。
 *
 * 批注（note / commentary）与评论线程的规则不同：只能挂在句子上、标题可省、正文必填，
 * commentary 的正文必须是义注句子模板。glossary 也只能挂在句子上。
 * 锚点（pos_* / quote_*）只有 {@see Discussion::ANCHORED_TYPES} 收。
 */
class StoreDiscussionRequest extends FormRequest
{
    private const ANCHOR_FIELDS = ['pos_start', 'pos_end', 'quote_exact', 'quote_prefix', 'quote_suffix'];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $type = $this->input('type', 'discussion');
        $severities = Discussion::SEVERITIES[$type] ?? [];
        $isAnnotation = in_array($type, Discussion::INJECTED_TYPES, true);
        $sentenceOnly = in_array($type, Discussion::SENTENCE_ONLY_TYPES, true);
        // 不收锚点的类型传了 422，免得静默丢掉
        $anchor = fn (array $rules) => in_array($type, Discussion::ANCHORED_TYPES, true)
            ? ['sometimes', 'nullable', ...$rules]
            : ['prohibited'];

        return [
            'target_type' => ['required', 'string', Rule::in($sentenceOnly ? ['sentence'] : Discussion::TARGET_TYPES)],
            'target_id' => ['required', 'uuid'],
            'type' => ['sometimes', 'string', Rule::in(Discussion::COMMENT_TYPES)],
            'title' => $isAnnotation
                ? ['sometimes', 'nullable', 'string', 'max:256']
                : ['required', 'string', 'max:256'],
            'content' => match ($type) {
                Discussion::TYPE_COMMENTARY => ['required', 'string', 'max:20000', 'regex:'.Discussion::COMMENTARY_PATTERN],
                Discussion::TYPE_NOTE => ['required', 'string', 'max:20000'],
                default => ['sometimes', 'nullable', 'string', 'max:20000'],
            },
            'severity' => $severities === []
                ? ['prohibited']
                : ['sometimes', 'nullable', 'string', Rule::in($severities)],
            'pos_start' => $anchor(['integer', 'min:0']),
            'pos_end' => $anchor(['integer', 'min:0', 'gte:pos_start']),
            'quote_exact' => $anchor(['string', 'max:1024']),
            'quote_prefix' => $anchor(['string', 'max:1024']),
            'quote_suffix' => $anchor(['string', 'max:1024']),
            'tpl_id' => ['sometimes', 'nullable', 'uuid'],
            'notify' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [
            'content.regex' => __('messages.discussion_commentary_format'),
            'severity.prohibited' => __('messages.discussion_severity_not_allowed'),
        ];
        if (in_array($this->input('type'), Discussion::INJECTED_TYPES, true)) {
            $messages['target_type.in'] = __('messages.discussion_annotation_target');
        } elseif ($this->input('type') === Discussion::TYPE_GLOSSARY) {
            $messages['target_type.in'] = __('messages.discussion_glossary_target');
        }
        foreach (self::ANCHOR_FIELDS as $field) {
            $messages["{$field}.prohibited"] = __('messages.discussion_anchor_thread');
        }

        return $messages;
    }
}
