<?php

/*
|--------------------------------------------------------------------------
| v3 · term —— 术语 / 百科词条
|--------------------------------------------------------------------------
|
| 由 routes/api.php 的 v3 组 require 进来，前缀已在那边加好。
|
*/

use App\Http\Controllers\V3\ChannelTermController;
use App\Http\Controllers\V3\ChannelTermImportController;
use App\Http\Controllers\V3\GlossaryController;
use App\Http\Controllers\V3\StudioTermController;
use App\Http\Controllers\V3\StudioTermImportController;
use App\Http\Controllers\V3\TermController;
use App\Services\V3\GlossaryService;
use Illuminate\Support\Facades\Route;

// 读公开（可选登录），写要登录。只注册 PATCH，不注册 PUT
Route::get('terms', [TermController::class, 'index'])->name('terms.index');
Route::get('terms/{term}', [TermController::class, 'show'])->name('terms.show')->whereUuid('term');
Route::patch('terms/{term}', [TermController::class, 'update'])->name('terms.update')
    ->middleware('auth.v3')->whereUuid('term');
Route::delete('terms/{term}', [TermController::class, 'destroy'])->name('terms.destroy')
    ->middleware('auth.v3')->whereUuid('term');

Route::get('channels/{channel}/terms', [ChannelTermController::class, 'index'])->name('channels.terms.index')
    ->whereUuid('channel');
Route::post('channels/{channel}/terms', [ChannelTermController::class, 'store'])->name('channels.terms.store')
    ->middleware('auth.v3')->whereUuid('channel');

Route::get('studios/{studio}/terms', [StudioTermController::class, 'index'])->name('studios.terms.index')
    ->middleware('auth.v3')->whereUuid('studio');
Route::post('studios/{studio}/terms', [StudioTermController::class, 'store'])->name('studios.terms.store')
    ->middleware('auth.v3')->whereUuid('studio');

// 术语词汇表（语法术语 / 社区译名），只读、整表。取代 v2 term-vocabulary
Route::get('glossaries/{kind}', GlossaryController::class)->name('glossaries.show')
    ->whereIn('kind', GlossaryService::KINDS);

// 术语导入（xlsx 上传）。导出不另设端点：列表接口带 Accept: xlsx 时直接返回表格
Route::post('channels/{channel}/terms/imports', ChannelTermImportController::class)->name('channels.terms.imports')
    ->middleware('auth.v3')->whereUuid('channel');
Route::post('studios/{studio}/terms/imports', StudioTermImportController::class)->name('studios.terms.imports')
    ->middleware('auth.v3')->whereUuid('studio');
