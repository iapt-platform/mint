<?php

// ================== 契约接口 ==================

namespace App\Services\Template\Contracts;

interface ParserInterface
{
    public function parse(string $content): \App\Services\Template\ParsedDocument;
}

interface RendererInterface
{
    public function render(\App\Services\Template\ParsedDocument $document): string;
}
