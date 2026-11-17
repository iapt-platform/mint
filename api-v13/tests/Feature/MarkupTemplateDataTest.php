<?php

use App\Models\Article;
use App\Models\BookTitle;
use App\Models\Channel;
use App\Models\DhammaTerm;
use App\Models\Discussion;
use App\Models\PaliText;
use App\Services\Markup\Format;
use App\Services\Markup\MarkupService;
use App\Services\Markup\RenderOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * App\Services\Markup 里要查库的模板：术语、句子、quote、ql、qa、Tpl、g、ref，以及批量取数、逐段对读、嵌套层数。
 */
function markupDataTree(string $source, array $options = []): array
{
    return json_decode(json_encode(app(MarkupService::class)->render($source, new RenderOptions(...$options))), true);
}

function markupDataRender(string $source, Format $format, array $options = []): string
{
    return app(MarkupService::class)->render($source, new RenderOptions(...array_merge($options, ['format' => $format])));
}

function makeMarkupTerm(array $attrs): DhammaTerm
{
    $term = new DhammaTerm;
    $term->forceFill(array_merge([
        'id' => random_int(1, PHP_INT_MAX),
        'guid' => (string) Str::uuid(),
        'word_en' => $attrs['word'],
        'meaning' => '',
        'language' => 'zh-Hans',
        'editor_id' => 0,
        'create_time' => time() * 1000,
        'modify_time' => time() * 1000,
    ], $attrs))->save();

    return $term;
}

/**
 * root 名下的系统 channel。
 */
function makeSystemChannel(string $name, string $type = 'translation', string $lang = 'en'): string
{
    $uid = makeChannel(config('mint.admin.root_uuid'), $name);
    Channel::where('uid', $uid)->update(['type' => $type, 'lang' => $lang]);

    return $uid;
}

beforeEach(function () {
    $this->owner = makeStudio('markup-owner');
    $this->channel = makeChannel($this->owner, 'markup channel');
});

