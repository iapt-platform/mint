<?php

namespace App\Services\Template;

class TemplateNode extends ContentNode
{
    public string $name;

    public array $parameters = [];

    public array $children = [];

    public string $raw = '';

    public function __construct(string $name, array $parameters = [], array $children = [], string $raw = '', array $position = [])
    {
        $this->type = 'template';
        $this->name = $name;
        $this->parameters = $parameters;
        $this->children = $children;
        $this->raw = $raw;
        $this->position = $position;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'parameters' => $this->parameters,
            'children' => array_map(fn ($child) => $child->toArray(), $this->children),
            'raw' => $this->raw,
            'position' => $this->position,
        ];
    }
}
