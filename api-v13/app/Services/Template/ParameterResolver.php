<?php

namespace App\Services\Template;

// ================== 参数解析器 ==================

class ParameterResolver
{
    private TemplateRegistry $registry;

    public function __construct(TemplateRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function resolveParameters(string $templateName, array $rawParams): array
    {
        $template = $this->registry->getTemplate($templateName);
        if (! $template) {
            return $rawParams;
        }

        $resolved = [];
        $paramMapping = $template['paramMapping'] ?? [];
        $defaultValues = $template['defaultValues'] ?? [];

        // 处理位置参数
        $positionIndex = 0;
        foreach ($rawParams as $key => $value) {
            if (is_numeric($key)) {
                // 位置参数
                $paramName = $paramMapping[$positionIndex] ?? $positionIndex;
                $resolved[$paramName] = $value;
                $positionIndex++;
            } else {
                // 命名参数
                $resolved[$key] = $value;
            }
        }

        // 应用默认值
        foreach ($defaultValues as $key => $value) {
            if (! isset($resolved[$key])) {
                $resolved[$key] = $value;
            }
        }

        return $resolved;
    }
}
