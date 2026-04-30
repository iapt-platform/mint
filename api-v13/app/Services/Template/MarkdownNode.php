<?php

namespace App\Services\Template;


class MarkdownNode extends ContentNode
{
    public string $content;

    public function __construct(string $content, array $position = [])
    {
        $this->type = 'markdown';
        $this->content = $content;
        $this->position = $position;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'content' => $this->content,
            'position' => $this->position
        ];
    }
}
