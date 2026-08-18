<?php

namespace App\Services\Templates;

interface TemplateInterface
{
    public function setParams(array $params): self;

    public function setOptions(array $options): self;

    public function render(): array;
}
