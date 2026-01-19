<?php

namespace App\Services;

use App\Models\ProgressChapter;
use App\Models\TagMap;

use Illuminate\Support\Facades\Log;

class ProgressChapterService
{
    protected $tags = null;
    protected $channelId = null;
    protected $channelType = null;
    protected $channelOwnerId = null;
    protected $minProgress = 0.01;
    public function setProgress($progress)
    {
        $this->minProgress = $progress;
        return $this;
    }
    public function setChannel($channelId)
    {
        $this->channelId = $channelId;
        return $this;
    }
    public function setChannelType($channelType)
    {
        $this->channelType = $channelType;
        return $this;
    }
    public function setChannelOwnerId($channelOwnerId)
    {
        $this->channelOwnerId = $channelOwnerId;
        return $this;
    }
    public function setTags($tags)
    {
        $this->tags = $tags;
        return $this;
    }
    public function get()
    {
        $tagCount = count($this->tags);
        $chapters = ProgressChapter::where('progress', '>', $this->minProgress)
            ->whereHas('channel', function ($query) {
                $query->where('owner_uid', $this->channelOwnerId);
            })->whereHas('tags', function ($query) {
                $query->whereIn('name', $this->tags);
            }, '=', $tagCount)->get();
        return $chapters;
    }
    public function getTags()
    {
        $tagCount = count($this->tags);
        $chapters = ProgressChapter::where('progress', '>', $this->minProgress)
            ->whereHas('channel', function ($query) {
                $query->where('owner_uid', $this->channelOwnerId);
            })->whereHas('tags', function ($query) {
                $query->whereIn('name', $this->tags);
            }, '=', $tagCount)->select('uid')->get();
        $tagMaps = TagMap::with('tags')->whereIn('anchor_id', $chapters)
            ->get();
        $tags = [];
        foreach ($tagMaps as $key => $value) {
            if (isset($tags[$value->tag_id])) {
                $tags[$value->tag_id]['count']++;
            } else {
                $tags[$value->tag_id] = [
                    'tag' => $value->tags,
                    'count' => 1
                ];
            }
        }
        $tagsValue = array_values($tags);
        // 按 count 降序排序
        usort($tagsValue, function ($a, $b) {
            return $b['count'] <=> $a['count']; // PHP 7+ 使用 spaceship 运算符
        });
        return $tagsValue;
    }
}
