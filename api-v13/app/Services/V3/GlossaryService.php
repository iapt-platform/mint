<?php

namespace App\Services\V3;

use App\Facades\RedisCache;
use App\Http\Resources\V3\GlossaryEntryResource;
use App\Models\DhammaTerm;
use App\Services\ChannelService as ChannelLookup;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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

    /**
     * 响应结构的版本，拼进 ETag。{@see GlossaryEntryResource} 的字段一改就加 1，
     * 否则浏览器带着旧 ETag 来会拿到 304、继续用旧结构的缓存。
     */
    private const SCHEMA_VERSION = 2;

    /** ETag 在 Redis 里的存活时间；本任务每天都会换新，过期了也只是让客户端多拉一次 */
    private const ETAG_TTL = 30 * 24 * 60 * 60;

    public function __construct(private readonly ChannelLookup $channels) {}

    /**
     * 某种词汇表的全部条目（按词排序），以及实际用到的语言。
     *
     * @return array{items: Collection<int, DhammaTerm>, lang: string}
     */
    public function entries(string $kind, string $lang): array
    {
        [$channel, $served] = $this->channelOf($kind, $lang);

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

    /**
     * 某种词汇表当前的 ETag；没有 ETag 的表（grammar）或表不存在时返回 null。
     *
     * 只有 community 有：它的 channel 只由 `upgrade:community.term` 维护，任务跑完调
     * {@see rotateEtag()} 换一个新 uuid，所以不用查整表、不用算 hash。有人绕过任务手工改了这个
     * channel，ETag 不会变，客户端要等下次任务跑完才看得到。grammar 是人工编辑的，暂不支持。
     */
    public function etag(string $kind, string $lang): ?string
    {
        if ($kind !== 'community') {
            return null;
        }
        [$channel] = $this->channelOf($kind, $lang);
        if ($channel === false) {
            return null;
        }

        // key 不存在（刚部署、Redis 清过、过期）就现生成一个
        $token = RedisCache::remember(self::etagKey($channel), self::ETAG_TTL, fn () => (string) Str::uuid());

        return $token.'.'.self::SCHEMA_VERSION;
    }

    /** 词汇表 channel 的内容变了：换一个新 ETag，客户端下次请求会拿到 200 与新数据 */
    public function rotateEtag(string $channel): void
    {
        RedisCache::put(self::etagKey($channel), (string) Str::uuid(), self::ETAG_TTL);
    }

    private static function etagKey(string $channel): string
    {
        return "/glossary/{$channel}/etag";
    }

    /**
     * 实际要读的 channel 与语言：该语言没有这张表时回退到英文（与 v2 相同）。
     *
     * @return array{0: string|false, 1: string}
     */
    private function channelOf(string $kind, string $lang): array
    {
        $prefix = self::CHANNEL_PREFIX[$kind];
        $channel = $this->channels->systemUid($prefix.strtolower($lang).'_');
        if ($channel !== false) {
            return [$channel, $lang];
        }

        return [$this->channels->systemUid($prefix.self::FALLBACK_LANG.'_'), self::FALLBACK_LANG];
    }
}
