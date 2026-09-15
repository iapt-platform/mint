<?php

namespace App\Services;

use App\Http\Api\ChannelApi;
use App\Http\Resources\TermResource;
use App\Models\DhammaTerm;
use App\Models\UserDict;
use App\Tools\Tools;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TermService
{
    // 术语表额外纳入的 user_dicts 词典 id（其 word 去除连字符与空格后加入）
    private const USER_DICT_IDS = [
        'b089de57-f146-4095-b886-057863728c43',
        '0bfd87ec-f3ac-49a2-985e-28388779078d',
        '7c7ee287-35ba-4cf3-b87b-30f1fa6e57c9',
        '4ac8a0d5-9c6f-4b9f-983d-84288d47f993',
    ];

    public function attachLocalName(array $categoryData, string $lang): array
    {
        $allNames = [];

        // 收集所有 name
        foreach ($categoryData as $item) {

            if (! empty($item['category']['name'])) {
                $allNames[] = $item['category']['name'];
            }

            foreach ($item['children'] as $child) {
                if (! empty($child['name'])) {
                    $allNames[] = $child['name'];
                }
            }
        }

        // 去重
        $allNames = array_values(array_unique($allNames));

        // 查词典
        $terms = $this->glossaryByLemma($allNames, $lang);

        // 构建映射
        $termMap = [];
        if ($terms) {
            foreach ($terms as $term) {
                $termMap[$term->word] = $term->meaning;
            }
        }

        // 回填
        foreach ($categoryData as &$item) {

            $name = $item['category']['name'] ?? null;
            $item['category']['local_name'] = $termMap[$name] ?? $name;

            foreach ($item['children'] as &$child) {
                $childName = $child['name'] ?? null;
                $child['local_name'] = $termMap[$childName] ?? $childName;
            }
        }

        unset($item, $child);

        return $categoryData;
    }

    public function glossaryByLemma(array $words, string $lang)
    {
        $localTermChannel = ChannelApi::getSysChannel(
            '_community_term_'.strtolower($lang).'_'
        );
        if (! $localTermChannel) {
            return null;
        }
        $result = DhammaTerm::select(['guid', 'word', 'tag', 'meaning', 'other_meaning'])
            ->whereIn('word', $words)
            ->where('channal', $localTermChannel)
            ->get();

        return $result;
    }

    public function getCommunityGlossary(string $lang)
    {
        $localTermChannel = ChannelApi::getSysChannel(
            '_community_term_'.strtolower($lang).'_',
            '_community_term_en_'
        );
        $result = DhammaTerm::select([
            'guid',
            'word',
            'tag',
            'meaning',
            'other_meaning',
        ])
            ->where('channal', $localTermChannel)
            ->get();

        return ['items' => $result, 'total' => count($result)];
    }

    public function getChannelGlossary(string $channelId)
    {
        $terms = DhammaTerm::whereNotNull('word')
            ->where('word', '!=', '')
            ->whereNotNull('word')
            ->where('channal', $channelId)
            ->select(['word', 'meaning', 'tag'])
            ->get();

        return $terms->toArray();
    }

    public function getGlossary()
    {
        // 1) dhamma_terms 全表 distinct 词条原形（不限 channel），排除 word 含空格的短语条目
        $dhammaWords = DhammaTerm::query()
            ->whereNotNull('word')
            ->where('word', '!=', '')
            ->where('word', 'not like', '% %')
            ->distinct()
            ->pluck('word')
            ->all();

        // 2) user_dicts 指定词典的词，去除连字符与空格后加入
        $userWords = UserDict::query()
            ->whereIn('dict_id', self::USER_DICT_IDS)
            ->whereNotNull('word')
            ->where('word', '!=', '')
            ->pluck('word')
            ->map(fn ($w) => str_replace(['-', ' '], '', (string) $w))
            ->all();

        // 合并 + NFC 归一（避免巴利文变音符 NFC/NFD 不一致）+ 去空 + 去重
        $terms = array_map(fn ($w) => $this->toNfc(trim((string) $w)), array_merge($dhammaWords, $userWords));
        $terms = array_values(array_unique(array_filter($terms, fn ($w) => $w !== '')));

        return $terms;
    }

    /**
     * Unicode 规范化为 NFC（巴利文变音符匹配需统一 NFC/NFD）。intl 不可用时原样返回。
     */
    private function toNfc(string $s): string
    {
        if (class_exists(\Normalizer::class)) {
            $normalized = \Normalizer::normalize($s, \Normalizer::FORM_C);
            if ($normalized !== false) {
                return $normalized;
            }
        }

        return $s;
    }

    public function getGrammarGlossary(string $lang)
    {
        $localTermChannel = ChannelApi::getSysChannel(
            '_System_Grammar_Term_'.strtolower($lang).'_',
            '_System_Grammar_Term_en_'
        );
        $result = DhammaTerm::select(['guid', 'word', 'tag', 'meaning', 'other_meaning'])
            ->where('channal', $localTermChannel)
            ->get();

        return ['items' => $result, 'total' => count($result)];
    }

    public function getRaw(string $id)
    {
        $result = DhammaTerm::find($id);

        return $result;
    }

    public function isCommunity(?string $channelId)
    {
        $channel = ChannelApi::getById($channelId);
        if (! $channel) {
            return false;
        }
        if (strpos($channel['name'], '_community_term_') === false) {
            return false;
        } else {
            return true;
        }
    }

    public function communityTerm(string $word, string $lang, string $format)
    {
        $localTermChannel = ChannelApi::getSysChannel(
            '_community_term_'.strtolower($lang).'_'
        );
        $result = DhammaTerm::where('word', $word)
            ->where('channal', $localTermChannel)
            ->first();
        if ($result) {
            $resource = new TermResource($result);
            $urlParam = ['format' => $format];
            $fakeRequest = Request::create('', 'GET', $urlParam);
            $termArray = $resource->toArray($fakeRequest);
            if ($result) {
                return $termArray;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function communityWiki(string $word, string $lang, string $format)
    {
        $localTermChannel = ChannelApi::getSysChannel(
            '_community_translation_'.strtolower($lang).'_'
        );
        $result = DhammaTerm::where('word', $word)
            ->where('channal', $localTermChannel)
            ->first();
        if ($result) {
            $resource = new TermResource($result);
            $urlParam = ['format' => $format];
            $fakeRequest = Request::create('', 'GET', $urlParam);
            $termArray = $resource->toArray($fakeRequest);
            if ($result) {
                return $termArray;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function communityTerms(string $lang)
    {
        $localTermChannel = ChannelApi::getSysChannel(
            '_community_term_'.strtolower($lang).'_'
        );
        $result = DhammaTerm::where('channal', $localTermChannel)
            ->whereNotNull('note')
            ->where('note', '<>', '')
            ->take(10)
            ->orderBy('updated_at', 'desc')
            ->get();

        return [
            'data' => TermResource::collection($result),
            'count' => 10,
        ];
    }

    public function find(string $id, string $format): ?array
    {
        $result = DhammaTerm::find($id);
        $resource = new TermResource($result);
        $urlParam = ['format' => $format];
        $fakeRequest = Request::create('', 'GET', $urlParam);
        $termArray = $resource->toArray($fakeRequest);
        if ($result) {
            return $termArray;
        } else {
            return null;
        }
    }

    public function update(string $id, array $data)
    {
        DhammaTerm::where('guid', $id)->update($data);
    }

    /**
     * @param array{
     *     word: string,
     *     tag: string,
     *     channal: string,
     *     meaning: string,
     *     other_meaning: string|null,
     *     note: string|null,
     *     editor_id: int,
     * } $data
     * @return string 返回记录的 id
     */
    public function updateOrCreateByWord(array $data): string
    {
        $now = time();

        $channelInfo = ChannelApi::getById($data['channel_id']);

        // 先查询是否存在
        $term = DhammaTerm::where('word', $data['word'])
            ->where('tag', $data['tag'] ?? null)
            ->where('channal', $data['channel_id'])
            ->first();

        if ($term) {
            // 已存在，直接更新
            $term->update([
                'meaning' => $data['meaning'],
                'other_meaning' => $data['other_meaning'] ?? null,
                'note' => $data['note'] ?? null,
                'redirect' => $data['redirect'] ?? null,
                'editor_id' => $data['editor_id'],
                'modify_time' => $now,
            ]);
        } else {
            // 不存在，新建（一次性写入所有字段）
            $term = new DhammaTerm;
            $term->id = app('snowflake')->id();
            $term->guid = (string) Str::uuid();
            $term->word = $data['word'];
            $term->tag = $data['tag'] ?? null;
            $term->channal = $data['channel_id'];
            $term->meaning = $data['meaning'];
            $term->other_meaning = $data['other_meaning'] ?? null;
            $term->note = $data['note'] ?? null;
            $term->redirect = $data['redirect'] ?? null;
            $term->editor_id = $data['editor_id'];  // 注意：需传入 int 类型的 editor id
            $term->owner = $channelInfo['studio_id'];
            $term->word_en = Tools::getWordEn($data['word']);
            $term->language = $channelInfo['lang'] ?? 'zh-Hans';
            $term->create_time = $now;
            $term->modify_time = $now;
            $term->save();
        }

        return $term->guid;
    }

    /**
     * 按 channel + word 查找术语；不存在则新建一条空白词条（释义留待后续填充）。
     *
     * @return DhammaTerm 既有或新建的术语记录
     */
    public function findOrCreate(string $channelId, string $word): DhammaTerm
    {
        $term = DhammaTerm::where('word', $word)
            ->where('channal', $channelId)
            ->first();

        if ($term) {
            return $term;
        }

        $channelInfo = ChannelApi::getById($channelId);
        $now = time();

        $term = new DhammaTerm;
        $term->id = app('snowflake')->id();
        $term->guid = (string) Str::uuid();
        $term->word = $word;
        $term->meaning = $word;
        $term->channal = $channelId;
        $term->word_en = Tools::getWordEn($word);
        $term->owner = $channelInfo['studio_id'] ?? null;
        $term->language = $channelInfo['lang'] ?? 'en';
        $term->editor_id = 0;
        $term->create_time = $now;
        $term->modify_time = $now;
        $term->save();

        return $term;
    }
}
