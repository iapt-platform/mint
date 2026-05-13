<?php

namespace App\Services;

use App\Models\DhammaTerm;
use App\Http\Api\ChannelApi;
use App\Http\Resources\TermResource;
use Illuminate\Http\Request;

use App\Tools\Tools;



class TermService
{
    public function attachLocalName(array $categoryData, string $lang): array
    {
        $allNames = [];

        // 收集所有 name
        foreach ($categoryData as $item) {

            if (!empty($item['category']['name'])) {
                $allNames[] = $item['category']['name'];
            }

            foreach ($item['children'] as $child) {
                if (!empty($child['name'])) {
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
            "_community_term_" . strtolower($lang) . "_"
        );
        if (!$localTermChannel) {
            return null;
        }
        $result = DhammaTerm::select(['guid', 'word', 'tag', 'meaning', 'other_meaning'])
            ->whereIn('word', $words)
            ->where('channal', $localTermChannel)
            ->get();
        return $result;
    }
    public function getCommunityGlossary($lang)
    {
        $localTermChannel = ChannelApi::getSysChannel(
            "_community_term_" . strtolower($lang) . "_",
            "_community_term_en_"
        );
        $result = DhammaTerm::select(['guid', 'word', 'tag', 'meaning', 'other_meaning'])
            ->where('channal', $localTermChannel)
            ->get();
        return ['items' => $result, 'total' => count($result)];
    }
    public function getGrammarGlossary($lang)
    {
        $localTermChannel = ChannelApi::getSysChannel(
            "_System_Grammar_Term_" . strtolower($lang) . "_",
            "_System_Grammar_Term_en_"
        );
        $result = DhammaTerm::select(['word', 'tag', 'meaning', 'other_meaning'])
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
        if (!$channel) {
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
            "_community_term_" . strtolower($lang) . "_"
        );
        $result = DhammaTerm::where('word', $word)
            ->where('channal', $localTermChannel)
            ->first();
        if ($result) {
            $resource = new TermResource($result);
            $urlParam = ['format' => $format];
            $fakeRequest = Request::create('', 'GET', $urlParam);
            $termArray    = $resource->toArray($fakeRequest);
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
            "_community_translation_" . strtolower($lang) . "_"
        );
        $result = DhammaTerm::where('word', $word)
            ->where('channal', $localTermChannel)
            ->first();
        if ($result) {
            $resource = new TermResource($result);
            $urlParam = ['format' => $format];
            $fakeRequest = Request::create('', 'GET', $urlParam);
            $termArray    = $resource->toArray($fakeRequest);
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
            "_community_term_" . strtolower($lang) . "_"
        );
        $result = DhammaTerm::where('channal', $localTermChannel)
            ->whereNotNull('note')
            ->where('note', '<>', '')
            ->take(10)
            ->orderBy('updated_at', 'desc')
            ->get();
        return [
            "data" => TermResource::collection($result),
            "count" => 10
        ];
    }

    public function find(string $id, string $format): ?array
    {
        $result = DhammaTerm::find($id);
        $resource = new TermResource($result);
        $urlParam = ['format' => $format];
        $fakeRequest = Request::create('', 'GET', $urlParam);
        $termArray    = $resource->toArray($fakeRequest);
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
                'meaning'       => $data['meaning'],
                'other_meaning' => $data['other_meaning'] ?? null,
                'note'          => $data['note'] ?? null,
                'redirect'      => $data['redirect'] ?? null,
                'editor_id'     => $data['editor_id'],
                'modify_time'   => $now,
            ]);
        } else {
            // 不存在，新建（一次性写入所有字段）
            $term = new DhammaTerm();
            $term->id           = app('snowflake')->id();
            $term->guid         = (string) \Illuminate\Support\Str::uuid();
            $term->word         = $data['word'];
            $term->tag          = $data['tag'] ?? null;
            $term->channal      = $data['channel_id'];
            $term->meaning      = $data['meaning'];
            $term->other_meaning = $data['other_meaning'] ?? null;
            $term->note         = $data['note'] ?? null;
            $term->redirect     = $data['redirect'] ?? null;
            $term->editor_id    = $data['editor_id'];  // 注意：需传入 int 类型的 editor id
            $term->owner        = $channelInfo['studio_id'];
            $term->word_en      = Tools::getWordEn($data['word']);
            $term->language     = $channelInfo['lang'] ?? 'zh-Hans';
            $term->create_time  = $now;
            $term->modify_time  = $now;
            $term->save();
        }

        return $term->guid;
    }
}
