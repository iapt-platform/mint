<?php

namespace App\Models;

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
}
