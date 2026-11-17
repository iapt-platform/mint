<?php

use App\Services\Markup\Format;
use App\Services\Markup\MarkupService;
use App\Services\Markup\Parsing\CjkFriendlyEmphasisParser;
use App\Services\Markup\RenderOptions;

/**
 * App\Services\Markup 的语法测试：标准 markdown、原始 HTML 白名单、全部模板写法、两种方言、各输出格式。
 * 这里的用例都不需要库里的数据（术语、句子等取数见 MarkupTemplateDataTest）。
 */

/**
 * tree 输出，转成纯数组便于断言。
 *
 * @return list<array<string, mixed>>
 */
function markupTree(string $source, array $options = []): array
{
    $rendered = app(MarkupService::class)->render($source, new RenderOptions(...$options));

    return json_decode(json_encode($rendered), true);
}

function markupRender(string $source, Format $format, array $options = []): string
{
    return app(MarkupService::class)->render($source, new RenderOptions(...array_merge($options, ['format' => $format])));
}

function markupE2(string $source): array
{
    return markupTree($source, ['contentType' => 'markdown-e2']);
}

/**
 * 树里所有模板节点（深度优先，含 props 里嵌套的）。
 *
 * @return list<array<string, mixed>>
 */
function markupTemplates(array $nodes): array
{
    $found = [];
    foreach ($nodes as $node) {
        if (! is_array($node)) {
            continue;
        }
        if (($node['type'] ?? null) === 'tpl') {
            $found[] = $node;
        }
        foreach (['children', 'props'] as $key) {
            if (isset($node[$key]) && is_array($node[$key])) {
                array_push($found, ...markupTemplates(array_values($node[$key])));
            }
        }
        if (! isset($node['type'])) {
            array_push($found, ...markupTemplates(array_values($node)));
        }
    }

    return $found;
}

function markupText(array $nodes): string
{
    $text = '';
    foreach ($nodes as $node) {
        if ($node['type'] === 'text') {
            $text .= $node['value'];
        } else {
            $text .= markupText($node['children'] ?? []);
        }
    }

    return $text;
}

