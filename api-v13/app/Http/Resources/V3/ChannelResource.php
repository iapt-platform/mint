<?php

namespace App\Http\Resources\V3;

use App\Http\Resources\Concerns\ResolvesActor;
use App\Models\Channel;
use Illuminate\Http\Request;

/**
 * 一个 channel（译文集，界面上叫「版本风格」）。
 *
 * `owner` 是拥有者：个人（kind=user）或团队（kind=team，团队空间里建的）。
 * `abilities` 是**当前查看者**能做什么，前端据此显示按钮。`progress` 只在列表带了 book + para 时出现
 * （该章的完成度，没有进度为 null）。**本类不查库**：拥有者来自预加载，abilities / progress 由 Service 挂上。
 *
 * 字段类型取自 channels 表：type / source_type / source_id 都是 varchar。
 *
 * @mixin Channel
 */
class ChannelResource extends BaseResource
{
    use ResolvesActor;

    /**
     * @param  Request  $request
     * @return array{
     *     id: string,
     *     name: string,
     *     summary: string|null,
     *     type: string,
     *     lang: string,
     *     status: int,
     *     is_system: bool,
     *     source_type: string,
     *     source_id: string|null,
     *     owner: array{kind: string, id: string, nickName: string, realName: string, avatar: string|null},
     *     abilities: array{read: bool, write: bool, update: bool, delete: bool},
     *     progress?: float|null,
     *     created_at: string,
     *     updated_at: string
     * }
     */
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->uid,
            'name' => $this->name,
            'summary' => $this->summary,
            'type' => $this->type,
            'lang' => $this->lang,
            'status' => (int) $this->status,
            'is_system' => (bool) $this->is_system,
            'source_type' => $this->source_type,
            'source_id' => $this->source_id,
            'owner' => $this->ownerSummary(),
            'abilities' => $this->getAttribute('abilities'),
            'progress' => $this->when(
                array_key_exists('progress', $this->resource->getAttributes()),
                fn () => $this->progress === null ? null : (float) $this->progress,
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * @return array{kind: string, id: string, nickName: string, realName: string, avatar: string|null}
     */
    private function ownerSummary(): array
    {
        if ($this->ownerGroup) {
            return [
                'kind' => 'team',
                'id' => $this->ownerGroup->uid,
                'nickName' => $this->ownerGroup->name,
                'realName' => $this->ownerGroup->uid,
                'avatar' => null,
            ];
        }
        $user = $this->actor($this->owner);

        return [
            'kind' => 'user',
            'id' => (string) ($user['id'] ?? $this->owner_uid),
            'nickName' => (string) ($user['nickName'] ?? ''),
            'realName' => (string) ($user['realName'] ?? ''),
            'avatar' => $user['avatar'] ?? null,
        ];
    }
}
