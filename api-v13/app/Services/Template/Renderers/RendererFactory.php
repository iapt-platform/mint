<?php

namespace App\Services\Template\Renderers;

use App\Services\Template\Contracts\RendererInterface;

// ================== 渲染器工厂 ==================

class RendererFactory
{
    private static array $renderers = [];

    public static function create(string $format): RendererInterface
    {
        if (! isset(self::$renderers[$format])) {
            self::$renderers[$format] = match ($format) {
                'json' => new JsonRenderer,
                'html' => new HtmlRenderer,
                'markdown' => new MarkdownRenderer,
                'text' => new TextRenderer,
                default => throw new \InvalidArgumentException("Unsupported format: $format")
            };
        }

        return self::$renderers[$format];
    }

    public static function getSupportedFormats(): array
    {
        return ['json', 'html', 'markdown', 'text'];
    }
}
