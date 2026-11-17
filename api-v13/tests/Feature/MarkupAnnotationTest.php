<?php

use App\Services\AnnotationLayout;
use App\Services\HtmlAnnotationMarkup;
use App\Services\Markup\Annotation\AnnotationSet;
use App\Services\Markup\Annotation\AnnotationToken;
use App\Services\Markup\Format;
use App\Services\Markup\MarkupService;
use App\Services\Markup\RenderOptions;

/**
 * 阅读页批注的结构化注入：AnnotationLayout 用 AnnotationSet 作输出策略，插入点写占位符，
 * App\Services\Markup 解析成 anno-* / note 模板节点，各格式都能输出。
 */

/**
 * 排版 + 渲染一句。
 *
 * @param  array<string, mixed>  $layout  AnnotationLayout::apply 的 glossaries / issues / notes / assessment
 */
function annotated(string $content, array $layout, Format $format = Format::Tree, ?callable $notes = null): array|string
{
    $set = new AnnotationSet;
    $noteSpecs = $notes ? $notes($set) : [];
    $result = AnnotationLayout::apply(
        $content,
        $layout['glossaries'] ?? [],
        $layout['issues'] ?? [],
        $noteSpecs,
        true,
        $layout['assessment'] ?? null,
        $set,
    );
    $rendered = app(MarkupService::class)->renderMany(
        [['source' => $result['content'], 'annotations' => $set]],
        new RenderOptions(format: $format),
    )[0];

    return is_array($rendered) ? json_decode(json_encode($rendered), true) : $rendered;
}

it('wraps an issue range as an anno-issue node in the tree', function () {
    $tree = annotated('first sentence', ['issues' => [['id' => 'i1', 'start' => 0, 'end' => 5, 'severity' => 'error']]]);

    expect($tree[0])->toMatchArray(['type' => 'tpl', 'name' => 'anno-issue', 'props' => ['ids' => ['i1'], 'severity' => 'error']]);
    expect($tree[0]['children'])->toBe([['type' => 'text', 'value' => 'first']]);
    expect($tree[1])->toBe(['type' => 'text', 'value' => ' sentence']);
});

it('renders the same html as the legacy injection', function () {
    $layout = [
        'issues' => [
            ['id' => 'i1', 'start' => 0, 'end' => 5, 'severity' => 'warning'],
            ['id' => 'i2', 'start' => null, 'end' => null, 'severity' => 'error'],
        ],
        'assessment' => ['id' => 'a1', 'score' => 85],
    ];
    $html = annotated('first sentence', $layout, Format::Html);
    $markup = new HtmlAnnotationMarkup;

    expect($html)->toBe('<span>'
        .HtmlAnnotationMarkup::issueOpenTag(['i1'], 'warning').'first</span> sentence'
        .$markup->issueMark(['i2'], 'error')
        .$markup->assessmentMark('a1', 85)
        .'</span>');
});

it('keeps highlighted text and drops icons in plain formats', function () {
    $layout = [
        'issues' => [['id' => 'i1', 'start' => 0, 'end' => 5, 'severity' => null], ['id' => 'i2', 'start' => null, 'end' => null, 'severity' => null]],
        'assessment' => ['id' => 'a1', 'score' => 30],
    ];

    expect(annotated('first sentence', $layout, Format::Text))->toBe('first sentence');
    expect(annotated('first sentence', $layout, Format::Markdown))->toBe('first sentence');
});

it('exposes assessments and issue marks as props in the tree', function () {
    $tree = annotated('句子', [
        'issues' => [['id' => 'i2', 'start' => null, 'end' => null, 'severity' => 'suggestion']],
        'assessment' => ['id' => 'a1', 'score' => 65],
    ]);

    expect($tree[1])->toMatchArray(['name' => 'anno-issue-mark', 'props' => ['ids' => ['i2'], 'severity' => 'suggestion']]);
    expect($tree[2])->toMatchArray(['name' => 'anno-assessment', 'props' => ['id' => 'a1', 'score' => 65, 'level' => 'medium']]);
});