describe('term', function () {
    it('finds the channel own term', function () {
        makeMarkupTerm(['word' => 'dhamma', 'meaning' => '法', 'other_meaning' => '正法', 'channal' => $this->channel, 'owner' => $this->owner]);

        $tree = markupDataTree('[[dhamma]]', ['channels' => [$this->channel]]);
        expect($tree[0]['props'])->toMatchArray([
            'word' => 'dhamma',
            'meaning' => '法',
            'meaning2' => '正法',
            'channel' => $this->channel,
            'parentChannelId' => $this->channel,
            'parentStudioId' => $this->owner,
        ]);
        expect($tree[0]['children'])->toBe([['type' => 'text', 'value' => '法(dhamma)']]);
    });

    it('falls back to the studio term in the same language before the language family', function () {
        makeMarkupTerm(['word' => 'sati', 'meaning' => '念-繁', 'language' => 'zh-Hant', 'owner' => $this->owner]);
        makeMarkupTerm(['word' => 'sati', 'meaning' => '念', 'language' => 'zh-Hans', 'owner' => $this->owner]);
        makeMarkupTerm(['word' => 'sati', 'meaning' => 'mindfulness', 'language' => 'en', 'owner' => $this->owner]);

        expect(markupDataTree('[[sati]]', ['channels' => [$this->channel]])[0]['props']['meaning'])->toBe('念');
    });

    it('falls back to the community term channel', function () {
        $community = makeSystemChannel('_community_term_zh-hans_');
        makeMarkupTerm(['word' => 'kusala', 'meaning' => '善', 'channal' => $community, 'owner' => config('mint.admin.root_uuid')]);

        $props = markupDataTree('{{term|kusala}}', ['channels' => [$this->channel]])[0]['props'];
        expect($props)->toMatchArray(['meaning' => '善', 'isCommunity' => true]);
    });

    it('filters by tag with word@tag and overrides the label with word#text', function () {
        makeMarkupTerm(['word' => 'sutta', 'meaning' => '经', 'tag' => 'a', 'channal' => $this->channel, 'owner' => $this->owner]);
        makeMarkupTerm(['word' => 'sutta', 'meaning' => '契经', 'tag' => 'b', 'channal' => $this->channel, 'owner' => $this->owner]);

        expect(markupDataTree('[[sutta@b]]', ['channels' => [$this->channel]])[0]['props']['meaning'])->toBe('契经');
        expect(markupDataTree('[[sutta@a#经文]]', ['channels' => [$this->channel]])[0]['props']['meaning'])->toBe('经文');
    });

    it('leaves the term note out of the tree but renders it, with its own templates, in markdown', function () {
        makeMarkupTerm(['word' => 'magga', 'meaning' => '道', 'note' => '见 [[nibbāna]]', 'channal' => $this->channel, 'owner' => $this->owner]);
        makeMarkupTerm(['word' => 'nibbāna', 'meaning' => '涅槃', 'channal' => $this->channel, 'owner' => $this->owner]);

        // 前端术语组件自己取词条详情，note 不随正文下发
        expect(markupDataTree('[[magga]]', ['channels' => [$this->channel]])[0]['props'])->not->toHaveKey('note');
        expect(markupDataRender('[[magga]]', Format::Markdown, ['channels' => [$this->channel], 'footnote' => true]))
            ->toBe("道(magga)[^1]\n\n[^1]: 见 涅槃(nibbāna)");
    });

    it('expands term notes only one level deep', function () {
        makeMarkupTerm(['word' => 'a', 'meaning' => '甲', 'note' => '甲注 [[b]]', 'channal' => $this->channel, 'owner' => $this->owner]);
        makeMarkupTerm(['word' => 'b', 'meaning' => '乙', 'note' => '乙注 [[c]]', 'channal' => $this->channel, 'owner' => $this->owner]);
        makeMarkupTerm(['word' => 'c', 'meaning' => '丙', 'channal' => $this->channel, 'owner' => $this->owner]);

        // 甲的 note 里的乙只出译名，不再展开乙注（否则词条互相引用会成倍膨胀）
        expect(markupDataRender('[[a]]', Format::Markdown, ['channels' => [$this->channel], 'footnote' => true]))
            ->toBe("甲(a)[^1]\n\n[^1]: 甲注 乙(b)");
    });

    it('renders terms per format', function () {
        makeMarkupTerm(['word' => 'dhamma', 'meaning' => '法', 'note' => '注', 'channal' => $this->channel, 'owner' => $this->owner]);
        $options = ['channels' => [$this->channel]];

        expect(markupDataRender('[[dhamma]]', Format::Text, $options))->toBe('法');
        expect(markupDataRender('[[dhamma]]', Format::Html, $options))
            ->toMatch('/^<span><span class="term-ref" data-id="[0-9a-f-]{36}" data-term="dhamma">法<\/span><\/span>$/');
        expect(markupDataRender('[[dhamma]]与[[dhamma]]', Format::Markdown, $options + ['footnote' => true]))
            ->toBe("法(dhamma)[^1]与法\n\n[^1]: 注");
    });

    it('looks up all terms of a document with a constant number of queries', function () {
        $words = [];
        foreach (range(1, 30) as $i) {
            makeMarkupTerm(['word' => "w{$i}", 'meaning' => "义{$i}", 'channal' => $this->channel, 'owner' => $this->owner]);
            $words[] = "[[w{$i}]]";
        }
        makeSystemChannel('_community_term_zh-hans_');

        DB::enableQueryLog();
        $tree = markupDataTree(implode(' ', $words), ['channels' => [$this->channel]]);
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        expect(collect($tree)->where('type', 'tpl')->pluck('props.meaning')->all())->toHaveCount(30)->toContain('义30');
        // channel、本 channel 术语、studio 术语、社区 channel、社区术语
        expect($queries)->toBeLessThanOrEqual(6);
    });
});

