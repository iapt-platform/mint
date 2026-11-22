<?php

namespace App\Services\V3;

use App\Exceptions\BusinessException;
use App\Facades\RedisCache;
use App\Models\Channel;
use App\Models\Sentence;
use App\Services\TipitakaCatalogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * 三藏下载：把一个 channel 的一部作品、一个分类或整个 channel 导出成文件。
 *
 * 流程：请求方 request() → 已有同一份文件就直接给；没有就写一条 queued 状态、投递到 RabbitMQ
 * （TipitakaExportQueue：单部作品与打包分两个队列）→ worker `mq:export.tipitaka` 调 run() 生成（TipitakaExportBuilder）→
 * 文件以二进制存进 Redis 集群（RedisCache::putBinary），保留 `mint.tipitaka_export.ttl`。
 *
 * **id 就是内容指纹**：channel + 范围 + 格式 + 模板版本 + 范围内句子的最后修改时间与条数。
 * 所以谁先生成谁就给后来的人用；译文一改，指纹就变，自然重新生成，不需要失效逻辑。
 *
 * 格式：html（单部作品一个文件，打包时加 index.html）、markdown（每章一个文件 + 目录）、
 * epub / docx（pandoc 生成；单部作品直接给文件，打包时一部一个）、offline（mobile 离线包，
 * gzip 的 NDJSON，一行一段，`html` 与 `tipitaka-reading` 的 display 一致；只按作品）。
 */
class TipitakaExportService
{
    public const FORMATS = ['html', 'markdown', 'epub', 'docx', 'offline'];

    /**
     * 模板 / 文件结构改了就加一，让已经生成好的旧文件不再命中。
     */
    public const TEMPLATE_VERSION = 2;

    /**
     * 范围统计（字数、最后修改时间）缓存多久。整个大 channel 求 max(updated_at) 要两秒，
     * 下载页每次打开都算不划算；代价是五分钟内的修改要等缓存过期才会触发重新生成。
     */
    private const STATS_TTL = 300;

    public function __construct(
        private readonly TipitakaCatalogService $catalog,
        private readonly ChannelAbilityService $abilities,
        private readonly TipitakaExportQueue $queue,
        private readonly TipitakaExportBuilder $builder,
    ) {}

    /**
     * 发起一次导出。已有可用的文件，或同一份正在生成，就直接返回它的状态，不重复排队。
     *
     * @param  array{channel: string, work?: int, book?: int, para?: int, category?: int, format: string}  $input
     * @return array<string, mixed> present() 的结果
     */
    public function request(array $input, ?string $userUid): array
    {
        $channel = $input['channel'];
        $this->abilities->assertReadable($channel, $userUid);
        $format = $input['format'];
        $work = $input['work'] ?? (isset($input['book']) ? $this->workAt((int) $input['book'], (int) $input['para']) : null);
        $scope = $this->scope($channel, $work, $input['category'] ?? null);
        if ($format === 'offline' && $scope['kind'] !== 'work') {
            throw ValidationException::withMessages(['format' => __('messages.export_offline_work_only')]);
        }

        $spec = $this->spec($channel, $scope, $format);
        $status = $this->find($spec['id']);
        if ($status !== null && $this->reusable($status)) {
            return $this->present($status);
        }

        $status = $spec + ['status' => 'queued', 'progress' => 0.0, 'message' => null, 'updated_at' => now()->toIso8601ZuluString()];
        $this->save($status);
        $this->queue->push($spec['id'], $scope['kind'] !== 'work');

        return $this->present($status);
    }

    /**
     * 下载页用：这个范围四种文件格式各自现在的状态，不排队。范围里没有作品时返回 null。
     *
     * @param  array{kind: string, works: list<array<string, mixed>>, title: string, work?: int, category?: int|null}  $scope
     * @return array<string, array<string, mixed>> 格式 => present()（可下载）或 {id, format, status: absent|stale, filename, too_large}
     */
    public function describe(string $channel, array $scope, ?string $userUid): array
    {
        $this->abilities->assertReadable($channel, $userUid);
        $formats = array_values(array_filter(self::FORMATS, fn ($f) => $f !== 'offline'));
        $result = [];
        foreach ($formats as $format) {
            $spec = $this->spec($channel, $scope, $format, false);
            $status = $this->find($spec['id']);
            $result[$format] = $status !== null && $this->reusable($status)
                ? $this->present($status)
                : [
                    'id' => $spec['id'],
                    'format' => $format,
                    // 失败、失联的按「没有」给：页面上都是点一下重新生成
                    'status' => in_array($status['status'] ?? null, ['queued', 'running'], true) ? 'stale' : 'absent',
                    'filename' => $spec['filename'],
                    'too_large' => $spec['too_large'],
                ];
        }

        return $result;
    }