describe('standard markdown', function () {
    it('renders a single paragraph as inline nodes without a p wrapper', function () {
        expect(markupTree('hello *world*'))->toBe([
            ['type' => 'text', 'value' => 'hello '],
            ['type' => 'el', 'tag' => 'em', 'children' => [['type' => 'text', 'value' => 'world']]],
        ]);
    });

    it('renders headings h1 to h6 with stable ids', function () {
        $tree = markupTree("# a\n\n## b\n\n### c\n\n#### d\n\n##### e\n\n###### f");
        expect(array_column($tree, 'tag'))->toBe(['h1', 'h2', 'h3', 'h4', 'h5', 'h6']);
        expect($tree[0]['attrs']['id'])->toMatch('/^h-[0-9a-f]{8}-1$/');
        expect(markupTree("# a\n\n## b")[0]['attrs']['id'])->not->toBe(markupTree("# x\n\n## b")[0]['attrs']['id']);
        expect(markupTree("# a\n\n## b"))->toBe(markupTree("# a\n\n## b"));
    });

    it('renders emphasis, strong, strikethrough and nesting', function () {
        $tree = markupTree('*em* **strong** ~~del~~ ***both***');
        expect(array_values(array_filter(array_column($tree, 'tag'))))->toBe(['em', 'strong', 'del', 'em']);
        expect($tree[6]['children'][0]['tag'])->toBe('strong');
    });

    it('renders links and images and drops unsafe urls', function () {
        $tree = markupTree('[site](https://wikipali.org "t") [bad](javascript:alert(1)) ![pic](/attachments/a.png)');
        expect($tree[0])->toMatchArray(['tag' => 'a', 'attrs' => ['href' => 'https://wikipali.org', 'title' => 't']]);
        expect($tree[2]['tag'])->toBe('a')->and($tree[2])->not->toHaveKey('attrs');
        expect($tree[4])->toMatchArray(['tag' => 'img', 'attrs' => ['src' => '/attachments/a.png', 'alt' => 'pic']]);
    });

    it('prefixes site images with the app url in html', function () {
        config(['app.url' => 'https://example.test']);
        expect(markupRender('![pic](/attachments/a.png)', Format::Html))
            ->toBe('<span><img src="https://example.test/attachments/a.png" alt="pic" /></span>');
    });

    it('renders autolinks', function () {
        expect(markupTree('see https://wikipali.org now')[1])
            ->toMatchArray(['tag' => 'a', 'attrs' => ['href' => 'https://wikipali.org']]);
    });

    it('renders hard and soft line breaks', function () {
        $tree = markupTree("a  \nb\nc");
        expect($tree[1]['tag'])->toBe('br');
        expect($tree[2])->toBe(['type' => 'text', 'value' => "b\nc"]);
    });

    it('renders block quotes', function () {
        $tree = markupTree("> quoted\n>\n> two");
        expect($tree[0]['tag'])->toBe('blockquote')
            ->and(array_column($tree[0]['children'], 'tag'))->toBe(['p', 'p']);
    });

    it('renders tight, loose, ordered and nested lists', function () {
        $tight = markupTree("- a\n- b\n  - c");
        expect($tight[0]['tag'])->toBe('ul');
        expect($tight[0]['children'][0]['children'][0])->toBe(['type' => 'text', 'value' => 'a']);
        expect($tight[0]['children'][1]['children'][1]['tag'])->toBe('ul');

        $loose = markupTree("- a\n\n- b");
        expect($loose[0]['children'][0]['children'][0]['tag'])->toBe('p');

        $ordered = markupTree("3. x\n4. y");
        expect($ordered[0])->toMatchArray(['tag' => 'ol', 'attrs' => ['start' => '3']]);
    });

    it('renders task lists', function () {
        $tree = markupTree("- [x] done\n- [ ] todo");
        expect($tree[0]['children'][0]['children'][0])
            ->toMatchArray(['tag' => 'input', 'attrs' => ['type' => 'checkbox', 'disabled' => 'disabled', 'checked' => 'checked']]);
        expect($tree[0]['children'][1]['children'][0]['attrs'])->not->toHaveKey('checked');
    });

    it('renders tables with alignment', function () {
        $tree = markupTree("| a | b |\n|:--|--:|\n| 1 | 2 |");
        expect($tree[0]['tag'])->toBe('table');
        [$head, $body] = $tree[0]['children'];
        expect($head['tag'])->toBe('thead')->and($body['tag'])->toBe('tbody');
        expect($head['children'][0]['children'][0])->toMatchArray(['tag' => 'th', 'attrs' => ['align' => 'left']]);
        expect($body['children'][0]['children'][1])->toMatchArray(['tag' => 'td', 'attrs' => ['align' => 'right']]);
    });

    it('renders thematic breaks', function () {
        expect(array_column(markupTree("a\n\n---\n\nb"), 'tag'))->toBe(['p', 'hr', 'p']);
    });

    it('renders indented code literally', function () {
        $tree = markupTree("para\n\n    {{note|x}} *y*");
        expect($tree[1]['tag'])->toBe('pre');
        expect($tree[1]['children'][0]['children'][0]['value'])->toBe("{{note|x}} *y*\n");
    });

    it('escapes text in html output', function () {
        expect(markupRender('a < b & "c"', Format::Html))->toBe('<span>a &lt; b &amp; &quot;c&quot;</span>');
    });

    it('returns empty output for empty content', function () {
        expect(markupTree(''))->toBe([]);
        expect(markupRender("  \n", Format::Html))->toBe('');
    });
});

describe('raw html', function () {
    it('keeps allowed inline tags and nests them', function () {
        $tree = markupTree('a <u>under <b>bold</b></u> 10<sup>12</sup>');
        expect($tree[1])->toMatchArray(['tag' => 'u']);
        expect($tree[1]['children'][1]['tag'])->toBe('b');
        expect($tree[3]['tag'])->toBe('sup');
    });

    it('keeps br as a void element', function () {
        expect(markupTree('a<br>b')[1])->toBe(['type' => 'el', 'tag' => 'br', 'children' => []]);
    });

    it('turns unknown tags into spans with data-tag', function () {
        $tree = markupTree('<r>(abhi-ṭṭha-1-291)</r>');
        expect($tree[0])->toMatchArray(['tag' => 'span', 'attrs' => ['data-tag' => 'r']]);
        expect($tree[0]['children'][0]['value'])->toBe('(abhi-ṭṭha-1-291)');
    });

    it('drops scripts, iframes and svg with their content', function () {
        $tree = markupTree('a<script>alert(1)</script>b<iframe src="x"></iframe>c');
        expect(markupText($tree))->toBe('abc');

        $block = markupTree("<svg><text>x</text></svg>\n\nafter");
        expect(markupText($block))->toBe('after');
    });

    it('drops event handlers and styles', function () {
        $tree = markupTree('<span class="k" style="color:red" onclick="x()">t</span>');
        expect($tree[0]['attrs'])->toBe(['class' => 'k']);
    });

    it('drops javascript links in raw html', function () {
        $tree = markupTree('<a href="javascript:alert(1)">x</a> <a href="JaVa script:alert(1)">y</a>');
        expect($tree[0])->not->toHaveKey('attrs');
    });

    it('parses html blocks through the dom', function () {
        $tree = markupTree("<div class=\"box\">\n<p>inside</p>\n</div>");
        expect($tree[0])->toMatchArray(['tag' => 'div', 'attrs' => ['class' => 'box']]);
        expect($tree[0]['children'][1]['tag'])->toBe('p');
    });

    it('renders templates inside html blocks', function () {
        $tree = markupTree("<nt>\n{{note|inner}}\n</nt>");
        expect($tree[0]['attrs']['data-tag'])->toBe('nt');
        expect(markupTemplates($tree)[0]['name'])->toBe('note');
    });
});

