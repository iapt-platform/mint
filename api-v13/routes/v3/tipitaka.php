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

use App\Http\Controllers\V3\TipitakaReadingController;
use Illuminate\Support\Facades\Route;

// 只有 channel 必填，所以路径上只有它一个变量；book / chapter / para 全是 filter。
// 不带任何查询串 = 取这个 channel 的全部译文，下载场景要的就是这个。
Route::get('tipitaka-reading/{channel}', TipitakaReadingController::class)
    ->whereUuid('channel');
