<?php

namespace App\Services\Markup\Render;

use App\Services\Markup\Annotation\AnnotationToken;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Ir\Element;
use App\Services\Markup\Ir\Node;
use App\Services\Markup\Ir\Text;
use App\Services\Markup\Parsing\Placeholder;

/**
 * 展开模板后的 markdown（导出用）。
 *
 * markdown 本身原样保留：拿预扫描后的源码，把每个占位符换成模板的 markdown() 输出；
 * 脚注变成 `[^n]`，脚注正文在文末（footnote 选项打开时）。站内附件地址补上站点地址。
 */
final class MarkdownRenderer implements Renderer
{
    public function document(Document $document, RenderContext $ctx): string
    {
        if ($document->source === '') {
            return $this->fromNodes($document->nodes, $ctx);
        }
        $markdown = (string) preg_replace_callback(
            Placeholder::PATTERN,
            function (array $m) use ($document, $ctx) {
                $call = $document->calls[(int) $m[1]] ?? null;

                return $call === null ? '' : $call->template->markdown($call, $ctx);
            },
            $document->source,
        );

        // 批注占位符：点标记换成模板的 markdown（脚注 → [^n]），区间边界去掉
        $markdown = (string) preg_replace_callback(
            AnnotationToken::PATTERN,
            function (array $m) use ($document, $ctx) {
                $call = $document->annotationCalls[(int) $m[1]] ?? null;

                return $call === null ? '' : $call->template->markdown($call, $ctx);
            },
            $markdown,
        );

        return str_replace('](/attachments/', ']('.rtrim((string) config('app.url'), '/').'/attachments/', $markdown);
    }

    /**
     * 没有源码的内容（IR 构建时生成的，如 toggle 的内容）：只取文字与模板。
     *
     * @param  list<Node>  $nodes
     */
    private function fromNodes(array $nodes, RenderContext $ctx): string
    {
        $markdown = '';
        foreach ($nodes as $node) {
            $markdown .= match (true) {
                $node instanceof Text => $node->value,
                $node instanceof Call => $node->template->markdown($node, $ctx),
                $node instanceof Element => $this->fromNodes($node->children, $ctx),
                default => '',
            };
        }

        return $markdown;
    }

    public function footnotes(RenderContext $ctx): string
    {
        if ($ctx->footnotes === []) {
            return '';
        }
        $lines = array_map(fn (array $note) => '[^'.$note['sn'].']: '.$note['content'], $ctx->footnotes);

        return "\n\n".implode("\n\n", $lines);
    }
}