    /**
     * 查一次导出的状态。不存在（或已过期）404。看不见那个 channel 403。
     *
     * @return array<string, mixed> present() 的结果
     */
    public function show(string $id, ?string $userUid): array
    {
        $status = $this->find($id);
        if ($status === null) {
            abort(404, __('messages.export_not_found'));
        }
        $this->abilities->assertReadable($status['channel'], $userUid);
        if ($status['status'] === 'ready' && ! $this->fileExists($id)) {
            // 文件被 Redis 淘汰了，状态还在：当作没生成过，让客户端重新发起
            abort(404, __('messages.export_not_found'));
        }

        return $this->present($status);
    }

    /**
     * 按 work / category 解析导出范围：哪些作品、叫什么名字。
     *
     * - 给 work：那一部作品，该 channel 没有收录它就 404
     * - 给 category：分类（含后代）下该 channel 收录的作品
     * - 都不给：该 channel 收录的全部作品
     *
     * @return array{kind: string, works: list<array<string, mixed>>, title: string, work?: int, category?: int|null}
     */
    public function scope(string $channel, ?int $work, ?int $category): array
    {
        $progress = $this->catalog->channelProgress($channel);
        $withTitle = function (array $w) use ($progress): array {
            $row = $progress["{$w['book']}-{$w['para']}"] ?? null;

            return $w + [
                'local_title' => ! empty($row['title']) ? $row['title'] : $w['title'],
                'progress' => $row ? (float) $row['progress'] : 0.0,
            ];
        };
        $included = fn (array $w): bool => isset($progress["{$w['book']}-{$w['para']}"]);

        if ($work !== null) {
            $found = $this->catalog->work($work);
            if (! $found || ! $included($found)) {
                abort(404, __('site.not_found'));
            }
            $item = $withTitle($found);

            return ['kind' => 'work', 'work' => $work, 'works' => [$item], 'title' => $item['local_title']];
        }

        if ($category !== null) {
            $node = $this->catalog->category($category);
            if (! $node) {
                abort(404, __('site.not_found'));
            }
            $works = array_values(array_map($withTitle, array_filter($this->catalog->worksInCategory($category), $included)));
            $title = $this->catalog->localNames([$node], TipitakaExportBuilder::locale($this->channelLang($channel)))[$node['name']] ?? $node['name'];
            $kind = 'category';
        } else {
            $works = array_values(array_map($withTitle, array_filter($this->catalog->works(), $included)));
            $title = (string) Channel::where('uid', $channel)->value('name');
            $kind = 'channel';
        }
        if ($works === []) {
            abort(404, __('site.not_found'));
        }
        // 分类 id 是分类树深度优先的流水号：按它排，同一分类的作品挨在一起，律 → 经 → 论 → 藏外
        usort($works, fn ($a, $b) => [$a['category_id'] ?? PHP_INT_MAX, $a['sn']] <=> [$b['category_id'] ?? PHP_INT_MAX, $b['sn']]);

        return ['kind' => $kind, 'category' => $category, 'works' => $works, 'title' => $title];
    }

    /**
     * 段落所在作品的 sn。不在任何作品里 404。
     */
    private function workAt(int $book, int $para): int
    {
        $work = $this->catalog->workAt($book, $para);
        if ($work === null) {
            abort(404, __('site.not_found'));
        }

        return $work['sn'];
    }

