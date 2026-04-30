<?php

namespace App\Services\Templates;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\DhammaTerm;
use App\Models\Channel;
use App\Http\Api\ChannelApi;
use App\Http\Api\MdRender;


class TermTemplate extends AbstractTemplate
{
    protected $glossaryKey = 'glossary';

    public function render(): array
    {
        $word = $this->getParam("word", 1);

        $props = $this->getTermProps($word, '');

        $output = $props['word'];
        switch ($this->options['format']) {
            case 'react':
                $output = [
                    'props' => base64_encode(\json_encode($props)),
                    'html' => $props['innerHtml'],
                    'tag' => 'span',
                    'tpl' => 'term',
                ];
                break;
            case 'unity':
                $output = [
                    'props' => base64_encode(\json_encode($props)),
                    'tpl' => 'term',
                ];
                break;
            case 'html':
                if (isset($props["meaning"])) {
                    $GLOBALS[$this->glossaryKey][$props["word"]] = $props['meaning'];

                    $key = 'term-' . $props["word"];
                    $termHead = "<a href='#'>" . $props['meaning'] . "</a>";

                    if (isset($GLOBALS[$key])) {
                        $output = $termHead;
                    } else {
                        $GLOBALS[$key] = 1;
                        $output = $termHead . '(<em>' . $props["word"] . '</em>)';
                    }
                } else {
                    $output = $props["word"];
                }
                break;
            case 'text':
                if (isset($props["meaning"])) {
                    $key = 'term-' . $props["word"];
                    if (isset($GLOBALS[$key])) {
                        $output = $props["meaning"];
                    } else {
                        $GLOBALS[$key] = 1;
                        $output = $props["meaning"] . '(' . $props["word"] . ')';
                    }
                } else {
                    $output = $props["word"];
                }
                break;
            case 'tex':
                if (isset($props["meaning"])) {
                    $key = 'term-' . $props["word"];
                    if (isset($GLOBALS[$key])) {
                        $output = $props["meaning"];
                    } else {
                        $GLOBALS[$key] = 1;
                        $output = $props["meaning"] . '(' . $props["word"] . ')';
                    }
                } else {
                    $output = $props["word"];
                }
                break;
            case 'simple':
                if (isset($props["meaning"])) {
                    $output = $props["meaning"];
                } else {
                    $output = $props["word"];
                }
                break;
            case 'markdown':
                if (isset($props["meaning"])) {
                    $key = 'term-' . $props["word"];
                    if (isset($GLOBALS[$key]) && $GLOBALS[$key] === 1) {
                        $GLOBALS[$key]++;
                        $output = $props["meaning"];
                    } else {
                        $GLOBALS[$key] = 1;
                        $output = $props["meaning"] . '(' . $props["word"] . ')';
                    }
                } else {
                    $output = $props["word"];
                }
                //如果有内容且第一次出现，显示为脚注
                if (!empty($props["note"]) && $GLOBALS[$key] === 1) {
                    if (isset($GLOBALS['note_sn'])) {
                        $GLOBALS['note_sn']++;
                    } else {
                        $GLOBALS['note_sn'] = 1;
                        $GLOBALS['note'] = array();
                    }
                    $content = $props["note"];
                    $output .= '[^' . $GLOBALS['note_sn'] . ']';
                    $GLOBALS['note'][] = [
                        'sn' => $GLOBALS['note_sn'],
                        'trigger' => '',
                        'content' => $content,
                    ];
                }
                break;
            default:
                if (isset($props["meaning"])) {
                    $output = $props["meaning"];
                } else {
                    $output = $props["word"];
                }
                break;
        }
        return $output;
    }

