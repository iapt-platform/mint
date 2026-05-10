<?php

namespace App\Services;

use App\Models\Sentence;
use App\Models\SentHistory;
use App\Models\Channel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

use Illuminate\Support\Str;

class SentenceService
{
    protected $timeOut = 30;

    public function save(array $data)
    {
        $row = Sentence::firstOrNew([
            "book_id" => $data['book_id'],
            "paragraph" => $data['paragraph'],
            "word_start" => $data['word_start'],
            "word_end" => $data['word_end'],
            "channel_uid" => $data['channel_uid'],
        ], [
            "id" => app('snowflake')->id(),
            "uid" => Str::uuid(),
        ]);
        $row->content = $data['content'];
        if (isset($data['content_type']) && !empty($data['content_type'])) {
            $row->content_type = $data['content_type'];
        }
        $row->strlen = mb_strlen($data['content'], "UTF-8");
        $lang = Channel::where('uid', $data['channel_uid'])->value('lang');
        $row->language = $lang;
        $row->status = $data['status'] ?? 10;
        if (isset($data['copy'])) {
            //复制句子，保留原作者信息
            $row->editor_uid = $data["editor_uid"];
            $row->acceptor_uid = $data["acceptor_uid"];
            $row->pr_edit_at = $data["updated_at"];
            if (isset($data['fork_from'])) {
                $row->fork_at = now();
            }
        } else {
            $row->editor_uid = $data["editor_uid"];
            $row->acceptor_uid = null;
            $row->pr_edit_at = null;
        }
        $row->create_time = time() * 1000;
        $row->modify_time = time() * 1000;
        $row->save();
    }

    /**
     *                'sentence' => [
                    'book_id' => $sentence['id'][0],
                    'paragraph' => $sentence['id'][1],
                    'word_start' => $sentence['id'][2],
                    'word_end' => $sentence['id'][3],
                    'channel_uid' => $channelId,
                    'content' => $prompt,
                    'content_type' => 'markdown',
                    'access_token' => $sentChannelInfo[1] ?? $params['token'],
                ],
     */
    public function saveRpc(array $sentence, string $editorToken)
    {
        $url = config('app.url') . '/api/v2/sentence';

        Log::info(" sentence update {$url}");
        $response = Http::timeout($this->timeOut)
            ->withToken($editorToken)
            ->post($url, [
                'sentences' => [$sentence],
            ]);
        if ($response->failed()) {
            Log::error(' sentence update failed', [
                'url' => $url,
                'data' => $response->json(),
            ]);
            throw new DatabaseException("sentence 数据库写入错误");
        }
        $count = $response->json()['data']['count'];
        return $count;
    }
}
