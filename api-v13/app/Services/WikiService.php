<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\DhammaTerm;
use App\Models\TermCategory;
use App\Services\Markup\Format;
use App\Services\Markup\MarkupService;
use App\Services\Markup\RenderOptions;
use App\Services\V3\ChannelAbilityService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * library 百科页（`/library/wiki/*`）的数据：按分类列词条、词条详情、相关词条、其它版本。
 *
 * 直接查库，不经 OpenSearch：质量取 dhamma_terms.quality，分类取 term_categories（v3 写入时从正文
 * `{{category|…}}` 抽出），摘要取 summary 或正文第一段。正文用新渲染器（{@see MarkupService}）出 HTML。
 *
 * 列表、相关词条、其它版本只列公开的：所在 channel 会被列出（status 30 或系统 channel，
 * {@see ChannelAbilityService::scopeListable()}），或 studio 级术语（不属于任何 channel）。
 * 按 guid 打开的词条页按「能访问」判（公开不列出的也能看，私有 403）。
 */
class WikiService
{
    /** 质量等级，数值越小越高；空按 pending */
    public const QUALITY_RANK = ['featured' => 1, 'standard' => 2, 'draft' => 3, 'pending' => 4];

    public function __construct(
        private readonly ChannelAbilityService $channels,
        private readonly MarkupService $markup,
    ) {}

    /**
     * 某组分类下的词条，质量不低于 $minQuality。同一个「词 + 释义」只留质量最高的一条。
     *
     * @param  list<string>  $categories
     * @return list<array{id: string, word: string, zh: string, quality: string}>
     */
    public function categoryEntries(array $categories, string $lang, string $minQuality): array
    {
        if ($categories === []) {
            return [];
        }
        $maxRank = self::QUALITY_RANK[$minQuality] ?? 4;
        $terms = $this->publicTerms($lang)
            ->whereIn('guid', TermCategory::query()->selectRaw('term_guid::text')->whereIn('category', $categories))
            ->orderBy('word')
            ->limit(1000)
            ->get(['guid', 'word', 'meaning', 'quality']);

        $unique = [];
        foreach ($terms as $term) {
            $quality = $term->quality ?? 'pending';
            $rank = self::QUALITY_RANK[$quality] ?? 4;
            if ($rank > $maxRank) {
                continue;
            }
            $key = mb_strtolower(trim($term->word).'|'.trim($term->meaning));
            if (isset($unique[$key]) && (self::QUALITY_RANK[$unique[$key]['quality']] ?? 4) <= $rank) {
                continue;
            }
            $unique[$key] = ['id' => (string) $term->guid, 'word' => (string) $term->word, 'zh' => (string) $term->meaning, 'quality' => $quality];
        }

        return array_values($unique);
    }

    /**
     * 按 guid 或词找要显示的词条。
     *
     * - guid：这一条，拿到链接就能看（公开不列出的也行），私有 channel 的 403
     * - 词：先找该语言社区 channel（`_community_translation_{lang}_`）里的，没有就取公开词条里质量最高、最近更新的
     *
     * @throws AuthorizationException guid 指向私有 channel 的术语
     */
    public function find(string $lang, string $wordOrId): ?DhammaTerm
    {
        if (Str::isUuid($wordOrId)) {
            return $this->accessibleTerm($wordOrId);
        }

        $community = Channel::where('name', '_community_translation_'.strtolower($lang).'_')
            ->where('owner_uid', config('mint.admin.root_uuid'))
            ->value('uid');
        if ($community) {
            $term = DhammaTerm::where('channal', $community)->where('word', $wordOrId)->first();
            if ($term) {
                return $term;
            }
        }

        return $this->publicTerms($lang)
            ->where('word', $wordOrId)
            ->orderByRaw("case coalesce(quality, 'pending') when 'featured' then 1 when 'standard' then 2 when 'draft' then 3 else 4 end")
            ->orderByDesc('updated_at')
            ->first();
    }