describe('sent', function () {
    beforeEach(function () {
        $this->pali = makeSystemChannel('_System_Pali_VRI_', 'original', 'pi');
        makeSentence($this->pali, 999, 1, 1, '<span>Evaṃ me sutaṃ</span>');
        makeSentence($this->channel, 999, 1, 1, '如是我闻[[dhamma]]');
        makeMarkupTerm(['word' => 'dhamma', 'meaning' => '法', 'channal' => $this->channel, 'owner' => $this->owner]);
    });

    it('renders origin and translation bodies with the row channel', function () {
        $props = markupDataTree('{{999-1-1-1}}', ['channels' => [$this->channel]])[0]['props'];

        // 只给渲染要用的：坐标在 id 里，原文 / 译文已分组（不给 channel.type），没有义注就不给 commentaries
        expect(array_keys($props))->toBe(['id', 'show', 'origin', 'translation']);
        expect($props)->toMatchArray(['id' => '999-1-1-1', 'show' => 'both']);
        expect($props['origin'][0]['channel'])->toBe(['id' => $this->pali, 'name' => '_System_Pali_VRI_', 'lang' => 'pi']);
        expect(array_keys($props['origin'][0]))->toBe(['id', 'channel', 'body']);
        expect($props['origin'][0]['body'][0])->toMatchArray(['type' => 'el', 'tag' => 'span']);
        expect($props['translation'][0]['body'][1]['name'])->toBe('term');
        expect($props['translation'][0]['body'][1]['props']['meaning'])->toBe('法');
        expect($props['translation'][0])->not->toHaveKey('content');
    });

    it('includes the source content in edit mode', function () {
        $wbw = makeSystemChannel('_System_Wbw_VRI_', 'wbw', 'pi');
        makeSentence($wbw, 999, 1, 1, 'wbw text');
        $tree = markupDataTree('{{999-1-1-1}}', ['channels' => [$this->channel], 'mode' => 'edit']);

        expect($tree[0]['name'])->toBe('sentedit');
        expect($tree[0]['props']['translation'][0]['content'])->toBe('如是我闻[[dhamma]]');
        expect($tree[0]['props']['translation'][0]['contentType'])->toBe('markdown');
        expect($tree[0]['props']['origin'][0]['channel']['id'])->toBe($wbw);
    });

    it('reads a single channel with id@channel', function () {
        $other = makeChannel($this->owner, 'other');
        makeSentence($other, 999, 1, 1, '另一译');

        $props = markupDataTree("{{999-1-1-1@{$other}}}", ['channels' => [$this->channel]])[0]['props'];
        expect(array_column(array_column($props['translation'], 'channel'), 'id'))->toBe([$other]);
    });

    it('renders sentences per format and honours text=', function () {
        $options = ['channels' => [$this->channel]];
        expect(markupDataRender('{{999-1-1-1}}', Format::Html, $options))
            ->toBe('<span><span class="sentence"><span class="origin"><span><span>Evaṃ me sutaṃ</span></span></span>'
                .'<span class="translation"><span>如是我闻<span class="term-ref" data-id="'
                .DhammaTerm::where('word', 'dhamma')->value('guid').'" data-term="dhamma">法</span></span></span></span></span>');
        expect(markupDataRender('{{sent|id=999-1-1-1|text=translation}}', Format::Text, $options))->toBe('如是我闻法');
        expect(markupDataRender('{{999-1-1-1}}', Format::Simple, $options))->toBe('Evaṃ me sutaṃ如是我闻法');
        expect(markupDataRender('{{999-1-1-1}}', Format::Markdown, $options + ['origin' => false]))->toBe('如是我闻法(dhamma)');
    });

    it('renders a markdown-e2 sentence with code instead of notes', function () {
        DB::table('sentences')->where('channel_uid', $this->channel)->update(['content' => '用 `code`', 'content_type' => 'markdown-e2']);

        $body = markupDataTree('{{999-1-1-1}}', ['channels' => [$this->channel]])[0]['props']['translation'][0]['body'];
        expect($body[1])->toMatchArray(['type' => 'el', 'tag' => 'code']);
    });

    it('renders sentences inside raw html blocks', function () {
        $tree = markupDataTree("<nt>\n{{999-1-1-1}}\n</nt>", ['channels' => [$this->channel]]);
        expect($tree[0]['attrs']['data-tag'])->toBe('nt');
        $sent = collect($tree[0]['children'])->firstWhere('type', 'tpl');
        expect($sent['props']['translation'][0]['body'][0]['value'])->toBe('如是我闻');
    });

    it('duplicates sentence-only paragraphs in paragraph mode', function () {
        $tree = markupDataTree("{{999-1-1-1}}\n\n正文", ['channels' => [$this->channel], 'paragraph' => true]);
        expect(array_column($tree, 'tag'))->toBe(['p', 'p', 'p']);
        expect($tree[0]['children'][0]['props']['show'])->toBe('origin');
        expect($tree[1]['children'][0]['props']['show'])->toBe('translation');
    });

    it('loads all sentences of a document in one query', function () {
        foreach (range(2, 20) as $start) {
            makeSentence($this->channel, 999, 1, $start, "句{$start}");
        }
        $source = implode('', array_map(fn ($start) => "{{999-1-{$start}-{$start}}}", range(1, 20)));

        DB::enableQueryLog();
        markupDataTree($source, ['channels' => [$this->channel]]);
        $sentenceQueries = collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], 'from "sentences"'))->count();
        DB::disableQueryLog();

        expect($sentenceQueries)->toBe(1);
    });

    it('stops nesting content that refers to itself', function () {
        DB::table('sentences')->where('channel_uid', $this->channel)->update(['content' => '自引{{999-1-1-1}}']);

        $tree = markupDataTree('{{999-1-1-1}}', ['channels' => [$this->channel]]);
        $json = json_encode($tree, JSON_UNESCAPED_UNICODE);
        // 文章 → 句子正文 → 句子正文，第 3 层不再展开
        expect(substr_count($json, '自引'))->toBe(2);
        expect($json)->toContain('"body":null');
    });

    it('lists referenced books with ref', function () {
        BookTitle::forceCreate(['book' => 999, 'paragraph' => 0, 'title' => 'dīghanikāya', 'sn' => 1]);

        $props = markupDataTree("{{999-1-1-1}}\n\n{{ref}}", ['channels' => [$this->channel]])[1]['children'][0]['props'];
        expect($props['pali'])->toBe([['sn' => 1, 'title' => 'dīghanikāya', 'copyright' => 'CSCD V4 VRI 2008']]);
        expect(markupDataRender("{{999-1-1-1}}\n\n{{ref}}", Format::Markdown, ['channels' => [$this->channel]]))
            ->toEndWith("[1] **Dīghanikāya** CSCD V4 VRI 2008\n\n");
    });
});

