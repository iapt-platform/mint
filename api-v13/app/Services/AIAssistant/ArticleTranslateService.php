<?php

namespace App\Services\AIAssistant;

use App\Http\Api\ChannelApi;
use App\Models\CustomBook;
use App\Services\ArticleService;
use App\Services\AuthService;
use App\Services\PaliContentService;
use App\Services\SentenceService;
use Illuminate\Support\Facades\Log;

class ArticleTranslateService
{
    protected string $modelId;

    protected string $modelToken;

    protected array $translation = [];

    protected string $outputChannelId;

    protected string $currArticleId;

    protected bool $thinking;

    protected string $systemPrompt = <<<'PROMPT'
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

     # 输出范例
     ```jsonl
    {"id":"1-2-3-4","content":"译文"}
    {"id":"2-3-4-5","content":"译文"}
    ```
    PROMPT;

    public function __construct(
        protected ArticleService $articleService,
        protected PaliContentService $paliContentService,
        protected TranslateService $translateService,
        protected SentenceService $sentenceService
    ) {}

    /**
     * 设置模型配置
     */
    public function setModel(string $model): self
    {
        $this->modelId = $model;
        $this->modelToken = app(AuthService::class)->getUserToken($model);

        return $this;
    }

    /**
     * 设置模型配置
     */
    public function setThinking(bool $thinking): self
    {
        $this->thinking = $thinking;

        return $this;
    }

    /**
     * 设置模型配置
     *
     * @param  string  $model
     */
    public function setChannel(string $id): self
    {
        $this->outputChannelId = $id;

        return $this;
    }

    public function getCurrArticleId()
    {
        return $this->currArticleId;
    }

    public function translateAnthology(string $anthologyId, ?callable $onEach = null): int
    {
        $articleIds = $this->articleService->articlesInAnthology($anthologyId);

        foreach ($articleIds as $article) {
            $this->translateArticle($article);
            if ($onEach) {
                $onEach($this);
            }
        }

        return count($articleIds);
    }

    public function translateArticle(string $articleId)
    {
        $this->currArticleId = $articleId;
        // 获取文章中的句子id
        $sentenceIds = $this->articleService->sentenceIds($articleId);
        if (! $sentenceIds || count($sentenceIds) === 0) {
            $this->translation = [];

            return $this;
        }
        $bookId = (int) explode('-', $sentenceIds[0])[0];
        // 提取原文
        $originalChannelId = CustomBook::where('book_id', $bookId)->value('channel_id');

        $original = $this->paliContentService->sentences($sentenceIds, [$originalChannelId], 'read');
        $orgData = [];
        foreach ($original as $key => $paragraph) {
            foreach ($paragraph['children'] as $key => $sent) {
                $org = $sent['origin'][0];
                $orgData[] = [
                    'id' => "{$org['book']}-{$org['para']}-{$org['wordStart']}-{$org['wordEnd']}",
                    'content' => ! empty($org['content']) ? $org['content'] : $org['html'],
                ];
            }
        }
        // 翻译
        $result = $this->translateService->setModel($this->modelId)
            ->setSystemPrompt($this->systemPrompt)
            ->setTranslatePrompt("# 原文\n\n".
                "```json\n".
                json_encode($orgData, JSON_UNESCAPED_UNICODE).
                "\n```")
            ->translate();
        Log::debug('ai translation', ['data' => $result->toArray()['data']]);
        $this->translation = $result->toArray()['data'];

        return $this;
    }

    // 写入结果channel
    public function save()
    {
        if (
            ! is_array($this->translation) ||
            count($this->translation) === 0
        ) {
            return 0;
        }
        $channelInfo = ChannelApi::getById($this->outputChannelId);
        $sentData = [];
        $sentData = array_map(function ($n) use ($channelInfo) {
            $sId = explode('-', $n['id']);

            return [
                'book_id' => $sId[0],
                'paragraph' => $sId[1],
                'word_start' => $sId[2],
                'word_end' => $sId[3],
                'channel_uid' => $channelInfo['id'],
                'content' => $n['content'],
                'content_type' => $n['content_type'] ?? 'markdown',
                'lang' => $channelInfo['lang'],
                'status' => $channelInfo['status'],
                'editor_uid' => $this->modelId,
            ];
        }, $this->translation);
        foreach ($sentData as $value) {
            $this->sentenceService->save($value);
        }

        return count($sentData);
    }

    public function saveRpc(string $endpoint, string $accessToken)
    {
        if (
            ! is_array($this->translation) ||
            count($this->translation) === 0
        ) {
            return 0;
        }
        $channelInfo = ChannelApi::getById($this->outputChannelId);
        $sentData = [];
        $sentData = array_map(function ($n) use ($channelInfo, $accessToken) {
            $sId = explode('-', $n['id']);

            return [
                'book_id' => $sId[0],
                'paragraph' => $sId[1],
                'word_start' => $sId[2],
                'word_end' => $sId[3],
                'channel_uid' => $channelInfo['id'],
                'content' => $n['content'],
                'content_type' => $n['content_type'] ?? 'markdown',
                'access_token' => $accessToken,
            ];
        }, $this->translation);
        foreach ($sentData as $value) {
            $this->sentenceService->saveRpc($endpoint, $value, $this->modelToken);
        }

        return count($sentData);
    }

    public function get()
    {
        return $this->translation;
    }
}
