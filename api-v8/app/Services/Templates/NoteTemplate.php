<?php

namespace App\Services\Templates;

use App\Http\Api\MdRender;

class NoteTemplate extends AbstractTemplate
{
    public function __construct() {}

    public function render(): array
    {
        $note = $this->getParam("text", 1);
        $trigger = $this->getParam("trigger", 2);
        $props = ["note" => $note];
        $innerString = "";
        if (!empty($trigger)) {
            $props["trigger"] = $trigger;
            $innerString = $props["trigger"];
        }
        if ($this->options['format'] === 'unity') {
            $props["note"] = MdRender::render(
                $props["note"],
                $this->options['channel_id'],
                null,
                'read',
                'translation',
                'markdown',
                'unity'
            );
        }
        $output = $note;
        switch ($this->options['format']) {
            case 'react':
                $output = [
                    'props' => base64_encode(\json_encode($props)),
                    'html' => $innerString,
                    'tag' => 'span',
                    'tpl' => 'note',
                ];
                break;
            case 'unity':
                $output = [
                    'props' => base64_encode(\json_encode($props)),
                    'tpl' => 'note',
                ];
                break;
            case 'html':
                if (isset($GLOBALS['note_sn'])) {
                    $GLOBALS['note_sn']++;
                } else {
                    $GLOBALS['note_sn'] = 1;
                    $GLOBALS['note'] = array();
                }
                $GLOBALS['note'][] = [
                    'sn' => $GLOBALS['note_sn'],
                    'trigger' => $trigger,
                    'content' => MdRender::render(
                        $props["note"],
                        $this->options['channel_id'],
                        null,
                        'read',
                        'translation',
                        'markdown',
                        'html'
                    ),
                ];

                $link = "<a href='#footnote-" . $GLOBALS['note_sn'] . "' name='note-" . $GLOBALS['note_sn'] . "'>";
                if (empty($trigger)) {
                    $output =  $link . "<sup>[" . $GLOBALS['note_sn'] . "]</sup></a>";
                } else {
                    $output = $link . $trigger . "</a>";
                }
                break;
            case 'text':
                $output = $trigger;
                break;
            case 'tex':
                $output = $trigger;
                break;
            case 'simple':
                $output = '';
                break;
            case 'markdown':
                if (isset($GLOBALS['note_sn'])) {
                    $GLOBALS['note_sn']++;
                } else {
                    $GLOBALS['note_sn'] = 1;
                    $GLOBALS['note'] = array();
                }
                $content = MdRender::render(
                    $props["note"],
                    $this->options['channel_id'],
                    null,
                    'read',
                    'translation',
                    'markdown',
                    'markdown'
                );
                $output = '[^' . $GLOBALS['note_sn'] . ']';
                $GLOBALS['note'][] = [
                    'sn' => $GLOBALS['note_sn'],
                    'trigger' => $trigger,
                    'content' => $content,
                ];
                //$output = '<footnote id="'.$GLOBALS['note_sn'].'">'.$content.'</footnote>';
                break;
            default:
                $output = '';
                break;
        }
        return $output;
    }
}