describe('templates: syntax', function () {
    it('parses named and positional params, counting named ones as positions', function () {
        $tree = markupTree('{{note|text=body|②}}');
        expect($tree[0])->toMatchArray(['type' => 'tpl', 'name' => 'note', 'props' => ['trigger' => '②']]);
        expect($tree[0]['children'])->toBe([['type' => 'text', 'value' => 'body']]);
    });

    it('drops the newline right after a pipe', function () {
        $tree = markupTree("{{note|\nbody}}");
        expect($tree[0]['children'])->toBe([['type' => 'text', 'value' => 'body']]);
    });

    it('nests templates in params', function () {
        $tree = markupTree('{{note|see {{note|deeper}} and [[dhamma]]}}');
        $names = array_column(markupTemplates($tree), 'name');
        expect($names)->toBe(['note', 'note', 'term']);
    });

    it('keeps an unclosed {{ as text', function () {
        expect(markupText(markupTree('a {{note|b')))->toBe('a {{note|b');
    });

    it('keeps backslash-escaped braces as text', function () {
        expect(markupTree('\{{note|x}}'))->toBe([['type' => 'text', 'value' => '{{note|x}}']]);
    });

    it('renders unknown templates as an unknown component and as source text', function () {
        $tree = markupTree('{{nosuch|a}}');
        expect($tree[0])->toMatchArray(['name' => 'unknown', 'props' => ['name' => 'nosuch', 'source' => '{{nosuch|a}}']]);
        expect(markupRender('{{nosuch|a}}', Format::Text))->toBe('{{nosuch|a}}');
    });

    it('keeps template pipes from splitting table cells', function () {
        $tree = markupTree("| a | b |\n|---|---|\n| {{note|x|y}} | 2 |");
        $cells = $tree[0]['children'][1]['children'][0]['children'];
        expect($cells)->toHaveCount(2);
        expect($cells[0]['children'][0])->toMatchArray(['name' => 'note', 'props' => ['trigger' => 'y']]);
    });

    it('recognizes the sentence shorthand and the sent template', function () {
        $short = markupTree('{{65-464-2-27}}');
        $long = markupTree('{{sent|id=65-464-2-27}}');
        expect($short[0])->toMatchArray(['type' => 'tpl', 'name' => 'sentread']);
        expect($short[0]['props']['id'])->toBe('65-464-2-27');
        expect($long[0]['props']['id'])->toBe('65-464-2-27');
        expect(markupTree('{{65-464-2-27}}', ['mode' => 'edit'])[0]['name'])->toBe('sentedit');
        expect(markupTree('{{sent|id=65-464-2-27|mode=edit}}')[0]['name'])->toBe('sentedit');
    });

    it('reports an invalid sentence id', function () {
        expect(markupTree('{{1-2-3}}')[0]['props']['error'])->toBe('invalid sentence id');
    });
});

