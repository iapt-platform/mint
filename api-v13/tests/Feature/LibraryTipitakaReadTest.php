<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * 阅读页 /library/tipitaka/{book}-{para}/read 的目标不存在时返回 404，而不是 500。
 */
it('returns 404 when the channel does not exist', function () {
    $this->get('/library/tipitaka/165-123/read?channel='.Str::uuid())
        ->assertNotFound();
});

it('returns 404 when the channel is not a uuid', function () {
    $this->get('/library/tipitaka/165-123/read?channel=not-a-uuid')
        ->assertNotFound();
});

it('returns 404 when the id is malformed', function () {
    $this->get('/library/tipitaka/abc/read?channel='.makeChannel(makeStudio('me'), 'c'))
        ->assertNotFound();
});

it('returns 404 when the paragraph is not a chapter', function () {
    $this->get('/library/tipitaka/99999-1/read?channel='.makeChannel(makeStudio('me'), 'c'))
        ->assertNotFound();
});