    public function getTermProps($word, $tag = null, $channel = null)
    {
        if ($channel && !empty($channel)) {
            $channelId = $channel;
        } else {
            if (count($this->options['channel_id']) > 0) {
                $channelId = $this->options['channel_id'][0];
            } else {
                $channelId = null;
            }
        }

        if (count($this->options['channelInfo']) === 0) {
            if (!empty($channel)) {
                $channelInfo = Channel::where('uid', $channel)->first();
                if (!$channelInfo) {
                    unset($channelInfo);
                }
            }
            if (!isset($channelInfo)) {
                Log::warning('channel is null');
                $output = [
                    "word" => $word,
                    'innerHtml' => '',
                ];
                return $output;
            }
        } else {
            $channelInfo = $this->options['channelInfo'][0];
        }

        if (Str::isUuid($channelId)) {
            $lang = Channel::where('uid', $channelId)->value('lang');
            if (!empty($lang)) {
                $langFamily = explode('-', $lang)[0];
            } else {
                $langFamily = 'zh';
            }
            Log::info("term:{$word} 先查属于这个channel 的", 'term');
            Log::info('channel id' . $channelId, 'term');
            $table = DhammaTerm::where("word", $word)
                ->where('channal', $channelId);
            if ($tag && !empty($tag)) {
                $table = $table->where('tag', $tag);
            }
            $tplParam = $table->orderBy('updated_at', 'desc')
                ->first();
            $studioId = $channelInfo->owner_uid;
        } else {
            $tplParam = false;
            $lang = '';
            $langFamily = '';
            $studioId = $this->options['studioId'];
        }

        if (!$tplParam) {
            if (Str::isUuid($studioId)) {
                /**
                 * 没有，再查这个studio的
                 * 按照语言过滤
                 * 完全匹配的优先
                 * 语族匹配也行
                 */
                Log::debug("没有-再查这个studio的", 'term');
                $table = DhammaTerm::where("word", $word);
                if (!empty($tag)) {
                    $table = $table->where('tag', $tag);
                }
                $termsInStudio = $table->where('owner', $channelInfo->owner_uid)
                    ->orderBy('updated_at', 'desc')
                    ->get();
                if (count($termsInStudio) > 0) {
                    $list = array();
                    foreach ($termsInStudio as $key => $term) {
                        if (empty($term->channal)) {
                            if ($term->language === $lang) {
                                $list[$term->guid] = 2;
                            } else if (strpos($term->language, $langFamily) !== false) {
                                $list[$term->guid] = 1;
                            }
                        }
                    }
                    if (count($list) > 0) {
                        arsort($list);
                        foreach ($list as $key => $one) {
                            foreach ($termsInStudio as $term) {
                                if ($term->guid === $key) {
                                    $tplParam = $term;
                                    break;
                                }
                            }
                            break;
                        }
                    }
                }
            }
        }

        if (!$tplParam) {
            Log::debug("没有，再查社区", 'term');
            $community_channel = ChannelApi::getSysChannel("_community_term_zh-hans_");
            $table = DhammaTerm::where("word", $word);
            if (!empty($tag)) {
                $table = $table->where('tag', $tag);
            }
            $tplParam = $table->where('channal', $community_channel)
                ->first();
            if ($tplParam) {
                $isCommunity = true;
            } else {
                Log::debug("查社区没有", 'term');
            }
        }
        $output = [
            "word" => $word,
            "parentChannelId" => $channelId,
            "parentStudioId" => $channelInfo->owner_uid,
        ];
        $innerString = $output["word"];
        if ($tplParam) {
            $output["id"] = $tplParam->guid;
            $output["meaning"] = $tplParam->meaning;
            $output["channel"] = $tplParam->channal;
            if (!empty($tplParam->note)) {
                $mdRender = new MdRender(['format' => $this->options['format']]);
                $output['note'] = $mdRender->convert($tplParam->note, $this->options['channel_id']);
            }
            if (isset($isCommunity)) {
                $output["isCommunity"] = true;
            }
            $innerString = "{$output["meaning"]}({$output["word"]})";
            if (!empty($tplParam->other_meaning)) {
                $output["meaning2"] = $tplParam->other_meaning;
            }
        }
        $output['innerHtml'] = $innerString;
        return $output;
    }
}