    /**
     * 按 guid 取一条：拿到链接就能看，与「会不会被列出」无关。
     *
     * channel 是公开（30）或公开不列出（20）、系统 channel、或 studio 级术语都能看；
     * library 没有登录态，私有 channel 的一律 403（{@see ChannelAbilityService::assertReadable()}），
     * 术语或它所在的 channel 不存在（含软删）返回 null（404）。
     *
     * @throws AuthorizationException
     */
    private function accessibleTerm(string $guid): ?DhammaTerm
    {
        $term = DhammaTerm::where('guid', $guid)->first();
        if ($term === null) {
            return null;
        }
        $channelUid = $term->channelUid();
        if ($channelUid !== null) {
            try {
                $this->channels->assertReadable($channelUid, null);
            } catch (ModelNotFoundException) {
                return null;
            }
        }

        return $term;
    }

    /**
     * 重定向的目标（`word` 或 `word@tag`）：先在同一 channel（studio 级的在同一 owner 的 studio 级术语）里找，
     * 找不到返回 null（读者停在原词条上）。
     */
    public function redirectTarget(DhammaTerm $term): ?DhammaTerm
    {
        $redirect = trim((string) $term->redirect);
        if ($redirect === '') {
            return null;
        }
        [$word, $tag] = array_pad(explode('@', $redirect, 2), 2, '');
        $query = DhammaTerm::query()->where('word', $word);
        $tag === ''
            ? $query->where(fn (Builder $q) => $q->whereNull('tag')->orWhere('tag', ''))
            : $query->where('tag', $tag);
        $term->channelUid() !== null
            ? $query->where('channal', $term->channelUid())
            : $query->studioLevel()->where('owner', $term->owner);

        $target = $query->first();

        return $target && $target->guid !== $term->guid ? $target : null;
    }