describe('templates reading other tables', function () {
    it('renders quote with the paragraph toc', function () {
        (new PaliText)->forceFill([
            'book' => 9001, 'paragraph' => 3, 'level' => 2, 'class' => '', 'toc' => 'Brahmajālasutta',
            'text' => '', 'html' => '', 'path' => json_encode([['title' => 'dīgha']]), 'uid' => (string) Str::uuid(),
        ])->save();

        expect(markupDataTree('{{quote|9001-3}}', ['channels' => [$this->channel]])[0]['props'])
            ->toBe(['paraId' => '9001-3', 'pali' => 'Brahmajālasutta']);
        expect(markupDataRender('{{quote|9001-3}}', Format::Text))->toBe('Brahmajālasutta');
        expect(markupDataRender('{{quote|9001-404}}', Format::Text))->toBe('9001-404');
    });

    it('renders qa with the discussion title', function () {
        $qa = Discussion::forceCreate([
            'res_type' => 'sentence', 'res_id' => (string) Str::uuid(), 'type' => 'qa',
            'title' => '为什么？', 'content' => 'body', 'editor_uid' => $this->owner,
        ]);

        $props = markupDataTree("{{qa|{$qa->id}}}")[0]['props'];
        expect($props)->toBe(['id' => $qa->id, 'title' => '为什么？', 'resType' => 'sentence']);
    });

    it('renders Tpl articles with mustache params', function () {
        (new Article)->forceFill([
            'id' => random_int(1, PHP_INT_MAX), 'uid' => (string) Str::uuid(), 'title' => 'Tpl:greet',
            'content' => 'Hello **{{1}}** from {{place}}', 'owner' => $this->owner, 'owner_id' => 0, 'editor_id' => 0,
            'create_time' => time() * 1000, 'modify_time' => time() * 1000,
        ])->save();

        $tree = markupDataTree('{{Tpl:greet|Ānanda|place=Sāvatthī}}');
        expect($tree[0])->toMatchArray(['name' => 'tpl', 'props' => ['name' => 'Tpl:greet']]);
        expect($tree[0]['children'][1])->toMatchArray(['tag' => 'strong']);
        expect(markupDataRender('{{Tpl:greet|Ānanda|place=Sāvatthī}}', Format::Text))->toBe('Hello Ānanda from Sāvatthī');
        expect(markupDataTree('{{Tpl:missing}}')[0]['children'])->toBe([]);
    });

    it('renders grammar lookups from the system grammar channel', function () {
        $grammar = makeSystemChannel('_System_Grammar_Term_en_');
        makeMarkupTerm(['word' => 'nom', 'meaning' => 'nominative', 'channal' => $grammar, 'owner' => config('mint.admin.root_uuid')]);

        $props = markupDataTree('{{g|nom}}', ['channels' => [$this->channel]])[0]['props'];
        expect($props['word'])->toBe('nom');
        expect($props['term'])->toMatchArray(['meaning' => 'nominative', 'channel' => $grammar]);
    });

    it('renders quote-link with a localized book name', function () {
        makeMarkupTerm(['word' => 'dīghanikāya', 'meaning' => '长部', 'tag' => ':quote:', 'channal' => $this->channel, 'owner' => $this->owner]);

        $tree = markupDataTree('{{ql|type=m|bookname=dīghanikāya|volume=1|page=23}}', ['channels' => [$this->channel]]);
        expect($tree[0]['name'])->toBe('quote-link');
        expect($tree[0]['props'])->toMatchArray(['type' => 'm', 'bookName' => 'dīghanikāya', 'volume' => 1, 'page' => '23']);
        expect($tree[0]['props'])->not->toHaveKey('bookNameLocal');
        expect($tree[0]['props']['term']['meaning'])->toBe('长部');
        expect(markupDataRender('{{ql|type=m|bookname=dīghanikāya|volume=1|page=23}}', Format::Text, ['channels' => [$this->channel]]))
            ->toBe('长部 1.23');
    });

    it('renders nissaya with the channel language', function () {
        Channel::where('uid', $this->channel)->update(['lang' => 'my']);

        expect(markupDataTree('{{nissaya|evaṃ|ဤသို့}}', ['channels' => [$this->channel]])[0]['props'])->toBe(['pali' => 'evaṃ', 'meaning' => ['ဤသို့']]);
        expect(markupDataRender('{{nissaya|evaṃ|ဤသို့}}', Format::Html, ['channels' => [$this->channel]]))
            ->toBe('<span><span lang="pi-Mymr">evaṃ</span>၊ <span lang="my">ဤသို့</span></span>');
    });
});

