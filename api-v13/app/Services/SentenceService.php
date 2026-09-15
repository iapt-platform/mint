<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\Sentence;
use App\Models\SentHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SentenceService
{
    protected $timeOut = 30;

    public function save(array $data): Sentence
    {
        $row = Sentence::firstOrNew([
            'book_id' => $data['book_id'],
            'paragraph' => $data['paragraph'],
            'word_start' => $data['word_start'],
            'word_end' => $data['word_end'],
            'channel_uid' => $data['channel_uid'],
        ], [
            'id' => app('snowflake')->id(),
            'uid' => Str::uuid(),
        ]);
        $row->content = $data['content'];
        if (isset($data['content_type']) && ! empty($data['content_type'])) {
            $row->content_type = $data['content_type'];
        }
        $row->strlen = mb_strlen($data['content'], 'UTF-8');
        $lang = Channel::where('uid', $data['channel_uid'])->value('lang');
        $row->language = $lang;
        $row->status = $data['status'] ?? 10;
        if (isset($data['copy'])) {
            // 复制句子，保留原作者信息
            $row->editor_uid = $data['editor_uid'];
            $row->acceptor_uid = $data['acceptor_uid'];
            $row->pr_edit_at = $data['updated_at'];
            if (isset($data['fork_from'])) {
                $row->fork_at = now();
            }
        } else {
            $row->editor_uid = $data['editor_uid'];
            $row->acceptor_uid = null;
            $row->pr_edit_at = null;
        }
        $row->create_time = time() * 1000;
        $row->modify_time = time() * 1000;
        $row->save();

        return $row;
    }

    /**
     * 保存句子并记录一条历史。
     * 在 save() 基础上，用持久化后的句子 uid、编辑者、内容写入 SentHistory；
     * acceptor_uid / fork_from / pr_from 可选（用于复制、fork、PR 接受等场景）。
     */
    public function saveWithHistory(array $data): Sentence
    {
        $row = $this->save($data);
        $this->saveHistory(
            $row->uid,
            $row->editor_uid,
            $row->content,
            $data['acceptor_uid'] ?? null,
            $data['fork_from'] ?? null,
            $data['pr_from'] ?? null,
        );

        return $row;
    }

    /**
     * 写入一条句子编辑历史。
     *
     * @param  string  $uid  句子 uid
     * @param  string  $editor  编辑者 user_uid
     * @param  string  $content  本次内容
     * @param  string|null  $user_uid  接受者 user_uid（fork / pr 场景填写）
     * @param  string|null  $fork_from  fork 来源
     * @param  string|null  $pr_from  pr 来源
     */
    public function saveHistory($uid, $editor, $content, $user_uid = null, $fork_from = null, $pr_from = null): void
    {
        $newHis = new SentHistory;
        $newHis->id = app('snowflake')->id();
        $newHis->sent_uid = $uid;
        $newHis->user_uid = $editor;
        if (empty($content)) {
            $newHis->content = '';
        } else {
            $newHis->content = $content;
        }
        if ($fork_from) {
            $newHis->fork_from = $fork_from;
            $newHis->accepter_uid = $user_uid;
        }
        if ($pr_from) {
            $newHis->pr_from = $pr_from;
            $newHis->accepter_uid = $user_uid;
        }
        $newHis->create_time = time() * 1000;
        $newHis->save();
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
    public function saveRpc(string $endpoint, array $sentence, string $editorToken)
    {
        $url = $endpoint;

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
            throw new DatabaseException('sentence 数据库写入错误');
        }
        $count = $response->json()['data']['count'];

        return $count;
    }
}