    /**
     * worker 入口：生成一份导出并存进 Redis。已经生成好、或状态已过期被清掉的直接跳过。
     */
    public function run(string $id): void
    {
        $status = $this->find($id);
        if ($status === null || ($status['status'] === 'ready' && $this->fileExists($id))) {
            return;
        }
        $status = $this->save(['status' => 'running', 'progress' => 0.0, 'message' => null] + $status);
        $started = microtime(true);

        try {
            $scope = $this->scope($status['channel'], $status['scope']['work'] ?? null, $status['scope']['category'] ?? null);
            $file = $this->builder->build($status, $scope, function (float $progress, string $message) use (&$status): void {
                $status = $this->save(['progress' => round($progress, 3), 'message' => $message] + $status);
            });
            $maxBytes = (int) config('mint.tipitaka_export.max_file_bytes');
            if (strlen($file) > $maxBytes) {
                throw new BusinessException(__('messages.export_too_large'), 422, 'export-too-large');
            }
            $ttl = (int) config('mint.tipitaka_export.ttl');
            RedisCache::putBinary($this->fileKey($id), $file, $ttl);
            $this->save([
                'status' => 'ready',
                'progress' => 1.0,
                'message' => null,
                'size' => strlen($file),
                'ready_at' => now()->toIso8601ZuluString(),
                'expires_at' => now()->addSeconds($ttl)->toIso8601ZuluString(),
            ] + $status, $ttl);
            Log::info('tipitaka-export: ready', ['id' => $id, 'format' => $status['format'], 'scope' => $status['scope'], 'bytes' => strlen($file), 'seconds' => round(microtime(true) - $started, 1)]);
        } catch (Throwable $e) {
            $this->save(['status' => 'failed', 'message' => $e instanceof BusinessException ? $e->getMessage() : __('messages.export_failed')] + $status);
            Log::error('tipitaka-export: failed', ['id' => $id, 'exception' => $e]);
        }
    }

    /**
     * 文件在 Redis 里的 key。下载接口按它分块读。
     */
    public function fileKey(string $id): string
    {
        return "/tipitaka-export/file/{$id}";
    }

    /**
     * 已生成的文件的元数据；没有（或不可下载）返回 null。
     *
     * @return array{filename: string, content_type: string, content_encoding: string|null, size: int}|null
     */
    public function file(string $id): ?array
    {
        $status = $this->find($id);
        $size = RedisCache::binaryLength($this->fileKey($id));
        if ($status === null || $status['status'] !== 'ready' || $size === null) {
            return null;
        }

        return [
            'filename' => $status['filename'],
            'content_type' => $status['content_type'],
            'content_encoding' => $status['format'] === 'offline' ? 'gzip' : null,
            'size' => $size,
        ];
    }

    /**
     * 导出的不变部分：id（内容指纹）、范围、文件名。超过字数上限时 $strict 抛 422，否则标 too_large。
     *
     * @param  array{kind: string, works: list<array<string, mixed>>, title: string, work?: int, category?: int|null}  $scope
     * @return array<string, mixed>
     */
    private function spec(string $channel, array $scope, string $format, bool $strict = true): array
    {
        $stats = $this->stats($channel, $scope);
        $max = (int) config('mint.tipitaka_export.max_source_chars');
        $tooLarge = $stats['chars'] > $max;
        if ($tooLarge && $strict) {
            throw new BusinessException(__('messages.export_too_large'), 422, 'export-too-large', ['chars' => $stats['chars'], 'limit' => $max]);
        }
        $scopeKey = match ($scope['kind']) {
            'work' => ['kind' => 'work', 'work' => $scope['work']],
            'category' => ['kind' => 'category', 'category' => $scope['category']],
            default => ['kind' => 'channel'],
        };
        $id = sha1(json_encode([$channel, $scopeKey, $format, self::TEMPLATE_VERSION, $stats['updated_at'], $stats['count']]));
        $channelName = (string) Channel::where('uid', $channel)->value('name');
        [$filename, $contentType] = $this->filename($scope, $format, $channelName);

        return [
            'id' => $id,
            'channel' => $channel,
            'scope' => $scopeKey,
            'format' => $format,
            'title' => $scope['title'],
            'works' => count($scope['works']),
            'filename' => $filename,
            'content_type' => $contentType,
            'too_large' => $tooLarge,
        ];
    }

