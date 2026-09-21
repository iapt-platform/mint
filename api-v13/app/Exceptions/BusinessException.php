<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 需要自己的 type / title / 扩展字段的业务错误。
 *
 * 普通的 404、403、422 不要用它——直接 abort() 或让 FormRequest 抛
 * ValidationException，bootstrap/app.php 会统一渲染。只有当这个错误
 * 是一种**客户端需要分别处理的业务状态**时才定义 slug，例如：
 *
 *     throw new BusinessException(
 *         __('sentence.locked'), 409, 'sentence-locked',
 *         ['locked_by' => $editor->nickname],
 *     );
 *
 * Laravel 的 Handler 会先看异常有没有 render()，所以这里的实现优先级
 * 最高，不受 bootstrap/app.php 里那些处理器影响。
 */
class BusinessException extends Exception
{
    /**
     * @param  string  $detail  面向人的文案，调用方传已经过 __() 的字符串
     * @param  string  $slug  进 type 字段的问题类型标识
     * @param  array<string, mixed>  $extra  扩展成员
     */
    public function __construct(
        string $detail,
        protected int $status = 409,
        protected string $slug = 'business-error',
        protected array $extra = [],
        protected ?string $problemTitle = null,
    ) {
        parent::__construct($detail, $status);
    }

    public function render(Request $request): ?JsonResponse
    {
        // 只接管 v3；v2 的错误形状在 dashboard-v4 下线前不能变
        if (! $request->is('api/v3/*')) {
            return null;
        }

        return Problem::response(
            $request,
            $this->status,
            $this->slug,
            $this->problemTitle,
            $this->getMessage(),
            $this->extra,
        );
    }
}