it('renders the unassessed mark as a level-none assessment', function () {
    $set = new AnnotationSet;
    $source = '句子'.$set->unassessedMark();
    $render = fn (Format $f) => app(MarkupService::class)->renderMany([['source' => $source, 'annotations' => $set]], new RenderOptions(format: $f))[0];

    expect(json_decode(json_encode($render(Format::Tree)), true)[1])->toMatchArray(['name' => 'anno-assessment', 'props' => ['level' => 'none']]);
    expect($render(Format::Html))->toBe('<span>句子'.HtmlAnnotationMarkup::unassessedMark().'</span>');
});

it('lifts an issue that crosses an emphasis boundary to wrap the whole element', function () {
    // 「*斜体*」不在 AnnotationLayout 的受保护区间里：区间 [3, 8) 从斜体中间开始
    $content = 'ab *cdef* gh';
    $tree = annotated($content, ['issues' => [['id' => 'i1', 'start' => 5, 'end' => 10, 'severity' => null]]]);

    $issue = collect($tree)->firstWhere('name', 'anno-issue');
    expect($issue)->not->toBeNull();
    expect(collect($issue['children'])->pluck('tag')->filter()->values()->all())->toBe(['em']);
    expect(json_encode($tree, JSON_UNESCAPED_UNICODE))->not->toContain("\u{E002}");
});

it('injects notes as structured note nodes with markdown and templates kept', function () {
    $tree = annotated('first sentence', [], Format::Tree, fn (AnnotationSet $set) => [
        ['id' => 'n1', 'at' => 5, 'order' => [0, 0, 0], 'html' => $set->note('见 **粗体** 与 [[dhamma]]', ['cite' => '义注', 'citelink' => '1-2-3-4'])],
    ]);

    expect($tree[1])->toMatchArray(['name' => 'note', 'props' => ['cite' => '义注', 'citelink' => '1-2-3-4']]);
    expect(collect($tree[1]['children'])->pluck('tag')->filter()->values()->all())->toBe(['strong']);
    expect(collect($tree[1]['children'])->firstWhere('name', 'term')['props']['word'])->toBe('dhamma');
});

it('renders note sidenotes with only inline html even for block content', function () {
    $html = annotated('first sentence', [], Format::Html, fn (AnnotationSet $set) => [
        ['id' => 'n1', 'at' => 5, 'order' => [0, 0, 0], 'html' => $set->note("1. 第一点\n2. 第二点\n\n另起一段")],
    ]);

    expect($html)->toContain('<span class="sidenote">')
        ->not->toContain('<ol')->not->toContain('<p>')
        ->toContain('<span class="md-ol"><span class="md-li">第一点</span>')
        ->toContain('<br /><span class="md-p">另起一段</span>');
});

it('renders notes as markdown footnotes', function () {
    $markdown = app(MarkupService::class)->renderMany(
        [['source' => 'first'.($set = new AnnotationSet)->note('注释正文').' sentence', 'annotations' => $set]],
        new RenderOptions(format: Format::Markdown, footnote: true),
    )[0];

    expect($markdown)->toBe("first[^1] sentence\n\n[^1]: 注释正文");
});

it('parses the note body in its own dialect', function () {
    $tree = annotated('句子', [], Format::Tree, fn (AnnotationSet $set) => [
        ['id' => 'n1', 'at' => 2, 'order' => [0, 0, 0], 'html' => $set->note('用 `code`', [], 'markdown-e2')],
    ]);

    expect($tree[1]['children'][1])->toMatchArray(['type' => 'el', 'tag' => 'code']);
});

it('gives sidenotes page-unique ids from the item id prefix', function () {
    $html = app(MarkupService::class)->renderMany([
        ['source' => 'a{{n|x}}', 'idPrefix' => '65-1-2-3'],
        ['source' => 'b{{n|y}}', 'idPrefix' => '65-1-4-5'],
    ], new RenderOptions(format: Format::Html));

    expect($html[0])->toContain('id="sn-65-1-2-3-1"')->and($html[1])->toContain('id="sn-65-1-4-5-1"');
});

it('ignores annotation tokens and internal template names written in content', function () {
    $stray = '正文'.AnnotationToken::make(0).'{{anno-issue|ids=x}}';
    $tree = json_decode(json_encode(app(MarkupService::class)->render($stray, new RenderOptions)), true);

    expect($tree[0])->toBe(['type' => 'text', 'value' => '正文']);
    expect($tree[1])->toMatchArray(['name' => 'unknown']);
});
