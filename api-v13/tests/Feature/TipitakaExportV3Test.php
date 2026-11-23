<?php

use App\Facades\RedisCache;
use App\Models\BookTitle;
use App\Models\Channel;
use App\Models\PaliText;
use App\Models\ProgressChapter;
use App\Models\Sentence;
use App\Models\Tag;
use App\Models\TagMap;
use App\Services\V3\TipitakaExportQueue;
use App\Services\V3\TipitakaExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/*
| 三藏下载 /v3/tipitaka-exports。这里断言的是形状而非契约（spectator 未安装）。
| 生成在测试里同步调 TipitakaExportService::run()，队列换成 mock，不连 RabbitMQ；
| 文件进的是 Storage::fake() 的默认磁盘（本地磁盘的流式下载）；S3 的跳转单独一条测。
*/

uses(RefreshDatabase::class);

beforeEach(fn () => Storage::fake());

function exportPaliText(int $book, int $paragraph, int $level, int $chapterLen, string $toc = ''): PaliText
{
    $row = new PaliText;
    $row->forceFill([
        'book' => $book, 'paragraph' => $paragraph, 'level' => $level, 'class' => '', 'toc' => $toc,
        'text' => '', 'html' => '', 'pcd_book_id' => 0, 'chapter_len' => $chapterLen, 'chapter_strlen' => 100,
        'uid' => (string) Str::uuid(),
    ])->save();

    return $row;
}

function exportProgress(string $channel, int $book, int $para, float $progress, ?string $title = null): void
{
    (new ProgressChapter)->forceFill([
        'id' => random_int(1, PHP_INT_MAX), 'uid' => (string) Str::uuid(), 'book' => $book, 'para' => $para,
        'lang' => 'zh-Hans', 'channel_id' => $channel, 'progress' => $progress, 'title' => $title,
        'all_trans' => 0, 'public' => 0,
    ])->save();
}

/**
 * 一部作品 sn 9100 = book 9100 的 3–102 段（两章：4、50），归入长部 › 戒蕴品（分类 3）。
 * channel 收录了它，有作品标题、两个章标题和带脚注的正文。
 *
 * @return array{channel: string, owner: string, sn: int}
 */
function exportFixture(): array
{
    $root = exportPaliText(9100, 3, 1, 100, 'Sīlakkhandhavaggapāḷi');
    exportPaliText(9100, 4, 2, 46, '1. Brahmajālasuttaṃ');
    exportPaliText(9100, 50, 2, 53, '2. Sāmaññaphalasuttaṃ');
    foreach (['sutta', 'dīghanikāya', 'sīlakkhandhavagga', 'pāḷi'] as $name) {
        $tag = Tag::where('name', $name)->first();
        if (! $tag) {
            $tag = new Tag;
            $tag->forceFill(['id' => (string) Str::uuid(), 'name' => $name, 'color' => 0, 'owner_id' => (string) Str::uuid()])->save();
        }
        (new TagMap)->forceFill(['id' => (string) Str::uuid(), 'table_name' => 'pali_texts', 'anchor_id' => $root->uid, 'tag_id' => $tag->id])->save();
    }
    (new BookTitle)->forceFill(['sn' => 9100, 'book' => 9100, 'paragraph' => 3, 'title' => 'dīghanikāyapāḷi'])->save();

    $owner = makeStudio('export-owner');
    $channel = makeChannel($owner, 'export channel');
    exportProgress($channel, 9100, 3, 0.4, '戒蕴品');
    makeSentence($channel, 9100, 3, 1, '戒蕴品');
    makeSentence($channel, 9100, 4, 1, '梵网经');
    makeSentence($channel, 9100, 5, 1, '如是我闻{{note|第一条注}}。');
    makeSentence($channel, 9100, 5, 2, '一时佛住{{note|第二条注}}。');
    makeSentence($channel, 9100, 50, 1, '沙门果经');
    makeSentence($channel, 9100, 51, 1, '尔时国王{{note|第三条注}}。');

    return ['channel' => $channel, 'owner' => $owner, 'sn' => 9100];
}

/**
 * 换掉队列，返回收到的 [id, bulk] 列表。
 */
function fakeExportQueue(): ArrayObject
{
    $pushed = new ArrayObject;
    test()->mock(TipitakaExportQueue::class, function ($mock) use ($pushed) {
        $mock->shouldReceive('push')->andReturnUsing(function (string $id, bool $bulk) use ($pushed) {
            $pushed[] = [$id, $bulk];
        });
    });

    return $pushed;
}

