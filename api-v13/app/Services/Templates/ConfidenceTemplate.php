<?php

namespace App\Services\Templates;

class ConfidenceTemplate extends AbstractTemplate
{
    public function render(): array
    {
        // 示例：处理 email 模板的渲染逻辑
        $value = $this->getParam('value', 1, '0');

        // 根据 format 调整渲染逻辑
        $props = ['value' => $value];
        $output = [];
        switch ($this->options['format']) {
            case 'react':
                $output = [
                    'props' => base64_encode(\json_encode($props)),
                    'html' => '',
                    'tag' => 'span',
                    'tpl' => 'cf',
                ];
                break;
            case 'unity':
                $output = [
                    'props' => base64_encode(\json_encode($props)),
                    'tpl' => 'cf',
                ];
                break;
            case 'markdown':
                $output = ["`$value`"];
                break;
            case 'html':
                $output = ["<span>{$value}</span>"];
                break;
            default:
                $output = [$value];
                break;
        }

        return $output;
    }
}