    /**
     * 正文 HTML：摘要 + 正文（摘要在前，不加分隔），用词条所在 channel 解析模板。
     *
     * 正文里的分类（`{{category|…}}`，渲染器输出 `span.tpl-category[data-category]`）换成指向
     * 该分类词条列表的链接，$lang 是当前百科页的语言。
     */
    public function contentHtml(DhammaTerm $term, string $lang): string
    {
        $source = trim(trim((string) $term->summary)."\n\n".(string) $term->note);
        $html = (string) $this->markup->render($source, new RenderOptions(
            format: Format::Html,
            channels: $term->channal ? [$term->channal] : [],
            contentType: $term->effectiveContentType(),
            lang: $term->language ?: 'zh-Hans',
        ));

        return (string) preg_replace_callback(
            '#<span class="tpl-category" data-category="([^"]*)">(.*?)</span>#u',
            function (array $m) use ($lang) {
                $name = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5);
                $url = route('library.wiki.index', ['lang' => $lang, 'category' => $name]);

                return '<a class="tpl-category" href="'.e($url).'">'.$m[2].'</a>';
            },
            $html,
        );
    }

    /**
     * 这条词条的分类（按正文里出现的顺序）。
     *
     * @return list<string>
     */
    public function categoriesOf(DhammaTerm $term): array
    {
        return TermCategory::where('term_guid', $term->guid)->orderBy('position')->pluck('category')->all();
    }

    /**
     * 与它共享分类的其它公开词条，最多 $limit 条，共享分类越多越靠前。
     *
     * @param  list<string>  $categories
     * @return list<array{word: string, zh: string, lang: string}>
     */
    public function related(DhammaTerm $term, array $categories, int $limit = 5): array
    {
        if ($categories === []) {
            return [];
        }
        $shared = TermCategory::query()
            ->selectRaw('term_guid::text as guid, count(*) as shared')
            ->whereIn('category', $categories)
            ->where('term_guid', '<>', $term->guid)
            ->groupBy('term_guid');

        return $this->publicTerms(null)
            ->joinSub($shared, 'shared', 'shared.guid', '=', 'dhamma_terms.guid')
            ->orderByDesc('shared.shared')
            ->orderBy('dhamma_terms.word')
            ->limit($limit)
            ->get(['dhamma_terms.word', 'dhamma_terms.meaning', 'dhamma_terms.language'])
            ->map(fn (DhammaTerm $t) => ['word' => (string) $t->word, 'zh' => (string) $t->meaning, 'lang' => (string) $t->language])
            ->all();
    }

    /**
     * 同一个词的其它公开版本（其它 channel、其它语言），最多 $limit 条。摘要是纯文本。
     *
     * @return list<array{type: string, id: string, lang: string, title: string, quality: string, category: string, snippet: string, updated: string}>
     */
    public function otherVersions(DhammaTerm $term, int $limit = 10): array
    {
        $versions = $this->publicTerms(null)
            ->where('word', $term->word)
            ->where('guid', '<>', $term->guid)
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get(['guid', 'word', 'meaning', 'language', 'quality', 'summary', 'note', 'content_type', 'channal', 'updated_at']);

        return $this->withSnippets($versions)->map(fn (DhammaTerm $t) => [
            'type' => 'term',
            'id' => (string) $t->guid,
            'lang' => (string) $t->language,
            'title' => (string) $t->meaning,
            'quality' => $t->quality ?? 'pending',
            'category' => '法义术语',
            'snippet' => (string) $t->snippet,
            'updated' => $t->updated_at?->toIso8601String() ?? '',
        ])->all();
    }

    /**
     * 公开的词条：所在 channel 会被列出，或 studio 级术语。$lang 给了就只要这种语言的。
     *
     * @return Builder<DhammaTerm>
     */
    private function publicTerms(?string $lang): Builder
    {
        $query = DhammaTerm::query()->where(function (Builder $q) {
            $q->studioLevel()->orWhereIn('channal', function ($sub) {
                $sub->selectRaw('uid::text')->from('channels')->whereNull('deleted_at');
                $this->channels->scopeListable($sub, null);
            });
        });
        if ($lang !== null) {
            $query->whereIn(DB::raw('lower(language)'), $this->languageVariants($lang));
        }

        return $query;
    }

    /**
     * 库里的语言写法不统一（zh-Hans / zh-hans / zh）。
     *
     * @return list<string>
     */
    private function languageVariants(string $lang): array
    {
        $lang = strtolower($lang);

        return match ($lang) {
            'zh-hans', 'zh-cn', 'zh' => ['zh-hans', 'zh-cn', 'zh'],
            'zh-hant', 'zh-tw' => ['zh-hant', 'zh-tw'],
            default => [$lang],
        };
    }

    /**
     * 给每条挂上纯文本摘要（`snippet`）：有 summary 用它，没有取正文第一段。整批渲染一次。
     *
     * @param  Collection<int, DhammaTerm>  $terms
     * @return Collection<int, DhammaTerm>
     */
    private function withSnippets(Collection $terms): Collection
    {
        $items = [];
        foreach ($terms as $term) {
            $summary = trim((string) $term->summary);
            $items[$term->guid] = [
                'source' => $summary !== '' ? $summary : (string) $term->note,
                'channels' => $term->channal ? [$term->channal] : [],
                'contentType' => $term->effectiveContentType(),
            ];
        }
        $texts = $items === [] ? [] : $this->markup->renderMany($items, new RenderOptions(format: Format::Text));

        return $terms->each(function (DhammaTerm $term) use ($texts) {
            $text = (string) ($texts[$term->guid] ?? '');
            $first = '';
            foreach (preg_split('/\n/u', $text) ?: [] as $line) {
                if (trim($line) !== '') {
                    $first = trim($line);
                    break;
                }
            }
            $term->snippet = Str::limit($first, 200);
        });
    }
}