describe('footnotes', function () {
    it('accepts note and n', function () {
        expect(markupTree('{{n|short}}')[0]['name'])->toBe('note');
        expect(markupTree('{{note|short}}')[0]['name'])->toBe('note');
    });

    it('allows line breaks, blank lines and blocks inside an inline note', function () {
        $tree = markupTree("正文{{n|第一段\n\n- 列表\n- 两项}}继续正文");
        expect($tree[0]['type'])->toBe('text')->and($tree[2])->toBe(['type' => 'text', 'value' => '继续正文']);
        $note = $tree[1];
        expect($note['name'])->toBe('note');
        expect(array_column($note['children'], 'tag'))->toBe(['p', 'ul']);
    });

    it('joins a note fence into the sentence around it (CJK: no space)', function () {
        $tree = markupTree("前半句\n```note\ncontent\n```\n后半句");
        expect($tree)->toHaveCount(3);
        expect($tree[0])->toBe(['type' => 'text', 'value' => '前半句']);
        expect($tree[1])->toMatchArray(['type' => 'tpl', 'name' => 'note']);
        expect($tree[2])->toBe(['type' => 'text', 'value' => '后半句']);
    });

    it('joins a note fence with a space between latin words', function () {
        $tree = markupTree("first half\n```note\ncontent\n```\nsecond half");
        expect($tree[0]['value'])->toBe('first half');
        expect($tree[2]['value'])->toBe(' second half');
    });

    it('keeps blank lines and pipes inside a note fence', function () {
        $tree = markupTree("文\n```note\n第一段 a|b }}\n\n第二段\n```");
        $note = $tree[1];
        expect(array_column($note['children'], 'tag'))->toBe(['p', 'p']);
        expect($note['children'][0]['children'][0]['value'])->toBe('第一段 a|b }}');
    });

    it('attaches a note fence surrounded by blank lines to the end of the previous paragraph', function () {
        $tree = markupTree("一段话。\n\n```note\n整段注\n```\n\n下一段");
        expect(array_column($tree, 'tag'))->toBe(['p', 'p']);
        expect(end($tree[0]['children']))->toMatchArray(['type' => 'tpl', 'name' => 'note']);
    });

    it('reads trigger from the note fence info string', function () {
        expect(markupTree("a\n```note trigger=\"①\"\nx\n```")[1]['props'])->toBe(['trigger' => '①']);
    });

    it('supports a longer fence so that the note can contain code', function () {
        $tree = markupTree("文\n````note\n```\ncode\n```\n````", ['contentType' => 'markdown-e2']);
        expect($tree[1]['children'][0]['tag'])->toBe('pre');
    });
});

describe('legacy dialect (markdown)', function () {
    it('treats `x` as a single-line note', function () {
        $tree = markupTree('依法`这里是注释`，故为');
        expect($tree[1])->toMatchArray(['name' => 'note']);
        expect($tree[1]['children'][0]['value'])->toBe('这里是注释');
    });

    it('treats a bare ``` fence as a multi-line note joined into the sentence', function () {
        $tree = markupTree("[[bodhisatta]]讲述。\n```\n或译为：亲依止缘。\n```");
        expect(array_column(markupTemplates($tree), 'name'))->toBe(['term', 'note']);
    });

    it('also accepts ```note', function () {
        expect(markupTree("a\n```note\nb\n```")[1]['name'])->toBe('note');
    });

    it('still treats ```lang fences as code', function () {
        $tree = markupTree("```python\nx = 1\n```");
        expect($tree[0]['tag'])->toBe('pre');
        expect($tree[0]['children'][0])->toMatchArray(['tag' => 'code', 'attrs' => ['class' => 'language-python']]);
    });

    it('treats any lowercase name= segment as a named param', function () {
        expect(markupTree('{{note|x=1}}')[0]['children'])->toBe([]);
    });
});

describe('E2 dialect (markdown-e2)', function () {
    it('renders backticks as code, not notes', function () {
        $tree = markupE2('use `{{note|x}}` and `[[term]]`');
        expect($tree[1])->toMatchArray(['tag' => 'code', 'children' => [['type' => 'text', 'value' => '{{note|x}}']]]);
        expect($tree[3]['children'][0]['value'])->toBe('[[term]]');
        expect(markupTemplates($tree))->toBe([]);
    });

    it('renders bare fences as code', function () {
        $tree = markupE2("```\n{{note|x}}\n```");
        expect($tree[0]['tag'])->toBe('pre');
        expect($tree[0]['children'][0]['children'][0]['value'])->toBe("{{note|x}}\n");
    });

    it('keeps pipes and a=b inside note content', function () {
        $tree = markupE2('{{n|含 | 竖线 a=b|trigger=①}}');
        expect($tree[0]['props'])->toBe(['trigger' => '①']);
        expect($tree[0]['children'][0]['value'])->toBe('含 | 竖线 a=b');
    });

    it('only treats declared names as named params', function () {
        $tree = markupE2('{{quality|x=featured}}');
        expect($tree[0]['props']['value'])->toBe('pending');
        expect(markupE2('{{quality|value=featured}}')[0]['props']['value'])->toBe('featured');
    });

    it('still supports note fences', function () {
        expect(markupE2("a\n```note\nb\n```\nc")[1]['name'])->toBe('note');
    });
});

