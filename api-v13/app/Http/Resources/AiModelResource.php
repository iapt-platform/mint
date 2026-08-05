<?php

namespace App\Http\Resources;

use App\Http\Api\AiAssistantApi;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiModelResource extends JsonResource
{
    /**
     * 把资源转成数组。
     *
     * 字段白名单：绝不能回落到 parent::toArray()，那会把 key（第三方 API key）
     * 和 system_prompt 一并吐出去，而 index() 的 view=all / view=usable 分支
     * 对任何登录用户可见，等于公开泄漏所有模型的 API key。
     *
     * key / system_prompt 仅在请求者是 owner 本人时附带——dashboard 的模型编辑页
     * （AiModelEdit）需要回填这两个字段。
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'uid' => $this->uid,
            'name' => $this->name,
            'description' => $this->description,
            'url' => $this->url,
            'model' => $this->model,
            'privacy' => $this->privacy,
            'owner_id' => $this->owner_id,
            'editor_id' => $this->editor_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => AiAssistantApi::userInfo($this),
        ];

        if ($this->isRequestedByOwner($request)) {
            $data['key'] = $this->key;
            $data['system_prompt'] = $this->system_prompt;
        }

        return $data;
    }

    /**
     * 请求者是否为本模型的 owner。
     *
     * 结果按请求缓存：index() 一次可返回上千行，逐行解一次 JWT 代价过高。
     */
    private function isRequestedByOwner($request): bool
    {
        if (! $request->attributes->has('auth.current')) {
            $request->attributes->set('auth.current', AuthService::current($request));
        }
        $user = $request->attributes->get('auth.current');

        return $user && $user['user_uid'] === $this->owner_id;
    }
}
