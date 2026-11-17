<?php

namespace App\Services\Markup\Parsing;

use App\Services\Markup\Annotation\AnnotationSet;
use App\Services\Markup\Annotation\AnnotationToken;
use App\Services\Markup\Annotation\AnnotationTokenParser;
use App\Services\Markup\Annotation\Marker;
use App\Services\Markup\Dialect;
use App\Services\Markup\Ir\Boundary;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Ir\Element;
use App\Services\Markup\Ir\Node;
use App\Services\Markup\Ir\Params;
use App\Services\Markup\Ir\Scope;
use App\Services\Markup\Ir\Text;
use App\Services\Markup\Templates\SentTemplate;
use App\Services\Markup\Templates\TemplateRegistry;
use Illuminate\Support\Facades\Log;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Parser\MarkdownParser;

/**
 * 源码 → {@see Document}（IR）。纯解析，不查库。
 *
 * 流程：清洗编码 → nissaya 转换 → {@see Preprocessor}（模板换占位符）→ CommonMark + GFM → {@see IrBuilder}。
 * 两种方言共用同一个 CommonMark 环境：旧方言的反引号脚注在预扫描时已经换掉了，剩下的反引号照 CommonMark 处理。
 */
final class MarkupParser
{
    /** 模板参数里再嵌模板的最大层数 */
    private const MAX_NESTING = 8;

    private ?Environment $environment = null;

    public function __construct(private readonly TemplateRegistry $registry) {}

    /**
     * @param  bool  $paragraphMode  巴汉逐段对读，见 {@see self::duplicateSentenceParagraphs()}
     * @param  AnnotationSet|null  $annotations  源码里批注占位符对应的结构化标记（阅读页注入，见 {@see AnnotationSet}）
     */
    public function parse(string $source, Scope $scope, bool $paragraphMode = false, int $nesting = 0, ?AnnotationSet $annotations = null): Document
    {
        $source = $this->clean($source);
        if ($annotations === null) {
            // 没有批注时源码里不该有批注占位符；有批注时，下标对不上的占位符解析时自然丢掉
            $source = AnnotationToken::strip($source);
        }
        // 只转换内容本身；模板参数（如 nissaya json 里 note 的正文）不是 nissaya 行
        if ($scope->channelType === 'nissaya' && $nesting === 0) {
            $source = $this->nissayaToTemplates($source, $scope->contentType);
        }
        if (trim($source) === '') {
            return Document::empty($scope);
        }

        $calls = [];
        $raws = [];
        $register = function (string $kind, string $raw, string $inner, string $info) use (&$calls, &$raws, $scope, $nesting): int {
            $index = count($calls);
            $raws[$index] = $raw;
            $calls[$index] = $this->callFromSyntax($kind, $raw, $inner, $info, $scope, $nesting);

            return $index;
        };
        $prepared = (new Preprocessor)->run($source, $scope->dialect, $register);

        $ast = (new MarkdownParser($this->environment()))->parse($prepared);
        $annotationCalls = [];
        $annotation = $annotations === null ? null : function (int $index) use ($annotations, $scope, $nesting, &$annotationCalls): ?Node {
            $node = $this->annotationNode($annotations->get($index), $scope, $nesting);
            if ($node instanceof Call) {
                $annotationCalls[$index] = $node;
            }

            return $node;
        };
        $builder = new IrBuilder(
            $calls,
            $raws,
            fn (string $name, array $segments, string $raw) => $this->makeCall($name, $segments, $raw, $scope, $nesting),
            $scope,
            $source,
            $annotation,
        );
        [$nodes, $inline] = $builder->build($ast);

        if ($paragraphMode) {
            $blocks = $inline ? [new Element('p', [], $nodes)] : $nodes;
            $duplicated = $this->duplicateSentenceParagraphs($blocks);
            if (count($duplicated) !== count($blocks)) {
                [$nodes, $inline] = [$duplicated, false];
            }
        }

        return new Document($nodes, $inline, $prepared, $calls, $scope, annotationCalls: $annotationCalls);
    }

    /**
     * 批注标记 → IR：点 → 模板调用；区间开 → 带着（待填内容的）调用的边界；区间闭 → 边界。
     */
    private function annotationNode(?Marker $marker, Scope $scope, int $nesting): ?Node
    {
        if ($marker === null) {
            return null;
        }
        if ($marker->kind === Marker::CLOSE) {
            return new Boundary($marker->pair, false);
        }
        if ($marker->template === 'note') {
            $template = $this->registry->get('note');
            // 批注正文的方言跟批注那一行（discussions），不跟句子
            $bodyScope = new Scope($scope->channels, Dialect::fromContentType($marker->bodyContentType), $scope->depth, 'translation', $marker->bodyContentType);
            $body = (string) $marker->body;

            return new Call('note', $template, new Params($marker->params, [1 => $body]), [
                'text' => $this->parse($body, $bodyScope, false, $nesting + 1),
            ], $scope, '');
        }
        $call = new Call($marker->template, $this->registry->internal($marker->template), new Params($marker->params), [], $scope, '');

        return $marker->kind === Marker::OPEN ? new Boundary($marker->pair, true, $call) : $call;
    }

    /**
     * @param  list<string>|Params  $params  参数片段，或已经分好的参数表（```note 的正文不再切 `|`）
     */
    public function makeCall(string $name, array|Params $params, string $raw, Scope $scope, int $nesting): Call
    {
        $template = $this->registry->get($name);
        if (is_array($params)) {
            $params = $template->parseParams($params, $scope->dialect);
        }
        $slots = [];
        foreach ($template->params() as $paramName => $spec) {
            if (! $spec->markdown) {
                continue;
            }
            $value = $params->raw($paramName, $spec->position);
            if ($value === null) {
                continue;
            }
            $slots[$paramName] = $nesting < self::MAX_NESTING
                ? $this->parse($value, $scope, false, $nesting + 1)
                : new Document([new Text($value)], true, $value, [], $scope);
        }

        return new Call($name, $template, $params, $slots, $scope, $raw);
    }

