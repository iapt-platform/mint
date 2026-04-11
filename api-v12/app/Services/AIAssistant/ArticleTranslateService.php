<?php

namespace App\Services\AIAssistant;

use App\Services\ArticleService;
use App\Services\PaliContentService;
use App\Models\CustomBook;
use Illuminate\Support\Facades\Log;

class ArticleTranslateService
{
    protected ArticleService $articleService;
    protected PaliContentService $paliContentService;
    protected TranslateService $translateService;
    protected string $modelId;
    protected array $translation;

    protected string $systemPrompt = <<<PROMPT
    请根据提供的原文，翻译为简体中文。

    原文为逐句数据，翻译时请依照句子的上下文翻译。
    id:句子编号
    content:内容

    # 翻译要求：
    1. 缅文巴利要给出罗马巴利转写
    2. 使用现代汉语
    3. 逐句翻译



    # 输出格式要求：
    - jsonl 格式
    - 每条记录是一个句子
    - 每个句子只输出两个字段
      1. id(句子编号)
      2. content(译文)
    - 无需输出原文
    - 只输出jsonl格式的译文 无需出处额外的解释
    PROMPT;

    public function __construct(
        ArticleService $article,
        PaliContentService $paliContent,
        TranslateService $translateService,
    ) {
        $this->articleService = $article;
        $this->paliContentService = $paliContent;
        $this->translateService = $translateService;
    }

    /**
     * 设置模型配置
     *
     * @param string $model
     * @return self
     */
    public function setModel(string $model): self
    {
        $this->modelId = $model;
        return $this;
    }

    public function translate(string $articleId)
    {
        //获取文章中的句子id
        $sentenceIds = $this->articleService->sentenceIds($articleId);
        if (!$sentenceIds || count($sentenceIds) === 0) {
            return null;
        }
        $bookId = (int)explode('-', $sentenceIds[0])[0];
        //提取原文
        $originalChannelId = CustomBook::where('book_id', $bookId)->value('channel_id');

        $original = $this->paliContentService->sentences($sentenceIds, [$originalChannelId], 'read');
        $orgData = [];
        foreach ($original as $key => $paragraph) {
            foreach ($paragraph['children'] as $key => $sent) {
                $org = $sent['origin'][0];
                $orgData[] = [
                    'id' => "{$org['book']}-{$org['para']}-{$org['wordStart']}-{$org['wordEnd']}",
                    'content' => !empty($org['content']) ? $org['content'] : $org['html'],
                ];
            }
        }
        //翻译
        $result = $this->translateService->setModel($this->modelId)
            ->setSystemPrompt($this->systemPrompt)
            ->setTranslatePrompt("# 原文\n\n" .
                "```json\n" .
                json_encode($orgData, JSON_UNESCAPED_UNICODE) .
                "\n```")
            ->translate();
        Log::debug('ai translation', ['data' => $result->toArray()['data']]);
        $this->translation = $result->toArray()['data'];
        return $this;
    }
    //写入结果channel
    public function save(string $channelId) {}
    public function get()
    {
        return $this->translation;
    }
}
