<?php

/*
 * v3 不准依赖 v2 的帮手类：纯能力走 App\Services 根下的 Service，权限走 App\Services\V3\*AbilityService。
 *
 * 每个命名空间单独一条：`expect([...])` 数组写法配 `not->toUse` 会空过（pest-plugin-arch 4.0.2 实测）。
 */

foreach ([
    'App\Http\Controllers\V3',
    'App\Http\Requests\V3',
    'App\Http\Resources\V3',
    'App\Services\V3',
    'App\Services\ChannelService',
] as $layer) {
    arch("{$layer} does not use ChannelApi")
        ->expect($layer)
        ->not->toUse('App\Http\Api\ChannelApi');
}