/**
 * 发起并同步生成，返回就绪后的 data。
 *
 * @param  array<string, mixed>  $body
 * @return array<string, mixed>
 */
function exportReady(array $body): array
{
    $id = test()->postJson('/api/v3/tipitaka-exports', $body)->assertStatus(202)->json('data.id');
    app(TipitakaExportService::class)->run($id);

    return test()->postJson('/api/v3/tipitaka-exports', $body)->assertOk()->json('data');
}

/**
 * zip 字节 → [条目名 => 内容]
 *
 * @return array<string, string>
 */
function unzipExport(string $bytes): array
{
    $path = tempnam(sys_get_temp_dir(), 'export');
    file_put_contents($path, $bytes);
    $zip = new ZipArchive;
    $zip->open($path);
    $files = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $files[$zip->getNameIndex($i)] = $zip->getFromIndex($i);
    }
    $zip->close();
    unlink($path);

    return $files;
}

it('queues a work export and does not queue the same one twice', function () {
    $pushed = fakeExportQueue();
    ['channel' => $channel, 'sn' => $sn] = exportFixture();

    $response = $this->postJson('/api/v3/tipitaka-exports', ['channel' => $channel, 'work' => $sn, 'format' => 'html'])
        ->assertStatus(202)
        ->assertHeader('Retry-After', '2');
    expect($response->json('data'))->toMatchArray([
        'status' => 'queued', 'format' => 'html', 'title' => '戒蕴品', 'works' => 1, 'size' => null, 'download_url' => null,
    ])->and($response->json('data.id'))->toMatch('/^[0-9a-f]{40}$/')
        ->and($response->json('data.filename'))->toBe('戒蕴品-export channel-html.zip');

    $this->postJson('/api/v3/tipitaka-exports', ['channel' => $channel, 'work' => $sn, 'format' => 'html'])->assertStatus(202);

    expect($pushed->getArrayCopy())->toBe([[$response->json('data.id'), false]]);
});

it('generates an html work export and serves it through the signed link', function () {
    fakeExportQueue();
    ['channel' => $channel, 'sn' => $sn] = exportFixture();

    $data = exportReady(['channel' => $channel, 'work' => $sn, 'format' => 'html']);
    expect($data['status'])->toBe('ready')
        ->and($data['size'])->toBeInt()->toBeGreaterThan(0)
        ->and($data['download_url'])->toStartWith("/api/v3/tipitaka-exports/{$data['id']}/file?expires=");

    $this->getJson("/api/v3/tipitaka-exports/{$data['id']}")->assertOk()->assertJsonPath('data.status', 'ready');

    $file = $this->get($data['download_url'])
        ->assertOk()
        ->assertHeader('Content-Type', 'application/zip')
        ->assertHeader('Accept-Ranges', 'bytes');
    expect($file->headers->get('Content-Disposition'))->toContain("filename*=utf-8''");
    $bytes = $file->streamedContent();
    expect(strlen($bytes))->toBe($data['size']);

    $files = unzipExport($bytes);
    expect(array_keys($files))->toBe(['戒蕴品.html']);
    expect($files['戒蕴品.html'])
        ->toContain('<!doctype html>')
        ->toContain('梵网经')
        ->toContain('如是我闻')
        ->toContain('href="#para-4"');

    // 断点续传与条件请求
    $part = $this->get($data['download_url'], ['Range' => 'bytes=10-19'])->assertStatus(206)
        ->assertHeader('Content-Range', "bytes 10-19/{$data['size']}");
    expect($part->streamedContent())->toBe(substr($bytes, 10, 10));
    $this->get($data['download_url'], ['Range' => 'bytes=999999999-'])->assertStatus(416);
    $this->get($data['download_url'], ['If-None-Match' => $file->headers->get('ETag')])->assertStatus(304);
});

