<?php

namespace App\Services\Markup\Annotation;

use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;

/**
 * 把批注占位符解析成 {@see AnnotationTokenNode}。
 */
final class AnnotationTokenParser implements InlineParserInterface
{
    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::regex(AnnotationToken::OPEN.'(\d+)'.AnnotationToken::CLOSE);
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        [$index] = $inlineContext->getSubMatches();
        $inlineContext->getCursor()->advanceBy($inlineContext->getFullMatchLength());
        $inlineContext->getContainer()->appendChild(new AnnotationTokenNode((int) $index));

        return true;
    }
}
