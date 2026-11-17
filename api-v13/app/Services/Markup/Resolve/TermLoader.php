<?php

namespace App\Services\Markup\Resolve;

use App\Models\DhammaTerm;
use App\Services\ChannelService;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Ir\Scope;
use Illuminate\Support\Collection;

/**
 * 术语查询，口径同 v2 TemplateRender::getTermProps()：
 *
 * 1. 这个 channel 自己的术语（最新的一条）
 * 2. channel 拥有者名下、不属于任何 channel 的术语：语言完全一致优先，其次同语族
 * 3. 社区术语 channel `_community_term_zh-hans_`
 *
 * 没有 channel（或 channel 不存在）时不查，只回显词头。tag 为空表示不按 tag 过滤。
 * 术语的 note 只展开一层：note 里出现的术语只查译名，不再取它们自己的 note。
 * 每个 channel 三次查询，与术语个数无关。
 */
final class TermLoader extends Loader
{
    private const COMMUNITY_CHANNEL = '_community_term_zh-hans_';

    /** @var array<string, array{word: string, tag: string, channel: ?string, depth: int, inTermNote: bool}> */
    private array $pending = [];

    /** @var array<string, array{term: ?DhammaTerm, community: bool, channel: ?string, studio: ?string, note: ?Document}> */
    private array $results = [];

    public function __construct(private readonly ChannelService $channelService) {}

    public function request(string $word, string $tag, ?string $channel, Scope $scope): string
    {
        // 层数进 key：note 里再引用同一个术语时，每层各有自己的结果，到最大层数为止（否则渲染时无限递归）
        $key = implode("\x1F", [$word, $tag, $channel ?? '', $scope->depth, (int) $scope->inTermNote]);
        if (! isset($this->results[$key])) {
            $this->pending[$key] = ['word' => $word, 'tag' => $tag, 'channel' => $channel, 'depth' => $scope->depth, 'inTermNote' => $scope->inTermNote];
        }

        return $key;
    }

    /**
     * @return array{term: ?DhammaTerm, community: bool, channel: ?string, studio: ?string, note: ?Document}
     */
    public function result(string $key): array
    {
        return $this->results[$key] ?? ['term' => null, 'community' => false, 'channel' => null, 'studio' => null, 'note' => null];
    }

    public function hasPending(): bool
    {
        return $this->pending !== [];
    }

    public function load(): void
    {
        $batch = $this->pending;
        $this->pending = [];

        $byChannel = [];
        foreach ($batch as $key => $request) {
            $byChannel[$request['channel'] ?? ''][$key] = $request;
        }

        $community = null;
        foreach ($byChannel as $channelUid => $requests) {
            $channel = $this->resolver->channels()->get($channelUid === '' ? null : $channelUid);
            if ($channel === null) {
                // 没给 channel：不查。给了但 channel 已不存在（被删）：只查社区术语（v2 口径）
                $community = $channelUid === '' ? null : ($community ?? $this->communityTerms(array_column($batch, 'word')));
                foreach ($requests as $key => $request) {
                    $term = $community === null ? null : $this->first($community, $request);
                    $this->results[$key] = [
                        'term' => $term,
                        'community' => $term !== null,
                        'channel' => $request['channel'],
                        'studio' => null,
                        'note' => $request['inTermNote'] ? null : $this->note($term, $channelUid, $request['depth']),
                    ];
                }

                continue;
            }
            $words = array_values(array_unique(array_column($requests, 'word')));
            $own = DhammaTerm::whereIn('word', $words)->where('channal', $channelUid)
                ->orderByDesc('updated_at')->get();
            $studio = DhammaTerm::whereIn('word', $words)->where('owner', $channel->owner_uid)
                ->where(fn ($query) => $query->whereNull('channal')->orWhere('channal', ''))
                ->orderByDesc('updated_at')->get();
            $community ??= $this->communityTerms(array_column($batch, 'word'));

            foreach ($requests as $key => $request) {
                $term = $this->first($own, $request);
                $isCommunity = false;
                if ($term === null) {
                    $term = $this->bestByLanguage($studio, $request, (string) $channel->lang);
                }
                if ($term === null) {
                    $term = $this->first($community, $request);
                    $isCommunity = $term !== null;
                }
                $this->results[$key] = [
                    'term' => $term,
                    'community' => $isCommunity,
                    'channel' => $channelUid,
                    'studio' => (string) $channel->owner_uid,
                    'note' => $request['inTermNote'] ? null : $this->note($term, $channelUid, $request['depth']),
                ];
            }
        }
    }

    /**
     * @param  list<string>  $words
     * @return Collection<int, DhammaTerm>
     */
    private function communityTerms(array $words): Collection
    {
        $uid = $this->channelService->systemUid(self::COMMUNITY_CHANNEL);
        if ($uid === false) {
            return new Collection;
        }

        return DhammaTerm::whereIn('word', array_values(array_unique($words)))->where('channal', $uid)
            ->orderByDesc('updated_at')->get();
    }

    /**
     * @param  Collection<int, DhammaTerm>  $terms
     * @param  array{word: string, tag: string}  $request
     */
    private function first(Collection $terms, array $request): ?DhammaTerm
    {
        return $terms->first(fn (DhammaTerm $term) => $this->matches($term, $request));
    }

    /**
     * 语言完全一致记 2 分、同语族记 1 分，取分高的；同分取最新的。
     *
     * @param  Collection<int, DhammaTerm>  $terms
     * @param  array{word: string, tag: string}  $request
     */
    private function bestByLanguage(Collection $terms, array $request, string $lang): ?DhammaTerm
    {
        $family = explode('-', $lang)[0];
        $best = null;
        $bestScore = 0;
        foreach ($terms as $term) {
            if (! $this->matches($term, $request)) {
                continue;
            }
            $language = (string) $term->language;
            $score = $language === $lang ? 2 : ($family !== '' && str_contains($language, $family) ? 1 : 0);
            if ($score > $bestScore) {
                [$best, $bestScore] = [$term, $score];
            }
        }

        return $best;
    }

    /**
     * @param  array{word: string, tag: string}  $request
     */
    private function matches(DhammaTerm $term, array $request): bool
    {
        return $term->word === $request['word'] && ($request['tag'] === '' || $term->tag === $request['tag']);
    }

    private function note(?DhammaTerm $term, string $channelUid, int $depth): ?Document
    {
        $note = (string) ($term?->note ?? '');
        $scope = new Scope(channels: [$channelUid], depth: $depth);
        if (trim($note) === '' || ! $scope->canNest()) {
            return null;
        }
        $nested = $scope->nested(contentType: $term->getAttribute('content_type') ?? 'markdown');
        // note 里的术语不再展开它们自己的 note：一层就够（v2 也只展开一层），否则词条正文互相引用会成倍膨胀
        $document = $this->resolver->parser->parse(
            $note,
            new Scope($nested->channels, $nested->dialect, $nested->depth, $nested->channelType, $nested->contentType, inTermNote: true),
        );
        $this->resolver->collect($document);

        return $document;
    }
}