it('stores the file in the temporary area and redirects to a signed S3 link when the disk is S3', function () {
    config(['filesystems.default' => 's3']);
    Storage::fake('s3')->buildTemporaryUrlsUsing(
        fn (string $path, DateTimeInterface $expiration, array $options) => "https://s3.test/{$path}?".http_build_query($options)
    );
    fakeExportQueue();
    ['channel' => $channel, 'sn' => $sn] = exportFixture();

    $data = exportReady(['channel' => $channel, 'work' => $sn, 'format' => 'html']);
    $path = config('mint.attachments.bucket_name.temporary')."/tipitaka-exports/{$channel}/html/work-{$sn}";
    Storage::disk('s3')->assertExists($path);
    expect(Storage::disk('s3')->size($path))->toBe($data['size']);

    $location = $this->get($data['download_url'])->assertRedirect()->headers->get('Location');
    expect($location)->toStartWith("https://s3.test/{$path}?")
        ->and(urldecode($location))->toContain('ResponseContentDisposition=attachment;')->toContain("filename*=utf-8''");

    // 文件被生命周期规则清掉了：状态还在也当作没有
    Storage::disk('s3')->delete($path);
    $this->get($data['download_url'])->assertNotFound();
    $this->getJson("/api/v3/tipitaka-exports/{$data['id']}")->assertNotFound();
});

it('regenerates into the same file once the status expires or the translation changes', function () {
    fakeExportQueue();
    ['channel' => $channel, 'sn' => $sn] = exportFixture();
    $path = config('mint.attachments.bucket_name.temporary')."/tipitaka-exports/{$channel}/html/work-{$sn}";

    $first = exportReady(['channel' => $channel, 'work' => $sn, 'format' => 'html']);
    expect(Storage::files(dirname($path)))->toBe([$path]);

    // Redis 里的状态过期：重新生成，同一个 id、同一个文件
    RedisCache::forget("/tipitaka-export/status/{$first['id']}");
    $again = exportReady(['channel' => $channel, 'work' => $sn, 'format' => 'html']);
    expect($again['id'])->toBe($first['id'])
        ->and(Storage::files(dirname($path)))->toBe([$path]);

    // 译文改了：换了 id，仍覆盖同一个文件；旧 id 的链接拿到的是新文件
    $sentence = Sentence::where('channel_uid', $channel)->orderBy('paragraph')->first();
    // 范围统计（指纹的来源）缓存 5 分钟；指纹看 max(updated_at)，改动要晚于建好的数据
    $this->travel(6)->minutes();
    $sentence->forceFill(['content' => '改过的译文', 'strlen' => 5])->save();
    $changed = exportReady(['channel' => $channel, 'work' => $sn, 'format' => 'html']);
    expect($changed['id'])->not->toBe($first['id'])
        ->and(Storage::files(dirname($path)))->toBe([$path]);
    $files = unzipExport($this->get($first['download_url'])->assertOk()->streamedContent());
    expect(array_values($files)[0])->toContain('改过的译文');
});

it('rejects the file link without a valid signature', function () {
    fakeExportQueue();
    ['channel' => $channel, 'sn' => $sn] = exportFixture();
    $data = exportReady(['channel' => $channel, 'work' => $sn, 'format' => 'html']);

    $this->get("/api/v3/tipitaka-exports/{$data['id']}/file")->assertForbidden();
    $this->get($data['download_url'].'0')->assertForbidden();
});

it('splits markdown by chapter and renumbers footnotes per file', function () {
    fakeExportQueue();
    ['channel' => $channel, 'sn' => $sn] = exportFixture();
    $data = exportReady(['channel' => $channel, 'work' => $sn, 'format' => 'markdown']);

    $files = unzipExport($this->get($data['download_url'])->streamedContent());
    expect(array_keys($files))->toEqualCanonicalizing(['戒蕴品/01-梵网经.md', '戒蕴品/02-沙门果经.md', '戒蕴品/README.md']);
    expect($files['戒蕴品/README.md'])->toContain('# 戒蕴品')->toContain('(<01-梵网经.md>)');
    // 两句各自从 [^1] 起编号，合进一章后是 1、2
    expect($files['戒蕴品/01-梵网经.md'])
        ->toStartWith("# 梵网经\n")
        ->toContain('如是我闻[^1]')
        ->toContain('一时佛住[^2]')
        ->toContain('[^1]: 第一条注')
        ->toContain('[^2]: 第二条注');
    expect($files['戒蕴品/02-沙门果经.md'])->toContain('尔时国王[^1]')->toContain('[^1]: 第三条注');
});

