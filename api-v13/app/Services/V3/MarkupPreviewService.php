<?php

namespace App\Services\V3;

use App\Services\Markup\MarkupService;
use App\Services\Markup\RenderOptions;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * 源码预览：把一段还没保存的 markdown（模板生成器、编辑器里写到一半的内容）渲染成节点树。
 *
 * 渲染口径与保存后的阅读页相同（{@see MarkupService}，读模式）。
 */
class MarkupPreviewService
{
    public function __construct(
        private readonly MarkupService $markup,
        private readonly ChannelAbilityService $channels,
    ) {}

    /**
     * 给了 channels 的，每个都要看得见（不存在 404、看不见 403），否则就能借预览读私有 channel 的译文。
     *
     * FIXME: 源码里模板自带的 channel（`{{sent|…|channel=…}}`、`{{65-464-2-27@channel}}`）在渲染器内部取数，
     * 这里拦不到；SentenceLoader 不判可见性，存量的术语 / 批注正文也有同样的口子，要在 Markup 的取数层统一补。
     *
     * @param  array{content: string, content_type?: string, channels?: list<string>, lang?: string}  $input
     * @return array{body: list<array<string, mixed>>}
     *
     * @throws AuthorizationException
     */
    public function preview(array $input, string $userUid, string $defaultLang): array
    {
        $channels = array_values(array_unique($input['channels'] ?? []));
        foreach ($channels as $channel) {
            $this->channels->assertReadable($channel, $userUid);
        }

        $body = $this->markup->render($input['content'], new RenderOptions(
            channels: $channels,
            contentType: $input['content_type'] ?? 'markdown',
            lang: $input['lang'] ?? $defaultLang,
        ));

        return ['body' => is_array($body) ? $body : []];
    }
}