describe('templates without data', function () {
    it('renders mermaid fences in both dialects', function () {
        foreach (['markdown', 'markdown-e2'] as $type) {
            $tree = markupTree("```mermaid\ngraph TD\nA-->B\n```", ['contentType' => $type]);
            expect($tree[0])->toMatchArray(['type' => 'tpl', 'name' => 'mermaid', 'props' => ['text' => "graph TD\nA-->B"]]);
        }
    });

    it('renders mermaid per format', function () {
        $source = "```mermaid\ngraph TD\nA-->B\n```";
        expect(markupRender($source, Format::Html))->toBe("<pre class=\"mermaid\">graph TD\nA--&gt;B</pre>");
        expect(markupRender($source, Format::Text))->toBe('mermaid');
        expect(markupRender($source, Format::Prompt))->toBe("```mermaid\ngraph TD\nA-->B\n```");
        expect(markupRender($source, Format::Markdown))->toBe($source);
    });

    it('renders quality and falls back to pending', function () {
        expect(markupTree('{{quality|featured}}')[0]['props'])->toBe(['value' => 'featured']);
        expect(markupTree('{{quality|bogus}}')[0]['props'])->toBe(['value' => 'pending']);
        expect(markupRender('{{quality|draft}}', Format::Html))->toBe('<span><span class="tag-quality draft">draft</span></span>');
    });

    it('renders category, para, dict-pref and ai', function () {
        expect(markupTree('{{category|vinaya}}')[0])->toMatchArray(['name' => 'category', 'props' => ['name' => 'vinaya']]);
        expect(markupTree('{{para|id=160-9|title=aa|style=card}}', ['channels' => ['c1', 'c2']])[0]['props'])
            ->toBe(['id' => '160-9', 'title' => 'aa', 'style' => 'card', 'channel' => 'c1_c2']);
        expect(markupTree('{{dict-pref|2}}')[0]['props'])->toBe(['currPage' => 2, 'pageSize' => 100]);
        expect(markupTree('{{ai|gpt}}')[0]['props'])->toBe(['model' => 'gpt']);
        expect(markupRender('x{{ai|gpt}}', Format::Prompt))->toBe('x');
        expect(markupRender('x{{ai|gpt}}', Format::Text))->toBe('xai');
    });

    it('renders video and strips unsafe urls', function () {
        expect(markupTree('{{v|https://v.example/a.mp4}}')[0]['props'])
            ->toBe(['url' => 'https://v.example/a.mp4', 'title' => '', 'style' => 'modal']);
        // 显式给空值时用空值（v2 口径：参数存在就不取默认值）
        expect(markupTree('{{v|https://v.example/a.mp4||intro}}')[0]['props'])
            ->toBe(['url' => 'https://v.example/a.mp4', 'title' => 'intro', 'style' => '']);
        expect(markupTree('{{v|javascript:alert(1)}}')[0]['props']['url'])->toBe('');
    });

    it('renders exercise and article', function () {
        expect(markupTree('{{exercise|id=e1|title=Q1}}', ['channels' => ['c1']])[0]['props'])
            ->toBe(['id' => 'e1', 'title' => 'Q1', 'channel' => 'c1']);
        $article = markupTree('{{article|type=chapter|book=168|paragraphs=915|title=T}}')[0]['props'];
        expect($article)->toMatchArray(['type' => 'chapter', 'id' => '168-915', 'title' => 'T', 'book' => '168']);
        expect(markupRender('{{article|type=article|id=x|title=T}}', Format::Text))->toBe('T');
        expect(markupRender('{{article|type=article|id=x|title=T}}', Format::Prompt))->toBe('');
    });

    it('renders nissaya templates and nissaya channel lines', function () {
        expect(markupTree('{{nissaya|evaṃ|ဤသို့=thus}}')[0]['props'])
            ->toMatchArray(['pali' => 'evaṃ', 'meaning' => ['ဤသို့', 'thus']]);

        $lines = markupTree("evaṃ=ဤသို့\nme\$ငါ", ['channelType' => 'nissaya']);
        expect(array_column(markupTemplates($lines), 'name'))->toBe(['nissaya', 'nissaya']);
        expect(markupTemplates($lines)[1]['props']['pali'])->toBe('me');
    });

    it('renders nissaya json content', function () {
        $json = json_encode([
            ['original' => 'evaṃ', 'translation' => 'ဤသို့>thus', 'note' => 'n1'],
            ['sn' => [1], 'word' => ['value' => 'me'], 'meaning' => ['value' => 'ငါ']],
        ]);
        $tree = markupTree($json, ['channelType' => 'nissaya', 'contentType' => 'json']);
        expect(array_column(markupTemplates($tree), 'name'))->toBe(['nissaya', 'note', 'nissaya']);
        expect(markupTemplates($tree)[0]['props']['meaning'])->toBe(['ဤသို့', 'thus']);
    });

    it('turns a list item holding only a div into a toggle', function () {
        $tree = markupTree("- <div>title</div>\n- plain");
        $first = $tree[0]['children'][0]['children'][0];
        expect($first)->toMatchArray(['type' => 'tpl', 'name' => 'toggle']);
        expect($first['children'][0]['tag'])->toBe('div');
        expect($tree[0]['children'][1]['children'][0]['type'])->toBe('text');
    });

    it('renders a term without a channel as the plain word', function () {
        $tree = markupTree('[[dhamma]]');
        expect($tree[0])->toMatchArray(['name' => 'term', 'props' => ['word' => 'dhamma', 'parentChannelId' => null, 'parentStudioId' => null]]);
        expect($tree[0]['children'])->toBe([['type' => 'text', 'value' => 'dhamma']]);
        expect(markupTree('[[dhamma#法]]')[0]['props']['meaning'])->toBe('法');
    });
});

