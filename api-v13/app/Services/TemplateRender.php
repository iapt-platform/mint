<?php

namespace App\Services;

use App\Services\Templates\ConfidenceTemplate;
use App\Services\Templates\NissayaTemplate;
use App\Services\Templates\NoteTemplate;
use App\Services\Templates\TemplateInterface;
use App\Services\Templates\TermTemplate;
use InvalidArgumentException;

class TemplateRender
{
    protected string $templateName;

    protected array $params = [];

    protected TemplateInterface $template;

    // 定义默认公共参数
    protected array $options = [
        'format' => 'react', // 默认格式为 react
    ];

    public static function name(string $templateName): self
    {
        $instance = new self;
        $instance->templateName = $templateName;

        return $instance;
    }

    public function param(array $params): self
    {
        $this->params = $params;

        return $this;
    }

    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function render(): array
    {
        // 解析模板类
        $this->resolveTemplate();

        // 设置参数并渲染
        return $this->template
            ->setParams($this->params)
            ->setOptions($this->options)
            ->render();
    }

    protected function resolveTemplate(): void
    {
        // 模板名称到类的映射（可以用配置文件替代）
        $templateMap = [
            'cf' => ConfidenceTemplate::class,
            'nissaya' => NissayaTemplate::class,
            'term' => TermTemplate::class,
            'note' => NoteTemplate::class,
        ];

        if (! isset($templateMap[$this->templateName])) {
            throw new InvalidArgumentException("Template {$this->templateName} not found.");
        }

        // 通过服务容器解析模板类
        $this->template = app($templateMap[$this->templateName]);
    }
}
