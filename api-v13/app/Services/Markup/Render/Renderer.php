<?php

namespace App\Services\Markup\Render;

use App\Services\Markup\Ir\Document;

interface Renderer
{
    /**
     * @return list<array<string, mixed>>|string tree 返回节点数组，其余返回字符串
     */
    public function document(Document $document, RenderContext $ctx): array|string;
}