describe('output formats', function () {
    $source = "# 标题\n\n正文[[dhamma]]{{n|注释 **粗**|trigger=①}}结束。\n\n- 一\n- 二";

    it('renders html with sidenotes and an endnote list', function () use ($source) {
        $html = markupRender($source, Format::Html, ['footnote' => true]);
        expect($html)
            ->toContain('<h1 id="')
            ->toContain('<span class="term-ref term_invalid" data-id="" data-term="dhamma">dhamma</span>')
            ->toMatch('/<label for="(sn-[0-9a-f]{8}-1)" class="margin-toggle sidenote-number">①<\/label><input type="checkbox" id="\1" class="margin-toggle"\/>/')
            ->toContain('<span class="sidenote"><span>注释 <strong>粗</strong></span></span>')
            ->toContain('<ul><li>一</li><li>二</li></ul>')
            ->toContain('<div><h1>endnote</h1><p><a name="footnote-1">[1]</a> <span>注释 <strong>粗</strong></span></p></div>');
    });

    it('omits the endnote list without the footnote option', function () use ($source) {
        expect(markupRender($source, Format::Html))->not->toContain('endnote');
    });

    it('renders text, simple and prompt', function () use ($source) {
        expect(markupRender($source, Format::Text))->toBe("标题\n正文dhamma①结束。\n一\n二");
        expect(markupRender($source, Format::Simple))->toBe("标题\n正文dhamma结束。\n一\n二");
        expect(markupRender($source, Format::Prompt))->toBe("标题\n正文dhamma结束。\n一\n二");
    });

    it('renders markdown with footnote references', function () use ($source) {
        expect(markupRender($source, Format::Markdown, ['footnote' => true]))
            ->toBe("# 标题\n\n正文dhamma[^1]结束。\n\n- 一\n- 二\n\n[^1]: 注释 **粗**");
    });

    it('renders legacy backtick notes as markdown footnotes', function () {
        expect(markupRender('a`b`c', Format::Markdown, ['footnote' => true]))->toBe("a[^1]c\n\n[^1]: b");
    });

    it('prefixes attachment links in markdown', function () {
        config(['app.url' => 'https://example.test']);
        expect(markupRender('![a](/attachments/x.png)', Format::Markdown))->toBe('![a](https://example.test/attachments/x.png)');
    });

    it('numbers footnotes per document in renderMany', function () {
        $out = app(MarkupService::class)->renderMany(['a' => '{{n|x}}', 'b' => '{{n|y}}'], new RenderOptions(format: Format::Markdown, footnote: true));
        expect($out)->toBe(['a' => "[^1]\n\n[^1]: x", 'b' => "[^1]\n\n[^1]: y"]);
    });
});

