<?php

use App\Services\AnnotationLayout;

/**
 * 阅读页批注排版：glossary 替换、issue 高亮、note 插入，以及它们交叠时的处理。
 * 偏移一律是字符偏移（mb），区间半开 [start, end)。
 */
function layoutGlossary(string $id, int $start, int $end, string $title): array
{
    return compact('id', 'start', 'end', 'title');
}

function layoutIssue(string $id, ?int $start, ?int $end, ?string $severity = 'warning'): array
{
    return compact('id', 'start', 'end', 'severity');
}

function layoutNote(string $id, int $at, string $html, array $order = [0]): array
{
    return compact('id', 'at', 'html', 'order');
}

/** 把 issue 的开标签压成 <I:ids> 便于断言 */
function layoutTags(string $html): string
{
    $html = preg_replace('/<span class="anno-issue anno-issue-\w+" data-discussion-ids="([^"]*)" data-severity="\w*">/', '<I:$1>', $html);

    return preg_replace('/<span class="anno-issue-mark anno-issue-(\w+)" data-discussion-ids="([^"]*)".*?<\/i><\/span>/', '<!$1:$2>', $html);
}

// 原文：比库入村托钵（0 比 1 库 2 入 3 村 4 托 5 钵）
const LAYOUT_TEXT = '比库入村托钵';

/* ------------------------------------------------------------------ locate */

it('locates by position when the quote agrees', function () {
    expect(AnnotationLayout::locate(LAYOUT_TEXT, 2, 4, '入村'))->toBe([2, 4]);
});

it('relocates by quote when the position is stale, preferring prefix/suffix, then the nearest', function () {
    $text = '村里有村，村外有村';
    // 位置失效（正文改过），按摘录找；有两处「有村」，后缀「外」只吻合第一处之后……这里用前缀「，村外」选第二处
    expect(AnnotationLayout::locate($text, 0, 1, '有村', '村外', ''))->toBe([7, 9])
        ->and(AnnotationLayout::locate($text, 6, 7, '有村'))->toBe([7, 9])       // 无前后缀：取离原位置最近的
        ->and(AnnotationLayout::locate($text, null, null, '有村'))->toBe([2, 4]) // 无位置：取第一处
        ->and(AnnotationLayout::locate($text, 0, 2, '没有'))->toBeNull()
        ->and(AnnotationLayout::locate($text, 3, 99, null))->toBeNull()         // 越界且无摘录
        ->and(AnnotationLayout::locate($text, 3, 4, null))->toBe([3, 4]);
});

/* ------------------------------------------------------------------ glossary */

it('replaces a glossary span with [[title#text]]', function () {
    $out = AnnotationLayout::apply(LAYOUT_TEXT, [layoutGlossary('g1', 0, 2, 'bhikkhu')], [], []);

    expect($out['content'])->toBe('[[bhikkhu#比库]]入村托钵')
        ->and($out['glossaries'])->toBe(['g1']);
});

it('keeps the outer glossary when one contains another, and the earlier one when they cross', function () {
    $contain = AnnotationLayout::apply(LAYOUT_TEXT, [layoutGlossary('in', 1, 2, 'a'), layoutGlossary('out', 0, 4, 'b')], [], []);
    $cross = AnnotationLayout::apply(LAYOUT_TEXT, [layoutGlossary('late', 3, 6, 'a'), layoutGlossary('early', 2, 4, 'b')], [], []);
    $touch = AnnotationLayout::apply(LAYOUT_TEXT, [layoutGlossary('x', 0, 2, 'a'), layoutGlossary('y', 2, 4, 'b')], [], []);

    expect($contain['content'])->toBe('[[b#比库入村]]托钵')
        ->and($cross['content'])->toBe('比库[[b#入村]]托钵')
        ->and($touch['content'])->toBe('[[a#比库]][[b#入村]]托钵');   // 首尾相接不算交叠
});

it('drops a glossary that would cut existing markup or contains reserved characters', function () {
    $text = '缘起于[[sāvatthī]]。';   // 受保护区间 [3, 17)
    $out = AnnotationLayout::apply($text, [
        layoutGlossary('cut', 1, 5, 'x'),     // 切进已有术语
        layoutGlossary('ok', 0, 2, 'y'),
    ], [], []);
    $bad = AnnotationLayout::apply('a|b c', [layoutGlossary('pipe', 0, 3, 't'), layoutGlossary('hash', 4, 5, 'a#b')], [], []);

    expect($out['content'])->toBe('[[y#缘起]]于[[sāvatthī]]。')
        ->and($bad['content'])->toBe('a|b c')
        ->and($bad['glossaries'])->toBe([]);
});

/* ------------------------------------------------------------------ issue */

it('wraps an issue span with its id and severity class', function () {
    $out = AnnotationLayout::apply(LAYOUT_TEXT, [], [layoutIssue('i1', 2, 4, 'error')], []);

    expect($out['content'])->toBe('比库<span class="anno-issue anno-issue-error" data-discussion-ids="i1" data-severity="error">入村</span>托钵');
});

