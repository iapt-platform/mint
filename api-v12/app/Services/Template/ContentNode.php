<?php

namespace App\Services\Template;


abstract class ContentNode
{
    public string $type;
    public string $content;
    public array $position = [];

    abstract public function toArray(): array;
}
