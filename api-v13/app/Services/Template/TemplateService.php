<?php

namespace App\Services\Template;

use App\Services\Template\Renderers\RendererFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TemplateService
{
    private TemplateParser $parser;

    private bool $cacheEnabled;

    private int $cacheTtl;

    public function __construct(bool $cacheEnabled = true, int $cacheTtl = 3600)
    {
        $this->parser = new TemplateParser;
        $this->cacheEnabled = $cacheEnabled;
        $this->cacheTtl = $cacheTtl;
    }

    /**
     * 解析并渲染内容
     */
    public function parseAndRender(string $content, string $format = 'json'): array
    {
        try {
            // 生成缓存键
            $cacheKey = $this->generateCacheKey($content, $format);

            if ($this->cacheEnabled && Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            // 解析内容
            $document = $this->parser->parse($content);

            // 渲染内容
            $renderer = RendererFactory::create($format);
            $renderedContent = $renderer->render($document);

            $result = [
                'data' => $format === 'json' ? json_decode($renderedContent, true) : $renderedContent,
                'meta' => $document->meta,
            ];

            // 缓存结果
            if ($this->cacheEnabled) {
                Cache::put($cacheKey, $result, $this->cacheTtl);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Template parsing failed', [
                'content' => substr($content, 0, 200),
                'format' => $format,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * 仅解析，不渲染
     */
    public function parse(string $content): ParsedDocument
    {
        return $this->parser->parse($content);
    }

    /**
     * 仅渲染已解析的文档
     */
    public function render(ParsedDocument $document, string $format): string
    {
        $renderer = RendererFactory::create($format);

        return $renderer->render($document);
    }

    /**
     * 注册新模板
     */
    public function registerTemplate(string $name, array $config): void
    {
        $registry = $this->parser->getRegistry();
        $registry->registerTemplate($name, $config);

        // 清除相关缓存
        if ($this->cacheEnabled) {
            $this->clearTemplateCache($name);
        }
    }

    /**
     * 获取可用模板列表
     */
    public function getAvailableTemplates(): array
    {
        $registry = $this->parser->getRegistry();
        $templates = [];

        // 这里需要添加一个方法来获取所有模板
        // 为了示例，我们返回一些基本信息
        $basicTemplates = ['note', 'info', 'warning'];

        foreach ($basicTemplates as $templateName) {
            $template = $registry->getTemplate($templateName);
            if ($template) {
                $templates[$templateName] = [
                    'name' => $templateName,
                    'config' => $template,
                    'example' => $this->generateTemplateExample($templateName, $template),
                ];
            }
        }

        return $templates;
    }

    /**
     * 生成模板使用示例
     */
    private function generateTemplateExample(string $name, array $config): string
    {
        $params = [];
        $defaultParams = $config['defaultParams'] ?? [];

        foreach ($defaultParams as $index => $paramName) {
            $params[] = $paramName.'=示例值'.($index + 1);
        }

        return '{{'.$name.'|'.implode('|', $params).'}}';
    }

    /**
     * 生成缓存键
     */
    private function generateCacheKey(string $content, string $format): string
    {
        return 'template_'.$format.'_'.md5($content);
    }

    /**
     * 清除模板相关缓存
     */
    private function clearTemplateCache(string $templateName): void
    {
        // 这里可以实现更精确的缓存清除逻辑
        Cache::flush(); // 简单起见，清除所有缓存
    }

    /**
     * 批量处理内容
     */
    public function batchProcess(array $contents, string $format = 'json'): array
    {
        $results = [];

        foreach ($contents as $index => $content) {
            try {
                $results[$index] = $this->parseAndRender($content, $format);
            } catch (\Exception $e) {
                $results[$index] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * 验证模板语法
     */
    public function validateSyntax(string $content): array
    {
        $errors = [];

        try {
            $document = $this->parser->parse($content);

            // 检查是否有未知模板
            foreach ($document->content as $node) {
                if ($node instanceof TemplateNode) {
                    $registry = $this->parser->getRegistry();
                    if (! $registry->hasTemplate($node->name)) {
                        $errors[] = [
                            'type' => 'unknown_template',
                            'template' => $node->name,
                            'position' => $node->position,
                            'message' => "Unknown template: {$node->name}",
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            $errors[] = [
                'type' => 'parse_error',
                'message' => $e->getMessage(),
            ];
        }

        return $errors;
    }
}