it('gives single-work epub and docx as the file itself', function (string $format, string $type, string $magic) {
    fakeExportQueue();
    ['channel' => $channel, 'sn' => $sn] = exportFixture();
    $data = exportReady(['channel' => $channel, 'work' => $sn, 'format' => $format]);

    expect($data['filename'])->toBe("戒蕴品-export channel.{$format}")
        ->and($data['content_type'])->toBe($type);
    $bytes = $this->get($data['download_url'])->assertOk()->assertHeader('Content-Type', $type)->streamedContent();
    // epub 与 docx 本身都是 zip 容器
    expect(substr($bytes, 0, 2))->toBe($magic);
})->with([
    'epub' => ['epub', 'application/epub+zip', 'PK'],
    'docx' => ['docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'PK'],
])->skip(fn () => trim((string) shell_exec('command -v pandoc')) === '', 'pandoc 不可用');

it('builds the mobile offline package as gzip-encoded ndjson, only per work', function () {
    fakeExportQueue();
    ['channel' => $channel, 'sn' => $sn] = exportFixture();

    $this->postJson('/api/v3/tipitaka-exports', ['channel' => $channel, 'format' => 'offline'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('format');

    $data = exportReady(['channel' => $channel, 'work' => $sn, 'format' => 'offline']);
    expect($data['content_type'])->toBe('application/x-ndjson')
        ->and($data['filename'])->toBe('戒蕴品-export channel.ndjson');
    $file = $this->get($data['download_url'])
        ->assertHeader('Content-Type', 'application/x-ndjson')
        ->assertHeader('Content-Encoding', 'gzip');
    $lines = array_map(fn ($l) => json_decode($l, true), explode("\n", trim(gzdecode($file->streamedContent()))));

    expect($lines[0])->toMatchArray(['type' => 'header', 'format' => 1, 'id' => $data['id'], 'channel' => $channel, 'sn' => $sn, 'book' => 9100, 'para' => 3, 'paragraphs' => 5])
        ->and(end($lines))->toBe(['type' => 'end', 'count' => 5])
        ->and($lines[1])->toMatchArray(['type' => 'para', 'book' => 9100, 'para' => 3]);
    // 与阅读接口的 display 一字不差
    $display = $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9100&para=5")->json('data.0.display');
    expect(collect($lines)->firstWhere('para', 5)['html'])->toBe($display);
});

it('bundles the whole channel with an index page on the bulk queue', function () {
    $pushed = fakeExportQueue();
    ['channel' => $channel] = exportFixture();

    $data = exportReady(['channel' => $channel, 'format' => 'html']);
    expect($pushed[0][1])->toBeTrue()
        ->and($data['filename'])->toBe('export channel-html.zip');

    $files = unzipExport($this->get($data['download_url'])->streamedContent());
    expect(array_keys($files))->toEqualCanonicalizing(['export channel-html/index.html', 'export channel-html/works/9100-戒蕴品.html']);
    expect($files['export channel-html/index.html'])->toContain('href="works/9100-%E6%88%92%E8%95%B4%E5%93%81.html"')->toContain('data-q');
    expect($files['export channel-html/works/9100-戒蕴品.html'])->toContain('href="../index.html"');
});

it('keeps private channels to people who can read them', function () {
    $pushed = fakeExportQueue();
    ['channel' => $channel, 'owner' => $owner, 'sn' => $sn] = exportFixture();
    Channel::where('uid', $channel)->update(['status' => 10]);

    $this->postJson('/api/v3/tipitaka-exports', ['channel' => $channel, 'work' => $sn, 'format' => 'html'])->assertForbidden();
    $this->postJson('/api/v3/tipitaka-exports', ['channel' => $channel, 'work' => $sn, 'format' => 'html'], authHeader(makeStudio('stranger')))->assertForbidden();
    $id = $this->postJson('/api/v3/tipitaka-exports', ['channel' => $channel, 'work' => $sn, 'format' => 'html'], authHeader($owner))
        ->assertStatus(202)->json('data.id');

    // 状态也只给看得见的人
    $this->getJson("/api/v3/tipitaka-exports/{$id}")->assertForbidden();
    $this->getJson("/api/v3/tipitaka-exports/{$id}", authHeader($owner))->assertOk();
    expect($pushed)->toHaveCount(1);
});

it('validates the request', function () {
    fakeExportQueue();
    ['channel' => $channel, 'sn' => $sn] = exportFixture();

    $this->postJson('/api/v3/tipitaka-exports', [])->assertStatus(422)->assertJsonValidationErrors(['channel', 'format']);
    $this->postJson('/api/v3/tipitaka-exports', ['channel' => $channel, 'format' => 'pdf'])->assertStatus(422)->assertJsonValidationErrors('format');
    $this->postJson('/api/v3/tipitaka-exports', ['channel' => $channel, 'work' => $sn, 'category' => 3, 'format' => 'html'])
        ->assertStatus(422)->assertJsonValidationErrors('work');
    // 没有收录的作品、不存在的 channel
    $this->postJson('/api/v3/tipitaka-exports', ['channel' => $channel, 'work' => 1, 'format' => 'html'])->assertNotFound();
    $this->postJson('/api/v3/tipitaka-exports', ['channel' => (string) Str::uuid(), 'work' => $sn, 'format' => 'html'])->assertNotFound();
    $this->getJson('/api/v3/tipitaka-exports/'.str_repeat('a', 40))->assertNotFound();
});

it('refuses selections larger than the limit', function () {
    $pushed = fakeExportQueue();
    ['channel' => $channel, 'sn' => $sn] = exportFixture();
    config(['mint.tipitaka_export.max_source_chars' => 5]);

    $this->postJson('/api/v3/tipitaka-exports', ['channel' => $channel, 'work' => $sn, 'format' => 'html'])
        ->assertStatus(422)
        ->assertJsonPath('type', fn ($type) => str_ends_with($type, 'export-too-large'));
    expect($pushed)->toHaveCount(0);
});

it('marks the export failed when the file is too big, and retries on the next request', function () {
    $pushed = fakeExportQueue();
    ['channel' => $channel, 'sn' => $sn] = exportFixture();
    config(['mint.tipitaka_export.max_file_bytes' => 10]);

    $id = $this->postJson('/api/v3/tipitaka-exports', ['channel' => $channel, 'work' => $sn, 'format' => 'html'])->json('data.id');
    app(TipitakaExportService::class)->run($id);

    $this->getJson("/api/v3/tipitaka-exports/{$id}")->assertOk()->assertJsonPath('data.status', 'failed');
    $this->postJson('/api/v3/tipitaka-exports', ['channel' => $channel, 'work' => $sn, 'format' => 'html'])
        ->assertStatus(202)->assertJsonPath('data.status', 'queued');
    expect($pushed)->toHaveCount(2);
});

it('shows the library download pages', function () {
    fakeExportQueue();
    ['channel' => $channel, 'sn' => $sn] = exportFixture();
    exportReady(['channel' => $channel, 'work' => $sn, 'format' => 'epub']);

    $this->get("/library/tipitaka/channels/{$channel}/work/{$sn}")
        ->assertOk()
        ->assertSee("/library/tipitaka/channels/{$channel}/work/{$sn}/download", false);

    $page = $this->get("/library/tipitaka/channels/{$channel}/work/{$sn}/download")->assertOk();
    $page->assertSee('data-format="epub"', false)
        ->assertSee('data-format="markdown"', false)
        ->assertDontSee('data-format="offline"', false)
        ->assertSee('/api/v3/tipitaka-exports/', false);
    expect(substr_count($page->getContent(), 'data-state="ready"'))->toBe(1);

    $this->get("/library/tipitaka/channels/{$channel}/download")->assertOk()->assertSee('戒蕴品');
    // 打包只有「全部」，没有按藏 / 分类的切换
    $this->get("/library/tipitaka/channels/{$channel}/download")->assertDontSee('tipitaka-pill', false);
    $this->get("/library/tipitaka/channels/{$channel}/work/1/download")->assertNotFound();
})->skip(fn () => ! file_exists(public_path('build/manifest.json')), '需要先 npm run build');

it('finds the work by book and paragraph for clients that only know coordinates', function () {
    fakeExportQueue();
    ['channel' => $channel, 'sn' => $sn] = exportFixture();

    $bySn = $this->postJson('/api/v3/tipitaka-exports', ['channel' => $channel, 'work' => $sn, 'format' => 'offline'])->json('data.id');
    // 作品里的任一段都指向同一份（id 相同）
    $this->postJson('/api/v3/tipitaka-exports', ['channel' => $channel, 'book' => 9100, 'para' => 51, 'format' => 'offline'])
        ->assertStatus(202)
        ->assertJsonPath('data.id', $bySn);

    $this->postJson('/api/v3/tipitaka-exports', ['channel' => $channel, 'book' => 9100, 'format' => 'offline'])
        ->assertStatus(422)->assertJsonValidationErrors('para');
    $this->postJson('/api/v3/tipitaka-exports', ['channel' => $channel, 'book' => 9100, 'para' => 3, 'work' => $sn, 'format' => 'offline'])
        ->assertStatus(422)->assertJsonValidationErrors('work');
    // 不在任何作品里的段
    $this->postJson('/api/v3/tipitaka-exports', ['channel' => $channel, 'book' => 9100, 'para' => 999, 'format' => 'offline'])->assertNotFound();
});
