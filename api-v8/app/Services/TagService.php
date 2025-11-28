<?php

namespace App\Services;

use App\Models\TagMap;

class TagService
{
    public function getTagsName(string $resId)
    {
        $tagsName = TagMap::where('table_name', 'pali_texts')
            ->where('anchor_id', $resId)
            ->join('tags', 'tag_maps.tag_id', '=', 'tags.id')
            ->select('tags.name')
            ->get();
        $output = [];
        foreach ($tagsName as $key => $value) {
            $output[] = $value->name;
        }
        return $output;
    }
}
