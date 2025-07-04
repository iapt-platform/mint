<?php

namespace App\Services\Template;

use App\Services\Template\Contracts\RendererInterface;
use App\Services\Template\Contracts\ParserInterface;

// ================== 数据结构 ==================

class ParsedDocument
{
    public string $type = 'document';
    public array $content = [];
    public array $meta = [];

    public function __construct(array $content = [], array $meta = [])
    {
        $this->content = $content;
        $this->meta = $meta;
    }
}
