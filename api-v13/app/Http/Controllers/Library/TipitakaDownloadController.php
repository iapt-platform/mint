<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Services\TipitakaCatalogService;
use App\Services\V3\TipitakaExportService;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

/**
 * library 三藏的下载页：一部作品，或整个 channel 打包。
 *
 * 页面只负责展示四种格式现在的状态（已就绪的直接给链接）；点击后由页面里的脚本调
 * `POST /api/v3/tipitaka-exports` 发起、轮询 `GET /api/v3/tipitaka-exports/{id}`，就绪后自动下载。
 */
class TipitakaDownloadController extends Controller
{
    /**
     * 下载页上的格式及顺序。离线包（offline）是给 mobile 的，不在网页上出现。
     */
    private const FORMATS = ['epub', 'html', 'docx', 'markdown'];

    public function __construct(
        private readonly TipitakaCatalogService $catalog,
        private readonly TipitakaExportService $exports,
    ) {}

    public function work(string $channel, int $sn): View
    {
        $channelModel = $this->catalog->channel($channel);
        abort_unless($channelModel, 404);
        $scope = $this->exports->scope($channel, $sn, null);
        $work = $scope['works'][0];
        $progress = $this->catalog->channelProgress($channel)["{$work['book']}-{$work['para']}"] ?? null;
        $channelInfo = $this->channelInfo($channelModel);

        return view('library.tipitaka.download', [
            'mode' => 'work',
            'channelInfo' => $channelInfo,
            'book' => $this->catalog->bookCard($work, $channelInfo, $progress) + ['pali_title' => $work['title'], 'updated_at' => $progress['updated_at'] ?? null],
            'request' => ['channel' => $channel, 'work' => $sn],
            'formats' => $this->formats($this->exports->describe($channel, $scope, null), true),
            'backUrl' => route('library.tipitaka.channels.work', ['channel' => $channel, 'sn' => $sn]),
        ]);
    }

    /**
     * 整个 channel 打包下载。只有「全部」一种范围（用户定：不按藏 / 分类分开下）。
     */
    public function channel(string $channel): View
    {
        $channelModel = $this->catalog->channel($channel);
        abort_unless($channelModel, 404);
        $scope = $this->exports->scope($channel, null, null);
        $names = $this->catalog->localNames($this->catalog->categories(), Cookie::get('language') ?? 'en');

        $groups = [];
        foreach ($scope['works'] as $work) {
            $path = $work['category_id'] === null ? [] : $this->catalog->categoryPath($work['category_id']);
            $label = implode(' · ', array_map(fn ($c) => $names[$c['name']] ?? $c['name'], $path)) ?: __('library.export_uncategorized');
            $groups[$label][] = [
                'title' => $work['local_title'],
                'progress' => (int) round($work['progress'] * 100),
                'url' => route('library.tipitaka.channels.work', ['channel' => $channel, 'sn' => $work['sn']]),
            ];
        }

        return view('library.tipitaka.download', [
            'mode' => 'channel',
            'channelInfo' => $this->channelInfo($channelModel),
            'groups' => $groups,
            'request' => ['channel' => $channel],
            'formats' => $this->formats($this->exports->describe($channel, $scope, null), false),
            'backUrl' => route('library.tipitaka.channels.category', ['channel' => $channel]),
        ]);
    }

    /**
     * @return array{uid: string, name: string, type: string, lang: string, owner: mixed}
     */
    private function channelInfo(object $channel): array
    {
        return ['uid' => $channel->uid, 'name' => $channel->name, 'type' => $channel->type, 'lang' => $channel->lang, 'owner' => $channel->owner];
    }

    /**
     * 格式卡片：名字、说明（单部作品 / 打包两种口径）与当前状态。
     *
     * @param  array<string, array<string, mixed>>  $states
     * @return list<array<string, mixed>>
     */
    private function formats(array $states, bool $single): array
    {
        return array_map(fn (string $format) => [
            'key' => $format,
            'name' => __("library.download_format_{$format}"),
            'description' => __($single ? "library.download_format_{$format}_desc" : "library.download_format_{$format}_bundle"),
            'state' => $states[$format],
        ], self::FORMATS);
    }
}
