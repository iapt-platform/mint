<?php

use App\Models\AiModel;
use App\Models\Channel;
use App\Models\DhammaTerm;
use App\Models\TermCategory;
use App\Services\AuthService;
use App\Services\V3\TermExchangeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

uses(RefreshDatabase::class);

/*
 * 术语导入 / 导出（xlsx）：列表接口带 Accept: xlsx 导出；POST …/terms/imports 导入（部分成功）。
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 */

const XLSX = TermExchangeService::MIME;

/** 生成一个 xlsx 上传文件：第一行是列名 */
function termSheet(array $header, array $rows): UploadedFile
{
    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->fromArray([$header, ...$rows], null, 'A1', true);
    $path = tempnam(sys_get_temp_dir(), 'terms').'.xlsx';
    (new Xlsx($spreadsheet))->save($path);

    return new UploadedFile($path, 'terms.xlsx', XLSX, null, true);
}

/** 读回导出的 xlsx：列名 => 值 的行数组 */
function readSheet(string $content): array
{
    $path = tempnam(sys_get_temp_dir(), 'export').'.xlsx';
    file_put_contents($path, $content);
    $rows = IOFactory::load($path)->getActiveSheet()->toArray(null, false, false);
    $header = array_shift($rows);

    return array_map(fn ($row) => array_combine($header, $row), $rows);
}

function importTo(string $channel, string $as, UploadedFile $file)
{
    return test()->post("/api/v3/channels/{$channel}/terms/imports", ['file' => $file], authHeader($as) + ['Accept' => 'application/json']);
}

function existingTerm(string $channel, string $as, array $body = []): array
{
    return test()->postJson("/api/v3/channels/{$channel}/terms", $body + ['word' => 'dhamma', 'meaning' => '法'], authHeader($as))
        ->assertCreated()->json('data');
}

/* ------------------------------------------------------------------ 导出 */

it('exports the terms of a channel as xlsx with the filters of the list', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    $term = existingTerm($channel, $owner, ['tag' => '义', 'quality' => 'draft', 'note' => '正文 {{category|法义}}']);
    existingTerm($channel, $owner, ['word' => 'sīla', 'meaning' => '戒']);

    $res = $this->get("/api/v3/channels/{$channel}/terms", ['Accept' => XLSX])->assertOk();
    expect($res->headers->get('Content-Type'))->toBe(XLSX);
    $rows = readSheet($res->getContent());
    expect(array_keys($rows[0]))->toBe(TermExchangeService::COLUMNS)
        ->and(array_column($rows, 'word'))->toBe(['dhamma', 'sīla'])
        ->and($rows[0])->toMatchArray(['id' => $term['id'], 'tag' => '义', 'quality' => 'draft', 'etag' => $term['etag'], 'channel_id' => $channel]);

    $filtered = readSheet($this->get("/api/v3/channels/{$channel}/terms?quality=draft", ['Accept' => XLSX])->getContent());
    expect(array_column($filtered, 'word'))->toBe(['dhamma']);

    Channel::where('uid', $channel)->update(['status' => Channel::STATUS_PRIVATE]);
    $this->get("/api/v3/channels/{$channel}/terms", ['Accept' => XLSX])->assertForbidden();
});

it('exports the terms of a studio for its members only', function () {
    [$team, $users] = makeTeam();
    existingTerm(makeChannel($team), $users['owner']);

    $rows = readSheet($this->get("/api/v3/studios/{$team}/terms", authHeader($users['reader']) + ['Accept' => XLSX])->assertOk()->getContent());
    expect(array_column($rows, 'word'))->toBe(['dhamma']);
    $this->get("/api/v3/studios/{$team}/terms", authHeader($users['outsider']) + ['Accept' => XLSX])->assertForbidden();
});

/* ------------------------------------------------------------------ 导入 */

it('imports new and changed terms, skipping bad rows with reasons (partial success)', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    $existing = existingTerm($channel, $owner, ['tag' => '义']);
    $untouched = existingTerm($channel, $owner, ['word' => 'sīla', 'meaning' => '戒']);

    $res = importTo($channel, $owner, termSheet(
        ['id', 'word', 'tag', 'meaning', 'note', 'quality', 'etag'],
        [
            [$existing['id'], 'dhamma', '义', '法（改）', '正文 {{category|律学}}', 'standard', $existing['etag']], // 2 改
            [null, 'kamma', null, '业', null, null, null],                                                          // 3 新建
            [null, 'sīla', null, '戒', null, null, null],                                                           // 4 无 id 同 word：内容没变
            [null, null, null, '没有词', null, null, null],                                                          // 5 word 为空
            [$existing['id'], 'dhammā', '义', 'x', null, null, null],                                                // 6 改 word
            [null, 'vinaya', null, '律', null, 'great', null],                                                       // 7 quality 不对
            [null, 'nibbāna', null, null, null, null, null],                                                         // 8 新建缺 meaning
        ],
    ))->assertOk();

    expect($res->json('data'))->toMatchArray(['created' => 1, 'updated' => 1, 'unchanged' => 1, 'reindexed' => true])
        ->and(array_column($res->json('data.errors'), 'row'))->toBe([5, 6, 7, 8]);
    expect(DhammaTerm::find($existing['id']))->meaning->toBe('法（改）')->quality->toBe('standard')->version->toBe(2);
    expect(TermCategory::where('term_guid', $existing['id'])->pluck('category')->all())->toBe(['律学']);
    expect(DhammaTerm::where('channal', $channel)->where('word', 'kamma')->first())
        ->content_type->toBe('markdown-e2')->language->toBe('zh-Hans');
    expect(DhammaTerm::find($untouched['id'])->version)->toBe(1);
});

