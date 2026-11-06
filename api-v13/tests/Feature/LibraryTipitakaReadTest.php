<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * 阅读页的目标不存在时返回 404，而不是 500。
 * 旧地址 /library/tipitaka/{book}-{para}/read 先校验 channel 再 301；新地址见最后两条。
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
    $channel = makeChannel(makeStudio('me'), 'c');
    $this->get("/library/tipitaka/channels/{$channel}/chapter/99999-1")
        ->assertNotFound();
});

it('returns 404 on the new reader url when the channel does not exist', function () {
    $this->get('/library/tipitaka/channels/'.Str::uuid().'/chapter/165-123')
        ->assertNotFound();
});
