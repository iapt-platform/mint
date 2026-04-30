<?php

namespace App\Services\Template;


// ================== 模板注册表 ==================

class TemplateRegistry
{
    private array $templates = [];

    public function __construct()
    {
        $this->loadDefaultTemplates();
    }

    private function loadDefaultTemplates(): void
    {
        $this->templates = [
            'note' => [
                'defaultParams' => ['text', 'type'],
                'paramMapping' => [
                    '0' => 'text',
                    '1' => 'type'
                ],
                'defaultValues' => [
                    'type' => 'info'
                ],
                'validation' => [
                    'required' => ['text'],
                    'optional' => ['type', 'title']
                ]
            ],
            'info' => [
                'defaultParams' => ['content'],
                'paramMapping' => [
                    '0' => 'content'
                ],
                'defaultValues' => [],
                'validation' => [
                    'required' => ['content'],
                    'optional' => ['title']
                ]
            ],
            'warning' => [
                'defaultParams' => ['message'],
                'paramMapping' => [
                    '0' => 'message'
                ],
                'defaultValues' => [],
                'validation' => [
                    'required' => ['message'],
                    'optional' => ['title']
                ]
            ]
        ];
    }

    public function registerTemplate(string $name, array $config): void
    {
        $this->templates[$name] = $config;
    }

    public function getTemplate(string $name): ?array
    {
        return $this->templates[$name] ?? null;
    }

    public function hasTemplate(string $name): bool
    {
        return isset($this->templates[$name]);
    }

    public function getParamMapping(string $templateName): array
    {
        return $this->templates[$templateName]['paramMapping'] ?? [];
    }

    public function getDefaultValues(string $templateName): array
    {
        return $this->templates[$templateName]['defaultValues'] ?? [];
    }
}
