<?php

use App\Models\Channel;
use App\Models\DhammaTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/*
 * POST /v3/markup-previews：把还没保存的源码渲染成节点树（模板生成器的预览）。
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 */

beforeEach(function () {
    $this->me = makeStudio('preview-me');
    $this->channel = makeChannel($this->me, 'preview channel');
});

it('renders the source into a node tree, resolving templates against the given channel', function () {
    (new DhammaTerm)->forceFill([
        'id' => random_int(1, PHP_INT_MAX), 'guid' => (string) Str::uuid(), 'word' => 'dhamma', 'word_en' => 'dhamma',
        'meaning' => '法', 'language' => 'zh-Hans', 'channal' => $this->channel, 'owner' => $this->me,
        'editor_id' => 0, 'create_time' => time() * 1000, 'modify_time' => time() * 1000,
    ])->save();

    $body = $this->postJson('/api/v3/markup-previews', [
        'content' => '{{term|dhamma}}',
        'channels' => [$this->channel],
    ], authHeader($this->me))->assertOk()->json('data.body');

    expect($body[0])->toMatchArray(['type' => 'tpl', 'name' => 'term'])
        ->and($body[0]['props'])->toMatchArray(['word' => 'dhamma', 'meaning' => '法', 'channel' => $this->channel]);
});

it('renders without channels, and keeps unknown templates as their source', function () {
    $body = $this->postJson('/api/v3/markup-previews', [
        'content' => "{{v|url=https://example.com/a.mp4|style=card|title=视频}}\n\n{{nope|x}}",
        'content_type' => 'markdown-e2',
    ], authHeader($this->me))->assertOk()->json('data.body');

    expect(json_encode($body))->toContain('"name":"video"')->toContain('"name":"unknown"');
});

it('requires login', function () {
    $this->postJson('/api/v3/markup-previews', ['content' => 'x'])->assertUnauthorized();
});

it('rejects a missing content, a bad content type and a non-uuid channel', function () {
    $this->postJson('/api/v3/markup-previews', [
        'content_type' => 'html', 'channels' => ['nope'],
    ], authHeader($this->me))->assertUnprocessable()->assertJsonValidationErrors(['content', 'content_type', 'channels.0']);
});

it('refuses a private channel the user cannot read, and 404s an unknown one', function () {
    $other = makeStudio('preview-other');
    $private = makeChannel($other, 'private channel');
    Channel::where('uid', $private)->update(['status' => 10]);

    $this->postJson('/api/v3/markup-previews', ['content' => 'x', 'channels' => [$private]], authHeader($this->me))
        ->assertForbidden();
    $this->postJson('/api/v3/markup-previews', ['content' => 'x', 'channels' => [(string) Str::uuid()]], authHeader($this->me))
        ->assertNotFound();
});
