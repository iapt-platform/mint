<?php

namespace App\Services;

use App\Models\Sentence;
use App\Models\SentHistory;
use Illuminate\Support\Str;

class SentenceService
{
    public function save($data)
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
        $row->language = $data['lang'];
        $row->status = $data['status'];
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
}