describe('legacy backtick edge cases (from real data)', function () {
    it('treats a pair of double backticks as the note boundary', function () {
        // 库里的写法：``注释``。v2 的正则把正文算成「`注释」、剩一个反引号；作者本意是整对是边界
        $tree = markupTree('婆罗门。``x y``后文');
        expect($tree[1])->toMatchArray(['name' => 'note']);
        expect($tree[1]['children'][0]['value'])->toBe('x y');
        expect($tree[2])->toBe(['type' => 'text', 'value' => '后文']);
    });

    it('accepts a backtick note whose backticks sit on their own lines', function () {
        $tree = markupTree("然后她们（宫女）\n`\n环绕（parikkhipiṃsu）一词用的是复数。\n`\n用屏风围上。");
        expect(array_column(markupTemplates($tree), 'name'))->toBe(['note']);
        expect(markupRender("她们\n`\n注释\n`\n用屏风", Format::Text))->toBe("她们\n\n用屏风");
    });

    it('pairs backtick notes across lines like the authors meant', function () {
        // 库里的真实写法：两条多行注释。v2 只在单行内配对，配成了「奉为」一条 + 一个跨行代码 span
        $tree = markupTree("炬口王`  炬口王 (Okkāka)：……\n其音、义皆与巴利语不同。\n`奉为`  奉为 (dahanti)：直译为放置。`祖父。");
        expect(array_column(markupTemplates($tree), 'name'))->toBe(['note', 'note']);
        expect(markupTemplates($tree)[1]['children'][0]['value'])->toBe('奉为 (dahanti)：直译为放置。');
        expect(markupRender("炬口王`注1\n续`奉为`注2`祖父", Format::Text))->toBe('炬口王奉为祖父');
    });

    it('does not pair backticks across a blank line', function () {
        $tree = markupTree("王`  炬口王\n\n奉为`");
        expect(markupTemplates($tree))->toBe([]);
        expect(markupText($tree))->toBe('王`  炬口王奉为`');
    });

    it('does not join a note fence with a following thematic break', function () {
        $tree = markupTree("瞿低迦經\n```\n莊春江 譯\n```\n***\n第三");
        expect(array_column($tree, 'tag'))->toBe(['p', 'hr', 'p']);
    });
});

/*
 * 新服务不准回头依赖 v2 的渲染与帮手类（v2 继续用它们，新服务各自独立）。
 * 一个类一条：pest-plugin-arch 的数组写法会空过。
 */
foreach (['App\Http\Api\MdRender', 'App\Http\Api\TemplateRender', 'App\Http\Api\ChannelApi', 'App\Http\Controllers\CorpusController'] as $legacy) {
    arch("App\\Services\\Markup does not use {$legacy}")
        ->expect('App\Services\Markup')
        ->not->toUse($legacy);
}

describe('emphasis next to CJK punctuation (markdown-cjk-friendly)', function () {
    it('renders bold that wraps CJK punctuation', function (string $source, string $html) {
        expect(markupRender($source, Format::Html))->toBe("<span>{$html}</span>");
    })->with([
        '引号在粗体里' => ['从**“tuccha”**的角度', '从<strong>“tuccha”</strong>的角度'],
        '括号在粗体里' => ['是**（括号）**吗', '是<strong>（括号）</strong>吗'],
        '标点在粗体外' => ['“**粗**”', '“<strong>粗</strong>”'],
        '句号紧跟' => ['**粗**。', '<strong>粗</strong>。'],
        '斜体' => ['这是*“斜”*体', '这是<em>“斜”</em>体'],
    ]);

    it('keeps CommonMark behaviour when neither side is CJK', function (string $source, string $html) {
        expect(markupRender($source, Format::Html))->toBe("<span>{$html}</span>");
    })->with([
        ['a **b** c', 'a <strong>b</strong> c'],
        ['a**b**c', 'a<strong>b</strong>c'],
        ['**ramu** + tano', '<strong>ramu</strong> + tano'],
        ['**foo "*bar*" foo**', '<strong>foo &quot;<em>bar</em>&quot; foo</strong>'],
        ['*(**foo**)*', '<em>(<strong>foo</strong>)</em>'],
        ['a * b * c', 'a * b * c'],
        ['a**"foo"**', 'a**&quot;foo&quot;**'],
        ['**foo**bar', '<strong>foo</strong>bar'],
    ]);

    it('only differs from the CommonMark flanking rule when a CJK character is involved', function () {
        $commonmark = function (string $before, string $after): array {
            $as = preg_match('/^\pZ|\s/u', $after) === 1;
            $ap = preg_match('/^[\p{P}\p{S}]/u', $after) === 1;
            $bs = preg_match('/^\pZ|\s/u', $before) === 1;
            $bp = preg_match('/^[\p{P}\p{S}]/u', $before) === 1;

            return [! $as && (! $ap || $bs || $bp), ! $bs && (! $bp || $as || $ap)];
        };
        $chars = ['a', 'Z', '0', ' ', "\n", '"', '(', ')', '.', ',', '-', 'ā', 'ṃ', '“', '”', '，', '。', '（', '）', '€'];
        foreach ($chars as $before) {
            foreach ($chars as $after) {
                expect(CjkFriendlyEmphasisParser::flanking($before, $after))
                    ->toBe($commonmark($before, $after), "before={$before} after={$after}");
            }
        }
    });
});