describe('ref', function () {
    beforeEach(function () {
        // book 998 收了两部作品：第 1 段起是 first work，第 10 段起是 second work；book 997 一部
        BookTitle::forceCreate(['book' => 998, 'paragraph' => 1, 'title' => 'first work', 'sn' => 1]);
        BookTitle::forceCreate(['book' => 998, 'paragraph' => 10, 'title' => 'second work', 'sn' => 2]);
        BookTitle::forceCreate(['book' => 997, 'paragraph' => 1, 'title' => 'third work', 'sn' => 3]);
    });

    $titles = function (array $tree): array {
        $find = function (array $nodes) use (&$find): ?array {
            foreach ($nodes as $node) {
                if (($node['name'] ?? null) === 'reference') {
                    return $node;
                }
                if (($found = $find($node['children'] ?? [])) !== null) {
                    return $found;
                }
            }

            return null;
        };

        return array_column($find($tree)['props']['pali'] ?? [], 'title');
    };

    it('collects references from para, quote, ql with book and para, and chapter articles', function () use ($titles) {
        $source = '{{para|id=998-12|title=t}} {{quote|997-3}} {{ql|type=m|book=998|para=2}} '
            .'{{article|type=chapter|book=998|paragraphs=15}} {{ql|type=m|bookname=x|volume=1|page=2}}{{ref}}';

        expect($titles(markupDataTree($source)))->toBe(['second work', 'third work', 'first work']);
    });

    it('picks the nearest title, counting a reference to the first paragraph of a work', function () use ($titles) {
        expect($titles(markupDataTree('{{para|id=998-10}}{{ref}}')))->toBe(['second work']);
        expect($titles(markupDataTree('{{para|id=998-9}}{{ref}}')))->toBe(['first work']);
        expect($titles(markupDataTree('{{para|id=998-0}}{{ref}}')))->toBe([]);
    });

    it('lists every reference wherever {{ref}} is placed', function () use ($titles) {
        expect($titles(markupDataTree("{{ref}}\n\n{{para|id=997-5}}\n\n{{998-12-1-2}}")))->toBe(['third work', 'second work']);
    });

    it('gives the list its own title, like {{toc}}', function () {
        $ref = fn (string $source) => collect(markupDataTree($source))->firstWhere('name', 'reference')
            ?? collect(markupDataTree($source))->pluck('children')->flatten(1)->firstWhere('name', 'reference');

        expect($ref('{{para|id=997-5}}{{ref}}')['props'])->toMatchArray(['title' => '参考文献']);
        expect($ref('{{para|id=997-5}}{{ref|title=引用书目}}')['props']['title'])->toBe('引用书目');
        expect($ref('{{para|id=997-5}}{{ref|title=}}')['props'])->not->toHaveKey('title');
        // 紧跟在手写的「## 参考文献」后面：不再显示第二个标题
        expect($ref("{{para|id=997-5}}\n\n## 参考文献\n\n{{ref}}")['props'])->not->toHaveKey('title');
        // 没有引用：整块不输出，也没有标题
        expect($ref('{{ref}}')['props'])->toBe(['pali' => []]);
        expect(markupDataRender('{{ref}}', Format::Html))->toBe('');
    });

    it('renders the title in html, text and markdown', function () {
        expect(markupDataRender('{{para|id=997-5}}{{ref}}', Format::Text))->toBe("参考文献\n[1] Third work CSCD V4 VRI 2008\n");
        expect(markupDataRender('{{para|id=997-5}}{{ref}}', Format::Markdown))->toBe("参考文献\n\n[1] **Third work** CSCD V4 VRI 2008\n\n");
        // html 的具体结构由页面决定（百科页另有写法），这里只看标题与条目都在、标题在前
        expect(markupDataRender('{{para|id=997-5}}{{ref}}', Format::Html))
            ->toMatch('/参考文献.*\[1\] Third work CSCD V4 VRI 2008/s');
    });

    it('counts references inside template parameters such as footnotes, once per title', function () use ($titles) {
        expect($titles(markupDataTree('{{n|见 {{quote|998-3}}}} {{para|id=998-4}} {{ref}}')))->toBe(['first work']);
    });
});