it('skips rows whose term was changed after export (etag)', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    $term = existingTerm($channel, $owner);
    $this->patchJson("/api/v3/terms/{$term['id']}", ['meaning' => '别人改的'], authHeader($owner) + ['If-Match' => $term['etag']])->assertOk();

    $res = importTo($channel, $owner, termSheet(['id', 'word', 'meaning', 'etag'], [[$term['id'], 'dhamma', '我的', $term['etag']]]));
    expect($res->json('data.updated'))->toBe(0)->and($res->json('data.errors.0.row'))->toBe(2);
    expect(DhammaTerm::find($term['id'])->meaning)->toBe('别人改的');

    // 没有 etag 列（手工做的表）照常覆盖
    importTo($channel, $owner, termSheet(['id', 'word', 'meaning'], [[$term['id'], 'dhamma', '覆盖']]))->assertJsonPath('data.updated', 1);
});

it('reads a v2 exported sheet by column names', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);

    importTo($channel, $owner, termSheet(
        ['id', 'word', 'meaning', 'other_meaning', 'note', 'tag', 'language', 'channel_id'],
        [[null, 'dhamma', '法', '达摩', '正文', null, 'en', 'ignored']],
    ))->assertJsonPath('data.created', 1);

    // channel 导入：language / channel_id 列不看，跟随 channel
    expect(DhammaTerm::where('channal', $channel)->first())->other_meaning->toBe('达摩')->language->toBe('zh-Hans');
});

it('imports into a studio by channel_id, and needs a language for studio-wide terms', function () {
    [$team, $users] = makeTeam();
    $channel = makeChannel($team);
    $foreign = makeChannel(makeStudio('other'));

    $res = $this->post("/api/v3/studios/{$team}/terms/imports", ['file' => termSheet(
        ['word', 'meaning', 'language', 'channel_id'],
        [
            ['in-channel', '甲', null, $channel],
            ['general', '乙', 'zh-Hans', null],
            ['no-language', '丙', null, null],
            ['foreign', '丁', null, $foreign],
        ],
    )], authHeader($users['owner']) + ['Accept' => 'application/json'])->assertOk();

    expect($res->json('data.created'))->toBe(2)->and(array_column($res->json('data.errors'), 'row'))->toBe([4, 5]);
    expect(DhammaTerm::where('word', 'in-channel')->value('channal'))->toBe($channel);
    expect(DhammaTerm::where('word', 'general')->first())->channal->toBeNull()->owner->toBe($team);

    // 团队 editor 不能新建 studio 通用术语，但能写 channel 里的
    $res = $this->post("/api/v3/studios/{$team}/terms/imports", ['file' => termSheet(
        ['word', 'meaning', 'language', 'channel_id'],
        [['by-editor', '戊', null, $channel], ['general-by-editor', '己', 'zh-Hans', null]],
    )], authHeader($users['editor']) + ['Accept' => 'application/json'])->assertOk();
    expect($res->json('data.created'))->toBe(1)->and($res->json('data.errors.0.row'))->toBe(3);
});

it('rejects importers without write access, AI models, and unreadable files', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    $file = fn () => termSheet(['word', 'meaning'], [['dhamma', '法']]);

    $this->post("/api/v3/channels/{$channel}/terms/imports", ['file' => $file()], ['Accept' => 'application/json'])->assertUnauthorized();
    importTo($channel, makeStudio('stranger'), $file())->assertForbidden();
    $model = AiModel::factory()->ownedBy($owner)->create();
    $this->post("/api/v3/channels/{$channel}/terms/imports", ['file' => $file()], [
        'Authorization' => 'Bearer '.AuthService::getUserToken($model->uid), 'Accept' => 'application/json',
    ])->assertForbidden();

    importTo($channel, $owner, UploadedFile::fake()->createWithContent('terms.xlsx', 'not a spreadsheet'))
        ->assertUnprocessable()->assertJsonValidationErrors('file');
    importTo($channel, $owner, termSheet(['foo', 'bar'], [['a', 'b']]))
        ->assertUnprocessable()->assertJsonValidationErrors('file');
    $this->post("/api/v3/channels/{$channel}/terms/imports", [], authHeader($owner) + ['Accept' => 'application/json'])
        ->assertUnprocessable()->assertJsonValidationErrors('file');
    expect(DhammaTerm::count())->toBe(0);
});
