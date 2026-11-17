<?php

namespace App\Services\Markup\Parsing;

use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;

/**
 * 把预扫描留下的占位符解析成 {@see PlaceholderNode}。
 */
final class PlaceholderParser implements InlineParserInterface
{
    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::regex(Placeholder::OPEN.'(\d+)'.Placeholder::CLOSE);
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();
        [$index] = $inlineContext->getSubMatches();
        $cursor->advanceBy($inlineContext->getFullMatchLength());
        $inlineContext->getContainer()->appendChild(new PlaceholderNode((int) $index));

        return true;
    }
}
