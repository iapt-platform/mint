<?php

namespace App\Services\V3;

use App\Models\DhammaTerm;
use App\Services\ChannelService as ChannelLookup;
use Illuminate\Support\Collection;

/**
 * 术语词汇表（只读）：语法术语表与社区译名表，客户端启动时整表拉取后本地查。
 *
 * 取代 v2 `term-vocabulary`（`TermVocabularyController`）。数据就是两个系统 channel 里的术语：
 * - grammar：`_System_Grammar_Term_{lang}_`
 * - community：`_community_term_{lang}_`
 * 该语言的 channel 不存在时回退到英文（与 v2 相同）。注意它与批注的 `glossary` 类型（外挂术语）不是一回事。
 */
class GlossaryService
{
    public const KINDS = ['grammar', 'community'];

    private const CHANNEL_PREFIX = [
        'grammar' => '_System_Grammar_Term_',
        'community' => '_community_term_',
    ];

    private const FALLBACK_LANG = 'en';

    public function __construct(private readonly ChannelLookup $channels) {}

    /**
     * 某种词汇表的全部条目（按词排序），以及实际用到的语言。
     *
     * @return array{items: Collection<int, DhammaTerm>, lang: string}
     */
    public function entries(string $kind, string $lang): array
    {
        $prefix = self::CHANNEL_PREFIX[$kind];
        $served = $lang;
        $channel = $this->channels->systemUid($prefix.strtolower($lang).'_');
        if ($channel === false) {
            $served = self::FALLBACK_LANG;
            $channel = $this->channels->systemUid($prefix.self::FALLBACK_LANG.'_');
        }

        $items = $channel === false
            ? collect()
            : DhammaTerm::query()
                ->where('channal', $channel)
                ->whereNotNull('word')
                ->where('word', '<>', '')
                ->orderBy('word')
                ->orderBy('guid')
                ->get(['guid', 'word', 'tag', 'meaning', 'other_meaning']);

        return ['items' => $items, 'lang' => $served];
    }
}
