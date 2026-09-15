<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaliText extends Model
{
    use HasFactory;

    protected $fillable = ['book', 'paragraph', 'level', 'class', 'toc', 'text', 'html', 'lenght'];

    public function progressChapters()
    {
        return $this->hasMany(ProgressChapter::class, null, null)
            ->whereColumn('progress_chapters.book', 'pali_texts.book')
            ->whereColumn('progress_chapters.para', 'pali_texts.paragraph');
    }

    public function tagMaps()
    {
        return $this->hasMany(TagMap::class, 'anchor_id', 'uid');
    }

    public function scopeWithAllTags(Builder $query, array $tagNames): Builder
    {
        foreach ($tagNames as $name) {
            $query->whereHas('tagMaps.tags', function ($q) use ($name) {
                $q->where('name', $name);
            });
        }

        return $query;
    }

    /**
     * 标签多的优化版
     *     public function scopeWithAllTags(Builder $query, array $tagNames): Builder
    {
        $count = count($tagNames);

        $query->whereIn('uid', function ($sub) use ($tagNames, $count) {
            $sub->select('tm.anchor_id')
                ->from('tag_maps as tm')
                ->join('tags as t', 't.id', '=', 'tm.tag_id')
                ->whereIn('t.name', $tagNames)
                ->groupBy('tm.anchor_id')
                ->havingRaw('COUNT(DISTINCT t.name) = ?', [$count]);
        });

        return $query;
    }
     */
}
