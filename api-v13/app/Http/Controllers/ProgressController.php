<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProgressV3Resource;
use App\Models\ProgressChapter;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProgressController extends Controller
{
    /**
     * 列出章节翻译进度
     *
     * 按 channel 查询各章节的翻译完成度。view 目前只支持 channel 一种口径，
     * 传其它值返回 422。
     *
     * @unauthenticated
     *
     * @queryParam view string required 查询口径，目前只有 channel。Enum: channel
     * @queryParam channels string required channel uid 列表，**下划线分隔**（不是逗号）
     * @queryParam level integer 只返回该层级及以上的章节，需联查 pali_texts
     * @queryParam lang string 按译文语言过滤。Example: zh-Hans
     * @queryParam book integer 按典籍 id 过滤
     * @queryParam order string 排序字段（progress_chapters 的列）。Default: completed_at
     * @queryParam dir string 排序方向。Enum: asc,desc Default: desc
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量。Default: 10
     */
    public function index(Request $request)
    {
        //
        $select = [
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
        switch ($request->input('view')) {
            case 'channel':
                $table = ProgressChapter::select($select)
                    ->whereIn('progress_chapters.channel_id', explode('_', $request->input('channels', '')));
                break;
            default:
                throw ValidationException::withMessages(['view' => __('site.invalid_parameter')]);
                break;
        }

        if ($request->filled('level')) {
            $table = $table->join('pali_texts', function ($join) {
                $join->on('progress_chapters.book', '=', 'pali_texts.book')
                    ->on('progress_chapters.para', '=', 'pali_texts.paragraph');
            })->where('pali_texts.level', '<=', (int) $request->input('level'));
        }

        if ($request->has('lang')) {
            $table = $table->where('progress_chapters.lang', $request->input('lang'));
        }
        if ($request->has('book')) {
            $table = $table->where('progress_chapters.book', $request->input('book'));
        }
        $table = $table->orderBy(
            'progress_chapters.'.$request->input('order', 'completed_at'),
            $request->input('dir', 'desc')
        );

        // 分页与 meta 全由框架算；page 参数 paginate() 自己会读
        return ProgressV3Resource::collection(
            $table->paginate($request->integer('per_page', 10))
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
