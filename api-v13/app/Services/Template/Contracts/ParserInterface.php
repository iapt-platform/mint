<?php

// ================== 契约接口 ==================

namespace App\Services\Template\Contracts;

use App\Services\Template\ParsedDocument;

interface ParserInterface
{
    public function parse(string $content): ParsedDocument;
}

interface RendererInterface
{
    public function render(ParsedDocument $document): string;
}