describe('toc', function () {
    $source = "{{toc}}\n\n# 一级\n\n## 甲 [[dhamma]]\n\n### 甲一\n\n#### 甲一a\n\n## 乙\n\n正文";

    /** 树里的目录节点 */
    $toc = function (array $tree): array {
        foreach ($tree as $node) {
            if (($node['name'] ?? null) === 'toc') {
                return $node;
            }
            foreach ($node['children'] ?? [] as $child) {
                if (($child['name'] ?? null) === 'toc') {
                    return $child;
                }
            }
        }

        return [];
    };

    /** 列表 → [[层级, 链接 href, 文字], …] */
    $items = function (array $list, int $depth = 1) use (&$items): array {
        $out = [];
        foreach ($list['children'] as $li) {
            foreach ($li['children'] as $child) {
                if ($child['tag'] === 'a') {
                    $out[] = [$depth, $child['attrs']['href'], markupText($child['children'])];
                } elseif ($child['tag'] === 'ul') {
                    array_push($out, ...$items($child, $depth + 1));
                }
            }
        }

        return $out;
    };

    it('lists h2 and h3 by default, nested, linking to the heading ids', function () use ($source, $toc, $items) {
        $tree = markupTree($source);
        $node = $toc($tree);
        expect($node['props'])->toBe(['title' => '目录']);

        $h2 = collect($tree)->where('tag', 'h2')->values();
        expect($items($node['children'][0]))->toBe([
            [1, '#'.$h2[0]['attrs']['id'], '甲 dhamma'],
            [2, '#'.collect($tree)->firstWhere('tag', 'h3')['attrs']['id'], '甲一'],
            [1, '#'.$h2[1]['attrs']['id'], '乙'],
        ]);
    });

    it('keeps templates inside heading text', function () use ($source, $toc) {
        $link = $toc(markupTree($source))['children'][0]['children'][0]['children'][0];
        expect(collect($link['children'])->firstWhere('name', 'term')['props']['word'])->toBe('dhamma');
    });

    it('skips a hand-written heading right before it', function () use ($toc, $items) {
        $tree = markupTree("## 目录\n\n{{toc}}\n\n## 甲\n\n## 乙");
        expect(array_column($items($toc($tree)['children'][0]), 2))->toBe(['甲', '乙']);
        // 已经有手写标题，不再显示第二个
        expect($toc($tree)['props'])->toBe([]);
        expect(markupRender("## 目录\n\n{{toc}}\n\n## 甲", Format::Html))->not->toContain('toc-title');
    });

    it('accepts a level range or a single level', function () use ($source, $toc, $items) {
        expect(array_column($items($toc(markupTree(str_replace('{{toc}}', '{{toc|2-4}}', $source)))['children'][0]), 2))
            ->toBe(['甲 dhamma', '甲一', '甲一a', '乙']);
        expect(array_column($items($toc(markupTree(str_replace('{{toc}}', '{{toc|2}}', $source)))['children'][0]), 2))
            ->toBe(['甲 dhamma', '乙']);
    });

    it('takes a custom title, or none', function () use ($source, $toc) {
        expect($toc(markupTree(str_replace('{{toc}}', '{{toc|title=本条目录}}', $source)))['props'])->toBe(['title' => '本条目录']);
        expect($toc(markupTree(str_replace('{{toc}}', '{{toc|title=}}', $source)))['props'])->toBe([]);
    });

    it('renders html, text and markdown', function () use ($source) {
        expect(markupRender($source, Format::Html))
            ->toMatch('/^<nav class="toc"><div class="toc-title">目录<\/div><ul><li><a href="#(h-[0-9a-f]{8}-2)">甲 .*<\/a><ul><li><a href="#h-[0-9a-f]{8}-3">甲一<\/a><\/li><\/ul><\/li><li><a href="#h-[0-9a-f]{8}-5">乙<\/a><\/li><\/ul><\/nav><h1 id="h-[0-9a-f]{8}-1">一级<\/h1><h2 id="\1">/');
        expect(markupRender($source, Format::Text))->toStartWith("目录\n甲 dhamma\n  甲一\n乙\n");
        expect(markupRender($source, Format::Markdown))->toMatch('/^目录\n- \[甲 dhamma\]\(#h-[0-9a-f]{8}-2\)\n  - \[甲一\]\(#h-[0-9a-f]{8}-3\)\n- \[乙\]\(#h-[0-9a-f]{8}-5\)\n/');
    });

    it('renders nothing when there are no headings', function () {
        expect(markupTree('{{toc}} 正文')[0])->toMatchArray(['name' => 'toc', 'children' => []]);
        expect(markupRender('{{toc}}', Format::Html))->toBe('');
    });
});
