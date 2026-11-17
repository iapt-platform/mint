<?php

namespace App\Services\Markup\Templates\Internal;

use App\Services\AnnotationLayout;
use App\Services\HtmlAnnotationMarkup;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Render\RenderContext;
use App\Services\Markup\Templates\ParamSpec;
use App\Services\Markup\Templates\Template;

/**
 * 阅读页句尾的评估对勾：颜色按分数档位（high / medium / low），点击看分数与理由。
 * 没有 id 的是「同段别的句子评估过、这句没有」的灰色对勾（level none，不能点）。由批注注入生成。
 * 纯文本 / markdown 不输出。
 */
final class AnnoAssessmentTemplate extends Template
{
    protected string $component = 'anno-assessment';

    public function params(): array
    {
        return ['id' => new ParamSpec, 'score' => new ParamSpec];
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        $id = (string) $call->param('id');
        if ($id === '') {
            return ['level' => 'none'];
        }
        $score = (int) $call->param('score');

        return ['id' => $id, 'score' => $score, 'level' => AnnotationLayout::assessmentLevel($score)];
    }

    public function html(Call $call, RenderContext $ctx): string
    {
        $id = (string) $call->param('id');

        return $id === ''
            ? HtmlAnnotationMarkup::unassessedMark()
            : (new HtmlAnnotationMarkup)->assessmentMark($id, (int) $call->param('score'));
    }
}
