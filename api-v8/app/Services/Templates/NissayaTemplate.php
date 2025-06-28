<?php

namespace App\Services\Templates;

use App\Tools\Tools;

class NissayaTemplate extends AbstractTemplate
{
    public function render(): array
    {

        $pali =  $this->getParam("pali", 1);
        $meaning = $this->getParam("meaning", 2);
        $innerString = "";
        $props = [
            "pali" => $pali,
            "meaning" => $meaning,
            "lang" => $this->options['lang'],
        ];
        switch ($this->options['format']) {
            case 'react':
                $output = [
                    'props' => base64_encode(\json_encode($props)),
                    'html' => $innerString,
                    'tag' => 'span',
                    'tpl' => 'nissaya',
                ];
                break;
            case 'unity':
                $output = [
                    'props' => base64_encode(\json_encode($props)),
                    'tpl' => 'nissaya',
                ];
                break;
            case 'text':
                $output = $pali . '၊' . $meaning;
                break;
            case 'tex':
                $output = $pali . '၊' . $meaning;
                break;
            case 'simple':
                $output = $pali . '၊' . $meaning;
                break;
            case 'prompt':
                $output = Tools::MyToRm($pali) . ':' . $meaning;
                break;
            default:
                $output = $pali . '၊' . $meaning;
                break;
        }
        return $output;
    }
}
