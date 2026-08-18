<?php

namespace App\Services\Templates;

abstract class AbstractTemplate implements TemplateInterface
{
    protected array $params = [];

    protected array $options = [];

    public function setParams(array $params): self
    {
        $this->params = $params;

        return $this;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    protected function getParam(string $name, int $id, string $default = ''): string
    {
        if (isset($this->params[$name])) {
            return trim($this->params[$name]);
        } elseif (isset($this->params["{$id}"])) {
            return trim($this->params["{$id}"]);
        } else {
            return $default;
        }
    }

    abstract public function render(): array;
}
