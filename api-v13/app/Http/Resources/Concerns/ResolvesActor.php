<?php

namespace App\Http\Resources\Concerns;

use App\Services\UserService;

/**
 * 给 Resource 用的操作者解析。
 *
 * 「操作者」在这个系统里可能是人类（`user_infos`）也可能是 AI 助手（`ai_models`），
 * 共用一个 uuid 空间且没有类型判别列，所以每个模型都要挂两条 belongsTo，
 * Resource 再从两者里择一。这个 trait 把「择一」那步收成一行：
 *
 *     use ResolvesActor;
 *
 *     'user' => $this->actor($this->user, $this->aiModel),
 *
 * 配套的预加载声明用 {@see UserService::eagerLoadActor()}，两边都别手抄列名。
 *
 * **前提是关系已经预加载。** 没 `->with()` 就每行懒加载，又回到 N+1；
 * `ReactionV3Test` 里那条「查询数与行数无关」的断言就是守这个的。
 */
trait ResolvesActor
{
    /**
     * 从两条已预加载的关系里定出操作者摘要；都为空时给 unknown 占位。
     *
     * Resource 的构造签名由 JsonResource 定死，注入不进来，只能在这里解析容器。
     * UserService 注册为 singleton，反复解析拿到的是同一个实例，不会丢身份映射。
     *
     * @param  mixed  $human  一行 `user_infos`，没有传 null
     * @param  mixed  $assistant  一行 `ai_models`，没有传 null
     * @return array<string, mixed>
     */
    protected function actor($human, $assistant = null): array
    {
        return app(UserService::class)->actor($human, $assistant);
    }
}
