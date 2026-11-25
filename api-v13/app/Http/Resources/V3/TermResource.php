<?php

namespace App\Http\Resources\V3;

use App\Http\Resources\Concerns\ResolvesActor;
use App\Models\DhammaTerm;
use App\Services\Markup\MarkupService;
use App\Services\V3\TermService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 术语 / 百科词条（`dhamma_terms` 表）。
 *
 * **本类不查库。** 编辑者、协作者、分类从预加载的关系上取；`abilities`、`summary_text`、`body`
 * 由 {@see TermService} 挂上。
 *
 * - `id` 是 guid（不暴露雪花 id）；`channel_id` 为空的是 studio 级术语，`channel` 是它的名字摘要
 * - `summary` / `note` 是源码，方言见 `content_type`；`body` 是渲染后的「摘要 + 正文」节点树（{@see MarkupService}），
 *   单条里有，按词查带 `with_body` 时每条也有；单条带 `format=html` 时换成 HTML 字符串 `html`（给 WebView 显示的客户端）
 * - `summary_text` 是纯文本摘要：有 summary 用它，没有取正文第一段
 * - `categories` 取自正文的 `{{category|…}}`
 * - `etag` 是乐观锁的版本标记：修改时原样放进 `If-Match` 请求头（单条响应也带 `ETag` 头）
 *
 * @mixin DhammaTerm
 */
class TermResource extends BaseResource
{
    use ResolvesActor;

    /**
     * @return array{
     *     id: string,
     *     word: string,
     *     tag: string|null,
     *     meaning: string,
     *     other_meaning: string|null,
     *     language: string,
     *     channel_id: string|null,
     *     channel: array{id: string, name: string}|null,
     *     owner_id: string,
     *     quality: string|null,
     *     content_type: string,
     *     summary: string|null,
     *     note: string|null,
     *     redirect: string|null,
     *     categories: list<string>,
     *     summary_text: string,
     *     body?: list<MarkupNode>,
     *     html?: string,
     *     editor: array{id: string, nickName: string, userName: string, realName: string, sn: int, avatar: string|null, roles: array|null}|null,
     *     co_editor: array{id: string, nickName: string, userName: string, realName: string, sn: int, avatar: string|null, roles: array|null}|null,
     *     etag: string,
     *     abilities: array{update: bool, delete: bool},
     *     created_at: string,
     *     updated_at: string,
     *     deleted_at: string|null
     * }
     */
    public function toArray(Request $request): array
    {
        $tag = trim((string) $this->tag);

        return [
            'id' => (string) $this->guid,
            'word' => (string) $this->word,
            'tag' => $tag === '' ? null : $tag,
            'meaning' => (string) $this->meaning,
            'other_meaning' => $this->other_meaning,
            'language' => (string) $this->language,
            'channel_id' => $this->channelUid(),
            'channel' => $this->channel ? ['id' => (string) $this->channel->uid, 'name' => (string) $this->channel->name] : null,
            'owner_id' => (string) $this->owner,
            'quality' => $this->quality,
            'content_type' => $this->effectiveContentType(),
            'summary' => $this->summary,
            'note' => $this->note,
            'redirect' => $this->redirect !== '' ? $this->redirect : null,
            'categories' => $this->categories->pluck('category')->values()->all(),
            'summary_text' => (string) ($this->summary_text ?? ''),
            'body' => $this->when(isset($this->body), fn () => $this->body),
            'html' => $this->when(isset($this->html), fn () => $this->html),
            'editor' => $this->actor($this->user, $this->aiModel),
            'co_editor' => $this->coEditor ? $this->actor(null, $this->coEditor) : null,
            'etag' => $this->etag(),
            'abilities' => [
                'update' => (bool) ($this->abilities['update'] ?? false),
                'delete' => (bool) ($this->abilities['delete'] ?? false),
            ],
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
            'deleted_at' => $this->deleted_at?->toIso8601ZuluString(),
        ];
    }

    /**
     * 单条响应带 ETag 头（列表不带）。
     */
    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->header('ETag', $this->etag());
    }
}
