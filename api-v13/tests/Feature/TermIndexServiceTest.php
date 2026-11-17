<?php

use App\Models\DhammaTerm;
use App\Models\TermCategory;
use App\Services\TermIndexService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/*
 * 术语的 OpenSearch 文档用新渲染器构建：内容 = 摘要 + 正文；分类、质量优先取 v3 的列，没有时从正文模板抽。
 */

function indexTerm(string $channel, array $attrs): DhammaTerm
{
    $term = new DhammaTerm;
    $term->forceFill($attrs + [
        'id' => random_int(1, PHP_INT_MAX),
        'guid' => (string) Str::uuid(),
        'word' => 'dhamma',
        'word_en' => 'dhamma',
        'meaning' => '法',
        'channal' => $channel,
        'language' => 'zh-Hans',
        'owner' => 'owner',
        'editor_id' => 0,
        'create_time' => time() * 1000,
        'modify_time' => time() * 1000,
    ])->save();

    return $term;
}

it('builds a v3 term document from summary + note with the new renderer and the v3 columns', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner, '译文');
    $term = indexTerm($channel, [
        'owner' => $owner,
        'summary' => '一句话摘要。',
        'note' => "## 小节\n\n正文**段落**。",
        'content_type' => 'markdown-e2',
        'quality' => 'standard',
    ]);
    TermCategory::create(['term_guid' => $term->guid, 'category' => '法义', 'position' => 0]);

    $doc = app(TermIndexService::class)->buildDocument($term->guid);

    expect($doc['summary']['text'])->toBe('一句话摘要。');
    expect($doc['content']['display'])->toContain('一句话摘要。')->toContain('<h2')->toContain('<strong>段落</strong>');
    expect($doc['content']['text']['zh'])->toContain('正文段落。')->not->toContain('**');
    expect($doc['tags'])->toBe(['category:法义', 'quality:standard']);
    expect($doc['path'])->toBe('owner/译文');
    expect($doc['metadata']['channel'])->toBe($channel);
});

it('falls back to templates in the note for terms written through v2', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    $term = indexTerm($channel, [
        'owner' => $owner,
        'note' => "{{quality|draft}}\n第一段。\n\n第二段。{{category|律学}} {{category|学处}}",
    ]);

    $doc = app(TermIndexService::class)->buildDocument($term->guid);

    expect($doc['tags'])->toBe(['category:律学', 'category:学处', 'quality:draft']);
    expect($doc['summary']['text'])->not->toBe('')->not->toContain('{{');
});
