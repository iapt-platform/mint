<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Skill 用 view=user-edit 列出「我能编辑的 channel」供用户交互式选择，
 * 而不是要求用户手工贴 channel uid（设计决策 3）。
 */
it('lists the channels the user can edit', function () {
    $me = makeStudio('me');
    $mine = makeChannel($me, 'my channel');
    makeChannel(makeStudio('someone-else'), 'not mine');

    $rows = $this->getJson('/api/v2/channel?view=user-edit', authHeader($me))
        ->assertOk()
        ->json('data.rows');

    $uids = array_column($rows, 'uid');

    expect($uids)->toContain($mine);
    expect($uids)->toHaveCount(1);
    // 交互式选择要展示的字段
    expect($rows[0])->toHaveKeys(['uid', 'name', 'lang']);
    expect($rows[0]['name'])->toBe('my channel');
});

it('requires authentication', function () {
    $this->getJson('/api/v2/channel?view=user-edit')
        ->assertJsonPath('ok', false);
});