it('merges overlapping and nested issues, taking the union of ids and the heaviest severity', function () {
    $overlap = AnnotationLayout::apply(LAYOUT_TEXT, [], [layoutIssue('a', 0, 3, 'suggestion'), layoutIssue('b', 2, 5, 'error')], []);
    $nested = AnnotationLayout::apply(LAYOUT_TEXT, [], [layoutIssue('outer', 0, 6), layoutIssue('inner', 2, 3, 'error')], []);
    $chain = AnnotationLayout::apply(LAYOUT_TEXT, [], [layoutIssue('a', 0, 2), layoutIssue('c', 4, 6), layoutIssue('b', 1, 5)], []);
    $touch = AnnotationLayout::apply(LAYOUT_TEXT, [], [layoutIssue('a', 0, 2), layoutIssue('b', 2, 4)], []);

    expect(layoutTags($overlap['content']))->toBe('<I:a,b>比库入村托</span>钵')
        ->and($overlap['issues'][0]['severity'])->toBe('error')
        ->and(layoutTags($nested['content']))->toBe('<I:outer,inner>比库入村托钵</span>')
        ->and(layoutTags($chain['content']))->toBe('<I:a,b,c>比库入村托钵</span>')   // 第三条把两条不相交的连起来
        ->and(layoutTags($touch['content']))->toBe('<I:a>比库</span><I:b>入村</span>托钵');
});

it('extends an issue to fully contain a glossary it partly overlaps', function () {
    // glossary 比库 [0,2)；issue 库入 [1,3) 交叉 → 扩成 [0,3)
    $cross = AnnotationLayout::apply(LAYOUT_TEXT, [layoutGlossary('g', 0, 2, 'bhikkhu')], [layoutIssue('i', 1, 3)], []);
    // issue 在 glossary 内部 → 扩成与 glossary 相同
    $inside = AnnotationLayout::apply(LAYOUT_TEXT, [layoutGlossary('g', 0, 4, 't')], [layoutIssue('i', 1, 2)], []);
    // issue 已包住 glossary → 不动
    $contains = AnnotationLayout::apply(LAYOUT_TEXT, [layoutGlossary('g', 2, 4, 't')], [layoutIssue('i', 1, 5)], []);

    expect(layoutTags($cross['content']))->toBe('<I:i>[[bhikkhu#比库]]入</span>村托钵')
        ->and(layoutTags($inside['content']))->toBe('<I:i>[[t#比库入村]]</span>托钵')
        ->and(layoutTags($contains['content']))->toBe('比<I:i>库[[t#入村]]托</span>钵');
});

it('merges issues that only meet after being extended over a glossary', function () {
    // glossary [2,4) 入村；issue a [0,3) 扩到 [0,4)，issue b [3,6) 扩到 [2,6)，两者因此交叠 → 合并
    $out = AnnotationLayout::apply(LAYOUT_TEXT, [layoutGlossary('g', 2, 4, 't')], [layoutIssue('a', 0, 3), layoutIssue('b', 3, 6)], []);

    expect(layoutTags($out['content']))->toBe('<I:a,b>比库[[t#入村]]托钵</span>');
});

it('extends an issue over existing markup instead of cutting it', function () {
    $text = '缘起于[[sāvatthī]]。';
    $out = AnnotationLayout::apply($text, [], [layoutIssue('i', 2, 6)], []);

    expect(layoutTags($out['content']))->toBe('缘起<I:i>于[[sāvatthī]]</span>。');
});

it('puts issues without a usable position into one end mark coloured by the heaviest severity', function () {
    $out = AnnotationLayout::apply(LAYOUT_TEXT, [], [
        layoutIssue('a', null, null, 'suggestion'),
        layoutIssue('b', 3, 99, 'error'),      // 越界当作没有位置
        layoutIssue('c', 0, 2, 'warning'),
    ], []);

    expect(layoutTags($out['content']))->toBe('<I:c>比库</span>入村托钵<!error:a,b>');
});

it('emits no issue markup when issues are turned off, but still replaces glossaries', function () {
    $out = AnnotationLayout::apply(LAYOUT_TEXT, [layoutGlossary('g', 0, 2, 'bhikkhu')], [layoutIssue('i', 0, 4), layoutIssue('j', null, null)], [], false);

    expect($out['content'])->toBe('[[bhikkhu#比库]]入村托钵');
});

/* ------------------------------------------------------------------ note */

it('moves a note out of a glossary or existing markup, but leaves it inside an issue', function () {
    $out = AnnotationLayout::apply(LAYOUT_TEXT, [layoutGlossary('g', 0, 2, 't')], [layoutIssue('i', 2, 6)], [
        layoutNote('n1', 1, '{N1}'),   // glossary 内部 → 挪到 2
        layoutNote('n2', 4, '{N2}'),   // issue 内部 → 原地
    ]);
    $markup = AnnotationLayout::apply('缘起于[[sāvatthī]]。', [], [], [layoutNote('n', 5, '{N}')]);

    expect(layoutTags($out['content']))->toBe('[[t#比库]]{N1}<I:i>入村{N2}托钵</span>')
        ->and($markup['content'])->toBe('缘起于[[sāvatthī]]{N}。');
});

it('orders closing issue, notes, then opening issue at the same position', function () {
    $out = AnnotationLayout::apply(LAYOUT_TEXT, [], [layoutIssue('a', 0, 2), layoutIssue('b', 2, 4)], [
        layoutNote('late', 2, '{L}', [1]),
        layoutNote('early', 2, '{E}', [0]),
    ]);

    expect(layoutTags($out['content']))->toBe('<I:a>比库</span>{E}{L}<I:b>入村</span>托钵')
        ->and($out['notes'])->toBe(['early', 'late']);
});

it('clamps note positions into the sentence and appends end notes before the issue mark', function () {
    $out = AnnotationLayout::apply(LAYOUT_TEXT, [], [layoutIssue('x', null, null, 'error')], [layoutNote('n', 99, '{N}')]);

    expect(layoutTags($out['content']))->toBe('比库入村托钵{N}<!error:x>');
});
