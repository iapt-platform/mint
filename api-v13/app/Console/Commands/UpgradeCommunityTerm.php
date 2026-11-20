<?php

namespace App\Console\Commands;

use App\Models\Channel;
use App\Models\DhammaTerm;
use App\Models\Sentence;
use App\Models\UserOperationDaily;
use App\Services\ChannelService;
use App\Services\V3\GlossaryService;
use App\Tools\Tools;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class UpgradeCommunityTerm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:community.term {lang} {word?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '由各用户的术语生成社区术语表（_community_term_{lang}_）：每个 word + tag 一条，取最常见的译名与得分最高的注释';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(ChannelService $channels, GlossaryService $glossaries)
    {
        if (Tools::isStop()) {
            return 0;
        }
        $lang = strtolower($this->argument('lang'));
        $langFamily = explode('-', $lang)[0];
        $localTerm = $channels->systemUid("_community_term_{$lang}_");
        if (! $localTerm) {
            return 1;
        }

        $channelId = $channels->systemUid('_System_Pali_VRI_');
        if ($channelId === false) {
            $this->error('no channel');

            return 1;
        }
        $langs = [$this->argument('lang'), $lang, $langFamily];
        $wordArg = $this->argument('word');

        // 来源：该语言的全部术语，但不含社区 channel 自己——
        // 否则社区里的每一行都会作为来源把自己续命，坏行永远清不掉。
        $sources = fn () => DhammaTerm::query()
            ->whereIn('language', $langs)
            ->where(fn ($q) => $q->whereNull('channal')->orWhere('channal', '<>', $localTerm));

        // 要生成的 (word, tag)。tag 的 NULL、''、纯空白都算「无 tag」，归成一组。
        $pairs = $sources()
            ->select(['word', 'tag'])
            ->whereNotNull('word')
            ->where('word', '<>', '')
            ->distinct()
            ->when($wordArg, fn ($q) => $q->where('word', $wordArg))
            ->get()
            ->map(fn ($row) => (object) ['word' => $row->word, 'tag' => self::normalizeTag($row->tag)])
            ->unique(fn ($row) => self::pairKey($row->word, $row->tag))
            ->values();

        if ($pairs->isEmpty()) {
            // 来源查空了多半是出了问题，不要据此清空整个社区 channel
            $this->error('no source terms');

            return 1;
        }

        // 社区 channel 里现有的行，按同样的口径分组；处理一组就取走一组，最后剩下的都是该删的
        $existing = DhammaTerm::where('channal', $localTerm)
            ->when($wordArg, fn ($q) => $q->where('word', $wordArg))
            ->orderBy('create_time')
            ->orderBy('guid')
            ->get()
            ->groupBy(fn ($row) => self::pairKey($row->word, self::normalizeTag($row->tag)));

        $aiChannels = Channel::where('source_type', 'ai')->pluck('uid')->flip();

        $total = $pairs->count();
        foreach ($pairs as $index => $word) {
            /**
             * 最优算法
             * 1. 找到最常见的意思
             * 2. 找到分数最高的
             */
            $bestNote = '';
            $allTerm = $sources()
                ->where('word', $word->word)
                ->get()
                ->filter(fn ($term) => self::normalizeTag($term->tag) === $word->tag)
                ->values();
            $score = [];
            // $term_exp = [];
            foreach ($allTerm as $key => $term) {
                // AI 生成的 channel（source_type=ai）里的注释不参与评选；译名统计仍算它们
                if ($term->channal !== null && isset($aiChannels[$term->channal])) {
                    continue;
                }
                // 获取经验值
                $exp = UserOperationDaily::where('user_id', $term->editor_id)
                    ->where('date_int', '<=', date_timestamp_get(date_create($term->updated_at)) * 1000)
                    ->sum('duration');
                $iExp = (int) ($exp / 1000);
                $noteStrLen = $term->note ? mb_strlen($term->note, 'UTF-8') : 0;
                $paliStrLen = 0;
                $tranStrLen = 0;
                $noteWithoutPali = '';
                if ($term->note && ! empty(trim($term->note))) {
                    // 计算note得分
                    // 查找句子模版
                    $pattern = "/\{\{[0-9].+?\}\}/";
                    // 获取去掉句子模版的剩余部分
                    $noteWithoutPali = preg_replace($pattern, '', $term->note);
                    $sentences = [];
                    $iSent = preg_match_all($pattern, $term->note, $sentences);
                    if ($iSent > 0) {
                        foreach ($sentences[0] as $sentence) {
                            $sentId = explode('-', trim($sentence, '{}'));
                            if (count($sentId) === 4) {
                                $hasTran = Sentence::where('book_id', $sentId[0])
                                    ->where('paragraph', $sentId[1])
                                    ->where('word_start', $sentId[2])
                                    ->where('word_end', $sentId[3])
                                    ->exists();

                                $sentLen = Sentence::where('book_id', $sentId[0])
                                    ->where('paragraph', $sentId[1])
                                    ->where('word_start', $sentId[2])
                                    ->where('word_end', $sentId[3])
                                    ->where('channel_uid', $channelId)
                                    ->value('strlen');
                                if ($sentLen) {
                                    $paliStrLen += $sentLen;
                                    if ($hasTran) {
                                        $tranStrLen += $sentLen;
                                    }
                                }
                            }
                        }
                    }
                }
                // 计算该术语note的总得分
                $score["{$key}"] = $iExp * $noteStrLen;
                // $term_exp["{$key}"] = $iExp;
                // $updated_time["{$key}"] = $term->updated_at 先提取，具体如何使用待定

            }

            // 需要过滤掉system_term的数量，把count(*)替换为经验值加合作为基础得分，（基础经验得分之和转化为标准活动周，再加上最近的更新时间，为最终得分）
            // FIXME: 只按 word 统计、不看 tag，同词不同义项会被写成同一个译名
            $hotMeaning = $sources()
                ->selectRaw('meaning,count(*) as co')
                ->where('word', $word->word)
                ->groupBy('meaning')
                ->orderBy('co', 'desc')
                ->first();
            if ($hotMeaning) {
                $bestNote = '';
                if (count($score) > 0) {
                    arsort($score);
                    $bestNote = $allTerm[(int) key($score)]->note;
                }

                // 同一组若有多行（历史上 NULL / '' / 空白 tag 各建了一行），留最早建的那行，其余删掉
                $rows = $existing->pull(self::pairKey($word->word, $word->tag)) ?? collect();
                $term = $rows->shift() ?? new DhammaTerm([
                    'id' => app('snowflake')->id(),
                    'guid' => Str::uuid(),
                    'word' => $word->word,
                    'word_en' => Tools::getWordEn($word->word),
                    'meaning' => '',
                    'channal' => $localTerm,
                    'language' => $this->argument('lang'),
                    'owner' => config('mint.admin.root_uuid'),
                    'editor_id' => 0,
                    'create_time' => time() * 1000,
                ]);
                $rows->each->delete();

                $term->tag = $word->tag;
                $term->meaning = $hotMeaning->meaning;
                $term->note = $bestNote;
                $term->modify_time = time() * 1000;
                $term->save();
            }
            $done = $index + 1;
            if ($done % 100 === 0 || $done === $total) {
                $this->info("{$done}/{$total}");
            }
        }

        // 没有来源支撑的行（来源术语已删，或历史重复行）：这个 channel 只由本任务维护，一并删除（软删）
        $stale = $existing->flatten();
        $stale->each->delete();
        $this->info("done: {$total} terms, {$stale->count()} stale rows removed");

        // 表可能变了：换 ETag，客户端（/v3/glossaries/community）下次请求拿新数据
        $glossaries->rotateEtag($localTerm);

        return 0;
    }

    /** tag 的 NULL、''、纯空白都当作「无 tag」（null） */
    private static function normalizeTag(?string $tag): ?string
    {
        $tag = trim((string) $tag);

        return $tag === '' ? null : $tag;
    }

    private static function pairKey(?string $word, ?string $tag): string
    {
        return ($word ?? '')."\0".($tag ?? '');
    }
}
