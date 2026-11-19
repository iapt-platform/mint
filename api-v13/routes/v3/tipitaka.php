<?php

/*
|--------------------------------------------------------------------------
| v3 · tipitaka —— 三藏相关的功能块
|--------------------------------------------------------------------------
|
| 由 routes/api.php 的 v3 组 require 进来，前缀已在那边加好。
|
| **路径开头是「领域-功能」，不是资源坐标。** `channel + book + para` 在 wikipali
| 里是通用坐标（sentences / wbw / 批注 / 进度全都用它），放在路径开头会让每个新
| 能力来抢同一个前缀，而且看不出这条 API 干什么。所以是
| `/v3/tipitaka-reading/{channel}?book=&para=`，不是 `/v3/channels/{c}/books/{b}/…`。
|
| 路由判据（见根目录 CLAUDE.md）：
|   - 必填参数进路径，可选参数留查询串
|   - 路径变量最多两个，能做到一个最好；多出来的想办法降成 filter
|
*/

use App\Http\Controllers\V3\PaliWordFormController;
use App\Http\Controllers\V3\PaliWordSuggestionController;
use App\Http\Controllers\V3\TipitakaChannelController;
use App\Http\Controllers\V3\TipitakaChapterChannelController;
use App\Http\Controllers\V3\TipitakaChapterController;
use App\Http\Controllers\V3\TipitakaDiscussionController;
use App\Http\Controllers\V3\TipitakaPageNumberController;
use App\Http\Controllers\V3\TipitakaParagraphChannelController;
use App\Http\Controllers\V3\TipitakaReadingController;
use App\Http\Controllers\V3\TipitakaRelatedParagraphAggregateController;
use App\Http\Controllers\V3\TipitakaRelatedParagraphController;
use App\Http\Controllers\V3\TipitakaWordParagraphAggregateController;
use App\Http\Controllers\V3\TipitakaWordParagraphController;
use Illuminate\Support\Facades\Route;

// 只有 channel 必填，所以路径上只有它一个变量；book / chapter / para 全是 filter。
// 不带任何查询串 = 取这个 channel 的全部译文，下载场景要的就是这个。
Route::get('tipitaka-reading/{channel}', TipitakaReadingController::class)
    ->whereUuid('channel');

// 一段经文里的讨论：同样只有 channel 必填；book / chapter / para / to 与 type 等都是 filter。
// 与 /v3/discussions（按单个对象查）必填参数不同，是另一个契约。
Route::get('tipitaka-discussions/{channel}', TipitakaDiscussionController::class)
    ->whereUuid('channel');

// 一章有哪些 channel 及其完成度（原 v2 progress?view=chapter_channels）。book、para 都必填，进路径。
Route::get('tipitaka-chapter-channels/{book}/{para}', TipitakaChapterChannelController::class)
    ->whereNumber(['book', 'para']);

// 章节标题：按名字找章节（容错），或列出一本书的全部标题（原 v2 search-title-index）。q / book 至少给一个，都是过滤条件。
// 某段有内容的 channel（取代 v2 channel?view=paragraphs）
Route::get('tipitaka-paragraph-channels/{book}/{para}', TipitakaParagraphChannelController::class)
    ->name('tipitaka-paragraph-channels.index')
    ->whereNumber(['book', 'para']);

// 有译文的公开 channel 目录（取代 v2 progress?view=channel）
Route::get('tipitaka-channels', TipitakaChannelController::class)
    ->name('tipitaka-channels.index');

Route::get('tipitaka-chapters', TipitakaChapterController::class)
    ->name('tipitaka-chapters.index');

// 段落关联关系：查询（原 v2 related-paragraph）+ 聚合（book_name 列表 / 某 book_name 的 cs_para）。
Route::get('tipitaka-related-paragraphs/aggregate', [TipitakaRelatedParagraphAggregateController::class, 'index'])
    ->name('tipitaka-related-paragraphs.aggregate');
Route::get('tipitaka-related-paragraphs/aggregate/{book_name}', [TipitakaRelatedParagraphAggregateController::class, 'show'])
    ->name('tipitaka-related-paragraphs.aggregate.show');
Route::get('tipitaka-related-paragraphs', [TipitakaRelatedParagraphController::class, 'index'])
    ->name('tipitaka-related-paragraphs.index');

// 词形展开：一个词 → 词根 → 三藏里出现过的词形及次数。取代 v2 case/{word}
Route::get('pali-word-forms/{word}', PaliWordFormController::class)
    ->where('word', '[^/]{1,64}')
    ->name('pali-word-forms.show');

// 巴利词补全（搜索框）：前缀 → 三藏里出现过的词形及次数。取代 v2 pali-word-index?view=key
Route::get('pali-word-suggestions', PaliWordSuggestionController::class)
    ->name('pali-word-suggestions.index');

// 按词形找段落（逐词表）+ 按书统计。取代 v2 search-pali-wbw / search-pali-wbw-books。
// 字面量段 aggregate 必须在前
Route::get('tipitaka-word-paragraphs/aggregate', TipitakaWordParagraphAggregateController::class)
    ->name('tipitaka-word-paragraphs.aggregate');
Route::get('tipitaka-word-paragraphs', [TipitakaWordParagraphController::class, 'index'])
    ->name('tipitaka-word-paragraphs.index');

// 按印本页码（PTS / 缅 / VRI / 泰 / 其他）或 CS 段落号找段落。取代 v2 search-page-number
Route::get('tipitaka-page-numbers/{page}', TipitakaPageNumberController::class)
    ->whereNumber('page')
    ->name('tipitaka-page-numbers.show');