    /**
     * 下载时的文件名与 MIME。单部作品的 epub / docx 直接给文件，其余打 zip；离线包是 gzip 的 NDJSON。
     *
     * @param  array{kind: string, works: list<array<string, mixed>>, title: string}  $scope
     * @return array{string, string}
     */
    private function filename(array $scope, string $format, string $channelName): array
    {
        $base = TipitakaExportBuilder::safeName($scope['kind'] === 'channel' ? $channelName : "{$scope['title']}-{$channelName}");
        if ($format === 'offline') {
            // 存的是 gzip 字节，下载时带 Content-Encoding: gzip：客户端的 HTTP 栈
            // （OkHttp / NSURLSession / 浏览器）自己解压，App 不用另装解压库
            return ["{$base}.ndjson", 'application/x-ndjson'];
        }
        if ($scope['kind'] === 'work' && $format === 'epub') {
            return ["{$base}.epub", 'application/epub+zip'];
        }
        if ($scope['kind'] === 'work' && $format === 'docx') {
            return ["{$base}.docx", 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        }

        return ["{$base}-{$format}.zip", 'application/zip'];
    }

    /**
     * 范围内的句子：条数、字数（strlen 之和）、最后修改时间。指纹与大小上限都靠它。
     *
     * @param  array{kind: string, works: list<array<string, mixed>>}  $scope
     * @return array{count: int, chars: int, updated_at: string|null}
     */
    private function stats(string $channel, array $scope): array
    {
        $key = '/tipitaka-export/stats/'.sha1($channel.'|'.json_encode(array_map(fn ($w) => [$w['book'], $w['para'], $w['end']], $scope['works'])));

        return RedisCache::remember($key, self::STATS_TTL, function () use ($channel, $scope) {
            $query = Sentence::query()->where('channel_uid', $channel)->where('strlen', '>', 0);
            if ($scope['kind'] !== 'channel') {
                $query->where(function (Builder $q) use ($scope) {
                    foreach ($scope['works'] as $work) {
                        $q->orWhere(fn (Builder $w) => $w->where('book_id', $work['book'])
                            ->whereBetween('paragraph', [$work['para'], $work['end']]));
                    }
                });
            }
            $row = $query->selectRaw('count(*) as c, coalesce(sum(strlen), 0) as s, max(updated_at) as m')->first();

            return ['count' => (int) $row->c, 'chars' => (int) $row->s, 'updated_at' => $row->m ? (string) $row->m : null];
        });
    }

    /**
     * 已完成且文件还在的，或正在排队 / 生成且没有失联的，可以直接给请求方用。失败的允许重试。
     *
     * @param  array<string, mixed>  $status
     */
    private function reusable(array $status): bool
    {
        return match ($status['status']) {
            'ready' => $this->fileExists($status['id']),
            'queued', 'running' => now()->diffInSeconds($status['updated_at'], true) < (int) config('mint.tipitaka_export.stale_after'),
            default => false,
        };
    }

    /**
     * 对外的状态：去掉内部字段，ready 时附带签名的下载链接（相对地址，反代下不拼错域名；7 天内有效）。
     *
     * @param  array<string, mixed>  $status
     * @return array<string, mixed>
     */
    private function present(array $status): array
    {
        $ready = $status['status'] === 'ready';

        return [
            'id' => $status['id'],
            'status' => $status['status'],
            'format' => $status['format'],
            'progress' => (float) $status['progress'],
            'message' => $status['message'] ?? null,
            'title' => $status['title'],
            'works' => (int) $status['works'],
            'filename' => $status['filename'],
            'content_type' => $status['content_type'],
            'size' => $ready ? (int) $status['size'] : null,
            'ready_at' => $status['ready_at'] ?? null,
            'expires_at' => $status['expires_at'] ?? null,
            'download_url' => $ready ? URL::temporarySignedRoute(
                'v3.tipitaka-exports.file',
                Carbon::parse($status['expires_at']),
                ['export' => $status['id']],
                absolute: false,
            ) : null,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function find(string $id): ?array
    {
        return RedisCache::get($this->statusKey($id));
    }

    /**
     * 写状态，顺带刷新 updated_at（失联判断看它）。返回写入的值。
     *
     * @param  array<string, mixed>  $status
     * @return array<string, mixed>
     */
    private function save(array $status, ?int $ttl = null): array
    {
        $status['updated_at'] = now()->toIso8601ZuluString();
        RedisCache::put($this->statusKey($status['id']), $status, $ttl ?? (int) config('mint.tipitaka_export.ttl'));

        return $status;
    }

    private function fileExists(string $id): bool
    {
        return RedisCache::binaryLength($this->fileKey($id)) !== null;
    }

    private function statusKey(string $id): string
    {
        return "/tipitaka-export/status/{$id}";
    }

    private function channelLang(string $channel): string
    {
        return (string) (Channel::where('uid', $channel)->value('lang') ?? 'en');
    }
}
