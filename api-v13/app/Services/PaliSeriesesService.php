<?php

namespace App\Services;

class PaliSeriesesService
{
    /**
     * 书缩写表：book => [起始段落 => ['abbr_my' => ..., 'abbr_pts' => ..., 'abbr_wp' => ...]]
     *
     * @var array<int, array<int, array{abbr_my: string, abbr_pts: string, abbr_wp: string}>>
     */
    private array $serieses;

    public function __construct()
    {
        $this->serieses = require resource_path('data/pali_serieses.php');
    }

    /**
     * 根据书号与段落号查找该段所属文本的书缩写。
     *
     * 缩写表以“段落起始号”为键，同一书内多个文本按起始段落递增排列；
     * 这里取不大于目标段落的最大起始号对应条目。
     *
     * @return array{abbr_my: string, abbr_pts: string, abbr_wp: string}|null
     */
    public function find(int $book, int $paragraph): ?array
    {
        $entries = $this->serieses[$book] ?? null;

        if (! is_array($entries)) {
            return null;
        }

        $result = null;
        foreach ($entries as $start => $abbr) {
            if ($start > $paragraph) {
                break;
            }

            $result = $abbr;
        }

        return $result;
    }
}
