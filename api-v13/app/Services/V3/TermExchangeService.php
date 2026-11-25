<?php

namespace App\Services\V3;

use App\Exceptions\BusinessException;
use App\Models\Channel;
use App\Models\DhammaTerm;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * 术语的批量导入 / 导出（xlsx）。
 *
 * 取代 v2 `terms-export`（生成后存 Redis 5 分钟、再凭 uuid 下载）与 `terms-import`（GET 写入、读附件临时文件）。
 *
 * 导出：列表接口带 `Accept: xlsx` 时整表返回（过滤同列表、不分页），权限同列表。
 * 导入：上传 xlsx。按**表头的列名**取值，所以 v2 导出的旧表格（id / word / meaning / other_meaning / note / tag /
 * language / channel_id）也能直接导入。规则（2026-11-17 用户定）：
 * - **部分成功**：出错的行跳过、其余照写，响应逐行列出跳过的原因
 * - 有 id：改那一条。word / tag 与库里不同就报错（建立后不能改）；带 etag 列且与当前不一致就报「导出之后被改过」
 * - 无 id：同范围里已有同 word + tag 的就改它，没有就新建
 * - word 为空报错（v2「有 id、word 为空就删除」不保留，删除走单条删除）
 * - 每行的权限与单条新建 / 修改相同；只给人类（不支持 agent 代写）
 * - 改动不超过 {@see self::REINDEX_LIMIT} 条时立即重建搜索索引，更多的等每周 `opensearch:index-term`
 */
class TermExchangeService
{
    public const MIME = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    public const MAX_ROWS = 5000;

    /** 改动不超过这么多条才立即重建搜索索引（逐条渲染全文，量大会很慢；线上没有队列 worker） */
    public const REINDEX_LIMIT = 50;

    /** 导出的列（导入按列名认，顺序无所谓） */
    public const COLUMNS = [
        'id', 'word', 'tag', 'meaning', 'other_meaning', 'summary', 'note',
        'quality', 'content_type', 'redirect', 'language', 'channel_id', 'etag',
    ];

    /** 导入时可以改的内容列 */
    private const CONTENT = ['meaning', 'other_meaning', 'summary', 'note', 'quality', 'content_type', 'redirect'];

    public function __construct(
        private readonly TermService $terms,
        private readonly ChannelAbilityService $channels,
        private readonly GroupAbilityService $groups,
        private readonly AgentGrantService $agents,
    ) {}

    /* ------------------------------------------------------------------ 导出 */

    /**
     * 把查询结果写成 xlsx（二进制内容）。
     *
     * @param  Builder<DhammaTerm>  $query
     */
    public function export(Builder $query): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([self::COLUMNS], null, 'A1');

        $line = 2;
        foreach ($query->cursor() as $term) {
            $sheet->fromArray([[
                $term->guid, $term->word, $this->terms->normalizeTag($term->tag), $term->meaning,
                $term->other_meaning, $term->summary, $term->note, $term->quality,
                $term->effectiveContentType(), $term->redirect, $term->language, $term->channal, $term->etag(),
            ]], null, "A{$line}", true);
            $line++;
        }

        $stream = fopen('php://memory', 'r+');
        (new Xlsx($spreadsheet))->save($stream);
        rewind($stream);
        $content = (string) stream_get_contents($stream);
        fclose($stream);

