<?php

namespace App\Http\Resources\V3;

use App\Http\Resources\Concerns\ResolvesActor;
use App\Models\Discussion;
use App\Services\V3\DiscussionService;
use Illuminate\Http\Request;

/**
 * 一条评论：话题（parent_id 为 null）或回复；或一条批注（note / commentary）。
 *
 * 批注与 issue / glossary 可带锚点（pos_start / pos_end / quote_*），指明它挂在句子文本的
 * 哪一段；其它评论线程的这几个字段为 null。glossary 的 title 是术语词头。
 *
 * 删除了但还挂着回复的话题以「墓碑」出现：deleted_at 有值，title / content 为 null，
 * 回复照常可读（与 Google Drive 的 comments.deleted、GitHub 的 deletedAt 同一做法）。
 *
 * editor 是对内容负责的人；AI 凭人类授权代写时，co_editor 是那个模型（仿 git 的
 * Co-Authored-By）。存量数据里 AI 写的行没有 co_editor，editor 直接是模型。
 *
 * **本类不查库。** 编辑者从预加载的 `user` / `aiModel` / `coEditor` 关系上取；权限与正文渲染
 * 由 {@see DiscussionService} 算好挂在模型的 `abilities` / `rendered` 属性上。
 *
 * `sentence` 只在按经文范围列出时有（`GET /v3/tipitaka-discussions/{channel}`）：所在句子的坐标，
 * 由查询顺带取出（`sentence_*` 属性），本类不另查。
 *
 * 字段名改了两处（v2 → v3）：res_type / res_id → target_type / target_id，
 * parent → parent_id；children_count → reply_count（只有话题有）。
 *
 * @mixin Discussion
 */
class DiscussionResource extends BaseResource
{
    use ResolvesActor;

    /**
     * @param  Request  $request
     * @return array{
     *     id: string,
     *     target_type: string,
     *     target_id: string,
     *     parent_id: string|null,
     *     type: string,
     *     severity: string|null,
     *     status: string,
     *     title: string|null,
     *     content: string|null,
     *     content_type: string,
     *     html?: string,
     *     summary?: string,
     *     tpl_id: string|null,
     *     pos_start: int|null,
     *     pos_end: int|null,
     *     quote_exact: string|null,
     *     quote_prefix: string|null,
     *     quote_suffix: string|null,
     *     reply_count?: int,
     *     sentence?: array{book: int, paragraph: int, word_start: int, word_end: int},
     *     editor: array{id: string, nickName: string, userName: string, realName: string, sn: int, avatar: string|null, roles: array|null}|null,
     *     co_editor: array{id: string, nickName: string, userName: string, realName: string, sn: int, avatar: string|null, roles: array|null}|null,
     *     abilities: array{update: bool, delete: bool, close: bool, reply: bool},
     *     created_at: string,
     *     updated_at: string,
     *     deleted_at: string|null
     * }
     */
    public function toArray($request): array
    {
        $tombstone = $this->trashed();

        return [
            'id' => (string) $this->id,
            'target_type' => $this->res_type,
            'target_id' => $this->res_id,
            'parent_id' => $this->parent,
            'type' => $this->type,
            'severity' => $this->severity,
            'status' => $this->status,
            'title' => $tombstone ? null : $this->title,
            'content' => $tombstone ? null : $this->content,
            'content_type' => $this->content_type,
            'html' => $this->when($this->rendered !== null, fn () => $this->rendered['html']),
            'summary' => $this->when($this->rendered !== null, fn () => $this->rendered['summary']),
            'tpl_id' => $this->tpl_id,
            'pos_start' => $this->pos_start,
            'pos_end' => $this->pos_end,
            'quote_exact' => $tombstone ? null : $this->quote_exact,
            'quote_prefix' => $tombstone ? null : $this->quote_prefix,
            'quote_suffix' => $tombstone ? null : $this->quote_suffix,
            'reply_count' => $this->when($this->parent === null, fn () => (int) $this->reply_count),
            'sentence' => $this->when(isset($this->sentence_book), fn () => [
                'book' => (int) $this->sentence_book,
                'paragraph' => (int) $this->sentence_paragraph,
                'word_start' => (int) $this->sentence_word_start,
                'word_end' => (int) $this->sentence_word_end,
            ]),
            'editor' => $this->actor($this->user, $this->aiModel),
            // AI 代写时的模型；人类自己写的、存量数据都为 null
            'co_editor' => $this->coEditor ? $this->actor(null, $this->coEditor) : null,
            'abilities' => $this->abilities ?? ['update' => false, 'delete' => false, 'close' => false, 'reply' => false],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
