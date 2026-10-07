<?php

namespace App\Services\V3;

use App\Models\ProgressChapter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * 章节翻译进度的查询。
 *
 * v2 的 `ProgressChapterController` 另有一套实现（还带着 view= 开关），两边不共用——
 * 见 CLAUDE.md 铁律 2：契约相关的逻辑 v3 重写，不为共享去改 v2。
 */
class ProgressService
{
    /**
     * 取出的列。progress_chapters 还有 summary / uid / completed_chapters 等，
     * 接口不返回就不查。
     *
     * @var list<string>
     */
    private const COLUMNS = [
        'progress_chapters.book',
        'progress_chapters.para',
        'progress_chapters.lang',
        'progress_chapters.progress',
        'progress_chapters.channel_id',
        'progress_chapters.title',
        'progress_chapters.last_chapter_completed_at',
        'progress_chapters.completed_at',
        'progress_chapters.updated_at',
    ];

    /**
     * 按 channel 列出章节进度。
     *
     * @param  array<string, mixed>  $filters  已经过 FormRequest 校验的查询参数
     * @return LengthAwarePaginator<int, ProgressChapter>
     */
    public function chapters(array $filters): LengthAwarePaginator
    {
        $query = ProgressChapter::query()
            ->select(self::COLUMNS)
            // channels 是下划线分隔的 uid 列表，既有契约
            ->whereIn('progress_chapters.channel_id', explode('_', (string) $filters['channels']));

        if (isset($filters['level'])) {
            // 只要该层级及以上的章节，层级在 pali_texts 上
            $query->join('pali_texts', function ($join) {
                $join->on('progress_chapters.book', '=', 'pali_texts.book')
                    ->on('progress_chapters.para', '=', 'pali_texts.paragraph');
            })->where('pali_texts.level', '<=', (int) $filters['level']);
        }
        if (isset($filters['lang'])) {
            $query->where('progress_chapters.lang', $filters['lang']);
        }
        if (isset($filters['book'])) {
            $query->where('progress_chapters.book', $filters['book']);
        }

        // order 已由 FormRequest 的白名单校验过，拼进列名是安全的
        $query->orderBy(
            'progress_chapters.'.($filters['order'] ?? 'completed_at'),
            $filters['dir'] ?? 'desc'
        );

        return $query->paginate($filters['per_page'] ?? 10);
    }
}