        return $content;
    }

    /* ------------------------------------------------------------------ 导入 */

    /**
     * 导入到 channel：所有行都落在这个 channel，owner 与语言跟 channel（表里的 channel_id / language 列不看）。
     *
     * @return array{created: int, updated: int, unchanged: int, reindexed: bool, errors: list<array{row: int, message: string}>}
     *
     * @throws AuthorizationException 不能写这个 channel，或是 AI 模型
     * @throws ValidationException 文件读不了、缺 word / meaning 列、行数超限
     */
    public function importToChannel(Channel $channel, string $actorUid, UploadedFile $file): array
    {
        $this->agents->assertHuman($actorUid);
        if (! $this->channels->abilities($channel, $actorUid)['write']) {
            throw new AuthorizationException(__('messages.term_forbidden'));
        }

        return $this->import($actorUid, $file, fn (array $row) => [
            'scope' => DhammaTerm::query()->where('channal', $channel->uid),
            'target' => ['channel' => $channel->uid, 'owner' => $channel->owner_uid, 'language' => $channel->lang],
            'language_editable' => false,
        ]);
    }

    /**
     * 导入到 studio：channel_id 列有值的落在那个 channel（必须是这个 studio 的、你能写的），
     * 没有的是 studio 级术语（要 language 列）。
     *
     * @return array{created: int, updated: int, unchanged: int, reindexed: bool, errors: list<array{row: int, message: string}>}
     *
     * @throws AuthorizationException 不是本人，也不是这个团队的成员；或是 AI 模型
     * @throws ValidationException
     */
    public function importToStudio(string $studioUid, string $actorUid, UploadedFile $file): array
    {
        $this->agents->assertHuman($actorUid);
        if (! $this->groups->can($actorUid, $studioUid, GroupAbilityService::READ)) {
            throw new AuthorizationException(__('messages.channel_studio_forbidden'));
        }
        $channels = [];

        return $this->import($actorUid, $file, function (array $row) use ($studioUid, $actorUid, &$channels) {
            $channelUid = $row['channel_id'] ?? null;
            if ($channelUid === null) {
                $language = $row['language'] ?? null;
                if ($language === null) {
                    throw new TermImportRowError(__('messages.term_import_language_required'));
                }

                return [
                    'scope' => DhammaTerm::query()->where('owner', $studioUid)->studioLevel()->where('language', $language),
                    'target' => ['channel' => null, 'owner' => $studioUid, 'language' => $language],
                    'language_editable' => true,
                ];
            }
            $channel = $channels[$channelUid] ??= Channel::where('uid', $channelUid)->first(['uid', 'owner_uid', 'lang', 'status', 'is_system']);
            if ($channel === null || $channel->owner_uid !== $studioUid) {
                throw new TermImportRowError(__('messages.term_import_channel_not_in_studio', ['channel' => $channelUid]));
            }
            if (! $this->channels->abilities($channel, $actorUid)['write']) {
                throw new TermImportRowError(__('messages.term_forbidden'));
            }

            return [
                'scope' => DhammaTerm::query()->where('channal', $channel->uid),
                'target' => ['channel' => $channel->uid, 'owner' => $studioUid, 'language' => $channel->lang],
                'language_editable' => false,
            ];
        });
    }

    /**
     * @param  callable(array<string, string|null>): array{scope: Builder<DhammaTerm>, target: array{channel: string|null, owner: string, language: string}, language_editable: bool}  $placeOf
     * @return array{created: int, updated: int, unchanged: int, reindexed: bool, errors: list<array{row: int, message: string}>}
     */
    private function import(string $actorUid, UploadedFile $file, callable $placeOf): array
    {
        $rows = $this->readRows($file);
        $actor = ['editor' => $actorUid, 'co_editor' => null, 'scope' => null];
        $result = ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'reindexed' => false, 'errors' => []];
        $written = [];

        foreach ($rows as $line => $row) {
            try {
                $outcome = $this->importRow($actor, $row, $placeOf($row));
                $result[$outcome['status']]++;
                if ($outcome['term'] !== null) {
                    $written[] = $outcome['term'];
                }
            } catch (TermImportRowError|BusinessException $e) {
                $result['errors'][] = ['row' => $line, 'message' => $e->getMessage()];
            } catch (ValidationException $e) {
                $result['errors'][] = ['row' => $line, 'message' => collect($e->errors())->flatten()->implode(' ')];
            } catch (AuthorizationException $e) {
                $result['errors'][] = ['row' => $line, 'message' => $e->getMessage() ?: __('messages.term_forbidden')];
            }
        }

        if (count($written) <= self::REINDEX_LIMIT) {
            foreach ($written as $term) {
                $this->terms->afterWrite($term);
            }
            $result['reindexed'] = true;
        }

        return $result;
    }

    /**
     * 一行：新建、修改或没变化。出错抛异常，由调用方记进 errors。
     *
     * @param  array{editor: string, co_editor: null, scope: null}  $actor
     * @param  array<string, string|null>  $row
     * @param  array{scope: Builder<DhammaTerm>, target: array{channel: string|null, owner: string, language: string}, language_editable: bool}  $place
     * @return array{status: 'created'|'updated'|'unchanged', term: DhammaTerm|null}
     */
    private function importRow(array $actor, array $row, array $place): array
    {
        $word = $row['word'] ?? null;
        if ($word === null) {
            throw new TermImportRowError(__('messages.term_import_word_required'));
        }
        $tag = $this->terms->normalizeTag($row['tag'] ?? null);

        if (($row['id'] ?? null) !== null) {
            $term = (clone $place['scope'])->where('guid', $row['id'])->first();
            if ($term === null) {
                throw new TermImportRowError(__('messages.term_import_not_found', ['id' => $row['id']]));
            }
            if ($term->word !== $word || $this->terms->normalizeTag($term->tag) !== $tag) {
                throw new TermImportRowError(__('messages.term_word_immutable'));
            }
            if (($row['etag'] ?? null) !== null && $row['etag'] !== $term->etag()) {
                throw new TermImportRowError(__('messages.term_import_modified'));
            }
        } else {
            $term = $this->terms->findExisting(clone $place['scope'], $word, $tag);
        }

        $changes = $this->contentOf($row, $place['language_editable']);
        $this->terms->guardRedirect($word, $tag, $changes);

        if ($term === null) {
            $this->assertCanCreate($actor['editor'], $place['target']);
            if (($changes['meaning'] ?? null) === null) {
                throw new TermImportRowError(__('messages.term_import_meaning_required'));
            }
            $created = $this->terms->insert($actor, $changes + ['word' => $word, 'tag' => $tag] + $place['target'], index: false);

            return ['status' => 'created', 'term' => $created];
        }

        if (! $this->terms->abilitiesMany(collect([$term]), $actor['editor'])[$term->guid]['update']) {
            throw new TermImportRowError(__('messages.term_forbidden'));
        }
        if (array_key_exists('meaning', $changes) && $changes['meaning'] === null) {
            throw new TermImportRowError(__('messages.term_import_meaning_required'));
        }
        $changes = array_filter($changes, fn ($value, $field) => $value !== $this->current($term, $field), ARRAY_FILTER_USE_BOTH);
        if ($changes === []) {
            return ['status' => 'unchanged', 'term' => null];
        }
        if (array_key_exists('note', $changes) && ! array_key_exists('content_type', $changes)) {
            $changes['content_type'] = $term->effectiveContentType();
        }
        if (! $this->terms->applyChanges($term, $actor, $changes, index: false)) {
            throw new TermImportRowError(__('messages.term_import_modified'));
        }

        return ['status' => 'updated', 'term' => $term->fresh()];
    }

    /**
     * 表里有的内容列 → 要写的值（空格子 = 清空）。quality / content_type 校验取值。
     *
     * @param  array<string, string|null>  $row
     * @return array<string, string|null>
     */
    private function contentOf(array $row, bool $languageEditable): array
    {
        $changes = array_intersect_key($row, array_flip(self::CONTENT));
        if ($languageEditable && array_key_exists('language', $row) && $row['language'] !== null) {
            $changes['language'] = $row['language'];
        }
        if (($changes['quality'] ?? null) !== null && ! in_array($changes['quality'], DhammaTerm::QUALITIES, true)) {
            throw new TermImportRowError(__('messages.term_import_bad_value', ['field' => 'quality', 'value' => $changes['quality']]));
        }
        if (array_key_exists('content_type', $changes)) {
            if ($changes['content_type'] === null) {
                unset($changes['content_type']);
            } elseif (! in_array($changes['content_type'], DhammaTerm::CONTENT_TYPES, true)) {
                throw new TermImportRowError(__('messages.term_import_bad_value', ['field' => 'content_type', 'value' => $changes['content_type']]));
            }
        }

        return $changes;
    }

    private function current(DhammaTerm $term, string $field): ?string
    {
        $value = $field === 'content_type' ? $term->effectiveContentType() : $term->getAttribute($field);

        return $value === null || $value === '' ? null : (string) $value;
    }

    /**
     * @param  array{channel: string|null, owner: string, language: string}  $target
     */
    private function assertCanCreate(string $actorUid, array $target): void
    {
        $allowed = $target['channel'] !== null
            ? true   // channel 的写权限在定位时已经查过
            : $this->groups->can($actorUid, $target['owner'], GroupAbilityService::CREATE);
        if (! $allowed) {
            throw new TermImportRowError(__('messages.term_forbidden'));
        }
    }

    /**
     * 读表格：第一行是列名（不分大小写），之后每行按列名取值；整行空的跳过。键是表格里的行号。
     *
     * @return array<int, array<string, string|null>>
     *
     * @throws ValidationException
     */
    private function readRows(UploadedFile $file): array
    {
        try {
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $sheet = $reader->load($file->getRealPath())->getActiveSheet();
        } catch (\Throwable) {
            throw ValidationException::withMessages(['file' => __('messages.term_import_unreadable')]);
        }

        $highestColumn = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        $headers = [];
        for ($col = 1; $col <= $highestColumn; $col++) {
            $name = strtolower(trim((string) $sheet->getCell([$col, 1])->getValue()));
            if (in_array($name, self::COLUMNS, true)) {
                $headers[$col] = $name;
            }
        }
        if (! in_array('word', $headers, true) || ! in_array('meaning', $headers, true)) {
            throw ValidationException::withMessages(['file' => __('messages.term_import_header_required')]);
        }

        $rows = [];
        $highestRow = $sheet->getHighestDataRow();
        if ($highestRow - 1 > self::MAX_ROWS) {
            throw ValidationException::withMessages(['file' => __('messages.term_import_too_many', ['max' => self::MAX_ROWS])]);
        }
        for ($line = 2; $line <= $highestRow; $line++) {
            $row = [];
            foreach ($headers as $col => $name) {
                $value = trim((string) $sheet->getCell([$col, $line])->getValue());
                $row[$name] = $value === '' ? null : $value;
            }
            if (array_filter($row, fn ($value) => $value !== null) === []) {
                continue;
            }
            $rows[$line] = $row;
        }

        return $rows;
    }
}