    private function callFromSyntax(string $kind, string $raw, string $inner, string $info, Scope $scope, int $nesting): Call
    {
        return match ($kind) {
            'wikilink' => $this->makeCall('term', TemplateSyntax::split('term|'.$inner)[1], $raw, $scope, $nesting),
            'note-backtick' => $this->makeCall('note', TemplateSyntax::split('note|'.$inner)[1], $raw, $scope, $nesting),
            'note-fence' => $this->makeCall('note', $this->fenceParams($inner, $info), $raw, $scope, $nesting),
            default => $this->templateCall($inner, $raw, $scope, $nesting),
        };
    }

    private function templateCall(string $inner, string $raw, Scope $scope, int $nesting): Call
    {
        // 句子简写 {{book-para-start-end}}（可带 @channel）
        if (preg_match('/^\s*[0-9]/', $inner) === 1) {
            return $this->makeCall('sent', new Params(['id' => trim($inner)]), $raw, $scope, $nesting);
        }
        [$name, $segments] = TemplateSyntax::split($inner);

        return $this->makeCall($name, $segments, $raw, $scope, $nesting);
    }

    /**
     * ```note 的参数：正文整段是第 1 个参数，info string 里的 `name="value"` 是命名参数。
     */
    private function fenceParams(string $inner, string $info): Params
    {
        $named = [];
        preg_match_all('/([a-z]+)=(?:"([^"]*)"|(\S+))/', $info, $pairs, PREG_SET_ORDER);
        foreach ($pairs as $pair) {
            $named[$pair[1]] = ($pair[2] ?? '') !== '' ? $pair[2] : ($pair[3] ?? '');
        }

        return new Params($named, [1 => $inner]);
    }

    /**
     * 只由句子模板组成的段落复制成两段：一段只显示原文、一段只显示译文（v2 的 preprocessingForParagraph）。
     *
     * @param  list<Node>  $nodes
     * @return list<Node>
     */
    private function duplicateSentenceParagraphs(array $nodes): array
    {
        $out = [];
        foreach ($nodes as $node) {
            if (! $node instanceof Element || $node->tag !== 'p' || ! $this->onlySentences($node->children)) {
                $out[] = $node;

                continue;
            }
            foreach (['origin', 'translation'] as $show) {
                $out[] = new Element('p', [], array_map(
                    fn (Node $child) => $child instanceof Call ? $child->withParam('text', $show) : $child,
                    $node->children,
                ));
            }
        }

        return $out;
    }

    /**
     * @param  list<Node>  $nodes
     */
    private function onlySentences(array $nodes): bool
    {
        $found = false;
        foreach ($nodes as $node) {
            if ($node instanceof Call && $node->template instanceof SentTemplate) {
                $found = true;
            } elseif (! ($node instanceof Text && trim($node->value, " \t\n>") === '')) {
                return false;
            }
        }

        return $found;
    }

    private function clean(string $source): string
    {
        $source = (string) iconv('UTF-8', 'UTF-8//IGNORE', $source);
        $source = str_replace(["\r\n", "\r"], "\n", $source);

        return Placeholder::strip($source);
    }

    /**
     * nissaya channel 的内容转成 nissaya 模板（v2 markdown2wiki 的 nissaya 段，口径不变）。
     *
     * - json：对齐后的 `{original, translation, note}`（translation 用 `>` 分层）或逐词解析的 wbw json
     * - markdown：每行 `巴利=意思`（`$` 也当 `=`）
     */
    private function nissayaToTemplates(string $source, string $contentType): string
    {
        if ($contentType === 'json') {
            $words = json_decode($source);
            if (! is_array($words)) {
                Log::error('nissaya json data is not array', ['data' => $source]);

                return '';
            }
            $out = [];
            foreach ($words as $word) {
                if (isset($word->original)) {
                    $out[] = '{{nissaya|'.$word->original.'|'.str_replace('>', '=', $word->translation ?? '').'}}';
                    if (! empty($word->note)) {
                        $out[] = '{{note|'.$word->note.'|[nt]}}';
                    }
                } elseif (isset($word->sn) && count($word->sn) === 1) {
                    $out[] = '{{nissaya|'.($word->word->value ?? '').'|'.($word->meaning->value ?? '').'}}';
                }
            }

            return implode('', $out);
        }

        $lines = [];
        foreach (explode("\n", $source) as $line) {
            if (! str_contains($line, '=') && ! str_contains($line, '$')) {
                $lines[] = $line;

                continue;
            }
            $parts = explode('=', str_replace('$', '=', $line));
            $lines[] = '{{nissaya|'.$parts[0].'|'.implode('=', array_slice($parts, 1)).'}}';
        }

        return implode("\n", $lines);
    }

    private function environment(): Environment
    {
        if ($this->environment === null) {
            $environment = new Environment(['html_input' => 'allow']);
            $environment->addExtension(new CommonMarkCoreExtension);
            $environment->addExtension(new GithubFlavoredMarkdownExtension);
            $environment->addInlineParser(new PlaceholderParser, 250);
            $environment->addInlineParser(new AnnotationTokenParser, 250);
            // 抢在 CommonMark 自带的分隔符解析（最低优先级）之前处理 `*`：中文标点旁的粗体
            $environment->addInlineParser(new CjkFriendlyEmphasisParser, 0);
            $this->environment = $environment;
        }

        return $this->environment;
    }
}
