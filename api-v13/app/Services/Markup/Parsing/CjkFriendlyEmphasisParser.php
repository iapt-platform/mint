<?php

namespace App\Services\Markup\Parsing;

use League\CommonMark\Delimiter\Delimiter;
use League\CommonMark\Environment\EnvironmentAwareInterface;
use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;
use League\CommonMark\Util\RegexHelper;

/**
 * `*` / `**` 强调的分隔符解析，按 markdown-cjk-friendly 提案放宽了「贴合」（flanking）规则。
 *
 * CommonMark 的规则为空格分词的语言设计：开头的 `**` 后面紧跟标点时，前面必须是空白或标点；
 * 结尾的 `**` 前面紧挨标点时，后面必须是空白或标点。中文没有空格，粗体又常常把引号、括号包在里面，
 * `从**“tuccha”**的角度` 两头都不成立，`**` 原样露出来。
 *
 * 放宽：在「紧挨着标点」的那一侧，中日韩字符（汉字、假名、谚文、注音）与空白同等对待。
 * 两侧都不是 CJK 字符时，判断与 CommonMark 完全相同。
 * 参考：https://github.com/tats-u/markdown-cjk-friendly
 *
 * 只接管 `*`：`_` 有词内不能强调的规则，中文正文也很少用；`~~` 删除线暂不处理。
 * 逻辑照 league/commonmark 的 DelimiterParser（final、@internal，不能继承），只换了判断开闭的那一步；
 * 配对与生成强调节点仍由原来的 EmphasisDelimiterProcessor 做。
 */
final class CjkFriendlyEmphasisParser implements EnvironmentAwareInterface, InlineParserInterface
{
    private const CJK = '/^[\p{Han}\p{Hiragana}\p{Katakana}\p{Hangul}\p{Bopomofo}]/u';

    private EnvironmentInterface $environment;

    public function setEnvironment(EnvironmentInterface $environment): void
    {
        $this->environment = $environment;
    }

    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::string('*');
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $processor = $this->environment->getDelimiterProcessors()->getDelimiterProcessor('*');
        if ($processor === null) {
            return false;
        }
        $cursor = $inlineContext->getCursor();
        $charBefore = $cursor->peek(-1) ?? "\n";

        $count = 0;
        while ($cursor->peek($count) === '*') {
            $count++;
        }
        if ($count < $processor->getMinLength()) {
            return false;
        }
        $cursor->advanceBy($count);
        $charAfter = $cursor->getCurrentCharacter() ?? "\n";

        [$canOpen, $canClose] = self::flanking($charBefore, $charAfter);
        $canOpen = $canOpen && $processor->getOpeningCharacter() === '*';
        $canClose = $canClose && $processor->getClosingCharacter() === '*';

        if (! $canOpen && ! $canClose) {
            $inlineContext->getContainer()->appendChild(new Text(str_repeat('*', $count)));

            return true;
        }

        $node = new Text(str_repeat('*', $count), ['delim' => true]);
        $inlineContext->getContainer()->appendChild($node);
        $inlineContext->getDelimiterStack()->push(
            new Delimiter('*', $count, $node, $canOpen, $canClose, $cursor->getPosition())
        );

        return true;
    }

    /**
     * 左贴合（能开）与右贴合（能闭）。与 CommonMark 的区别只在各自第二个条件里多了「或者另一侧是 CJK 字符」。
     *
     * @return array{0: bool, 1: bool}
     */
    public static function flanking(string $before, string $after): array
    {
        $afterSpace = preg_match(RegexHelper::REGEX_UNICODE_WHITESPACE_CHAR, $after) === 1;
        $afterPunct = preg_match(RegexHelper::REGEX_PUNCTUATION, $after) === 1;
        $beforeSpace = preg_match(RegexHelper::REGEX_UNICODE_WHITESPACE_CHAR, $before) === 1;
        $beforePunct = preg_match(RegexHelper::REGEX_PUNCTUATION, $before) === 1;
        $beforeCjk = preg_match(self::CJK, $before) === 1;
        $afterCjk = preg_match(self::CJK, $after) === 1;

        $left = ! $afterSpace && (! $afterPunct || $beforeSpace || $beforePunct || $beforeCjk);
        $right = ! $beforeSpace && (! $beforePunct || $afterSpace || $afterPunct || $afterCjk);

        return [$left, $right];
    }
}
