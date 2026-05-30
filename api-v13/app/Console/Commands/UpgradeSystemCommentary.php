<?php

namespace App\Console\Commands;

use App\Helpers\LlmResponseParser;
use App\Http\Api\ChannelApi;
use App\Http\Resources\AiModelResource;
use App\Models\BookTitle;
use App\Models\PaliSentence;
use App\Models\PaliText;
use App\Models\RelatedParagraph;
use App\Models\Tag;
use App\Models\TagMap;
use App\Services\AIModelService;
use App\Services\OpenAIService;
use App\Services\SearchPaliDataService;
use App\Services\SentenceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UpgradeSystemCommentary extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan upgrade:sys.commentary
     *
     * @var string
     */
    protected $signature = 'upgrade:sys.commentary {--book=} {--para=} {--list} {--model=} {--skip= : 跳过指定的 book_name，逗号分隔，支持前缀通配，如 abhi*,sn2} {--fresh : 清除缓存断点，从头开始}';

    protected $prompt = <<<'md'
    你是一个注释对照阅读助手。
    pali 是巴利原文，jsonl格式， 每条记录是一个句子。包括id 和 content 两个字段
    commentary 是pali的注释，jsonl 格式，每条记录是一个句子。包括id 和 content 两个字段
    commentary里面的内容是对pali内容的注释
    commentary里面的黑体字，说明该句子是注释pali中的对应的巴利文。
    你需要按照顺序将commentary中的句子与pali原文对照,。
    输出格式jsonl
    只输出pali数据
    在pali句子数据里面增加一个字段“commentary” 里面放这个句子对应的commentary句子的id
    不要输出content字段，只输出id,commentary字段
    直接输出jsonl数据，无需解释

**关键规则：**
1. 根据commentary中的句子的意思找到与pali对应的句子
1. 如果commentary中的某个句子**有黑体字**，它应该放在pali中对应巴利词汇出现的句子之后
2. 如果commentary中的某个句子**没有黑体字**，请将其与**上面最近的有黑体字的commentary句子**合并在一起（保持在同一个引用块内），不要单独成行
3. 有些pali原文句子可能没有对应的注释
4. 请不要遗漏任何commentary中的句子，也不要打乱顺序
5. 同时保持pali的句子数量不变，不要增删
6. 应该将全部commentary中的句子都与pali句子对应，不要有遗漏

**输出范例**
{"id":0,"commentary":[0,1]}
{"id":1,"commentary":[2]}
md;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    // 缓存键：记录已完成的 "book_name|cs_para" 集合，中断后重跑自动跳过，48h 过期
    private const CACHE_KEY = 'upgrade:sys.commentary:done';

    protected $sentenceService;

    protected $modelService;

    protected $openAIService;

    protected AiModelResource $model;

    protected $tokensPerSentence = 0;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        AIModelService $model,
        SentenceService $sent,
        OpenAIService $openAI
    ) {
        $this->modelService = $model;
        $this->sentenceService = $sent;
        $this->openAIService = $openAI;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('list')) {
            $result = RelatedParagraph::whereNotNull('book_name')
                ->groupBy('book_name')
                ->selectRaw('book_name,count(*)')
                ->get();
            foreach ($result as $key => $value) {
                $this->info($value['book_name'].'['.$value['count'].']');
            }

            return 0;
        }
        if ($this->option('model')) {
            $this->model = $this->modelService->getModelById($this->option('model'));
            // getModelById 始终返回 AiModelResource，未查到时其底层 resource 为 null，需据此判断
            if (empty($this->model->resource)) {
                $this->error('no model found id='.$this->option('model'));

                return 1;
            }
            $this->info("model:{$this->model['model']}");
        }

        if ($this->option('fresh')) {
            Cache::forget(self::CACHE_KEY);
            $this->info('Cleared cached cursor.');
        }

        // 是否为完整遍历（未指定 book/para），仅此情形在结束后清空断点缓存
        $isFullRun = ! $this->option('book') && ! $this->option('para');

        // 从缓存恢复已完成的 (book_name, cs_para) 集合，作为重入时的稳定游标
        $done = Cache::get(self::CACHE_KEY, []);

        // 需要跳过的 book_name 规则，逗号分隔，以 * 结尾为前缀匹配，否则全等匹配
        $skipPatterns = [];
        if ($this->option('skip')) {
            $skipPatterns = array_values(array_filter(array_map('trim', explode(',', $this->option('skip')))));
        }

        $channel = ChannelApi::getChannelByName('_System_commentary_');

        $books = [];
        if ($this->option('book')) {
            $books[] = ['book_name' => $this->option('book')];
        } else {
            // orderBy 保证每次遍历顺序一致，游标才稳定
            $books = RelatedParagraph::whereNotNull('book_name')
                ->where('book_name', '!=', '')
                ->where('cs_para', '>', 0)
                ->groupBy('book_name')
                ->orderBy('book_name')
                ->select('book_name')
                ->get()->toArray();
        }
        foreach ($books as $key => $currBook) {
            // 命中跳过规则时直接处理下一本：即便上次游标停在此书，也跳到下一个有效 book_name
            if ($this->shouldSkipBook($currBook['book_name'], $skipPatterns)) {
                $this->info('skip book '.$currBook['book_name']);

                continue;
            }
            $paragraphs = [];
            if ($this->option('para')) {
                $paragraphs[] = ['cs_para' => $this->option('para')];
            } else {
                // orderBy 保证每次遍历顺序一致，游标才稳定
                $paragraphs = RelatedParagraph::where('book_name', $currBook['book_name'])
                    ->where('cs_para', '>', 0)
                    ->groupBy('cs_para')
                    ->orderBy('cs_para')
                    ->select('cs_para')
                    ->get()->toArray();
            }
            foreach ($paragraphs as $key => $paragraph) {
                // 稳定游标：以 book_name|cs_para 唯一标识一个处理单元
                $cursor = $currBook['book_name'].'|'.$paragraph['cs_para'];
                // 已完成的单元直接跳过，实现中断后重入续跑
                if (isset($done[$cursor])) {
                    continue;
                }

                $message = 'ai commentary '.$currBook['book_name'].'-'.$paragraph['cs_para'];
                $this->info($message);
                $result = RelatedParagraph::where('book_name', $currBook['book_name'])
                    ->where('cs_para', $paragraph['cs_para'])
                    ->where('book_id', '>', 0)
                    ->orderBy('book_id')
                    ->orderBy('para')
                    ->get();
                $pcdBooks = [];
                $type = [];
                foreach ($result as $rBook) {
                    // 把段落整合成书。有几本书就有几条输出纪录
                    if (! isset($pcdBooks[$rBook->book_id])) {
                        $bookType = $this->getBookType($rBook->book_id);
                        $pcdBooks[$rBook->book_id] = $bookType;
                        if (! isset($type[$bookType])) {
                            $type[$bookType] = [];
                        }
                        $type[$bookType][$rBook->book_id] = [];
                    }
                    $currType = $pcdBooks[$rBook->book_id];
                    $type[$currType][$rBook->book_id][] = ['book' => $rBook->book, 'para' => $rBook->para];
                }
                foreach ($type as $keyType => $info) {
                    Log::debug($keyType);
                    foreach ($info as $bookId => $paragraphs) {
                        Log::debug($bookId);
                        foreach ($paragraphs as $paragraph) {
                            Log::debug($paragraph['book'].'-'.$paragraph['para']);
                        }
                    }
                }

                // 处理pali
                if (
                    $this->hasData($type, 'pāḷi') &&
                    $this->hasData($type, 'aṭṭhakathā')
                ) {
                    $paliJson = [];
                    foreach ($type['pāḷi'] as $keyBook => $paragraphs) {
                        foreach ($paragraphs as $paraData) {
                            $sentData = $this->getParaContent($paraData['book'], $paraData['para']);
                            $paliJson = array_merge($paliJson, $sentData);
                        }
                    }

                    $attaJson = [];
                    foreach ($type['aṭṭhakathā'] as $keyBook => $paragraphs) {
                        foreach ($paragraphs as $paraData) {
                            $sentData = $this->getParaContent($paraData['book'], $paraData['para']);
                            $attaJson = array_merge($attaJson, $sentData);
                        }
                    }

                    // llm 对齐
                    $result = $this->textAlign($paliJson, $attaJson);
                    // 写入db
                    $this->save($result, $channel);
                }

                // 处理义注
                if (
                    $this->hasData($type, 'aṭṭhakathā') &&
                    $this->hasData($type, 'ṭīkā')
                ) {
                    // 独立重建 attaJson，避免依赖上面 pāḷi 块是否执行
                    $attaJsonForTika = [];
                    foreach ($type['aṭṭhakathā'] as $keyBook => $attaParas) {
                        foreach ($attaParas as $paraData) {
                            $sentData = $this->getParaContent($paraData['book'], $paraData['para']);
                            $attaJsonForTika = array_merge($attaJsonForTika, $sentData);
                        }
                    }

                    $tikaResult = [];
                    foreach ($type['ṭīkā'] as $keyBook => $tikaParas) {
                        $tikaJson = [];
                        foreach ($tikaParas as $paraData) {
                            $sentData = $this->getParaContent($paraData['book'], $paraData['para']);
                            $tikaJson = array_merge($tikaJson, $sentData);
                        }

                        // llm 对齐
                        $result = $this->textAlign($attaJsonForTika, $tikaJson);
                        // 将新旧数据合并 如果原来没有，就添加，有，就合并数据
                        foreach ($result as $new) {
                            $found = false;
                            foreach ($tikaResult as $key => $old) {
                                if ($old['id'] === $new['id']) {
                                    $found = true;
                                    if (isset($new['commentary']) && is_array($new['commentary'])) {
                                        $tikaResult[$key]['commentary'] = array_merge($tikaResult[$key]['commentary'], $new['commentary']);
                                    }
                                    break;
                                }
                            }
                            if (! $found) {
                                array_push($tikaResult, $new);
                            }
                        }
                    }
                    // 写入db
                    $this->save($tikaResult, $channel);
                }

                // 该处理单元全部写库完成后再标记游标，确保中途中断不会误跳过
                $done[$cursor] = true;
                Cache::put(self::CACHE_KEY, $done, now()->addHours(24));
            }
        }

        // 完整遍历正常结束，清空断点缓存
        if ($isFullRun) {
            Cache::forget(self::CACHE_KEY);
        }

        return 0;
    }

    /**
     * 判断 book_name 是否命中跳过规则。
     *
     * @param  array<int, string>  $patterns  以 * 结尾为前缀匹配，否则全等匹配
     */
    private function shouldSkipBook(string $bookName, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (str_ends_with($pattern, '*')) {
                $prefix = rtrim($pattern, '*');
                if ($prefix !== '' && str_starts_with($bookName, $prefix)) {
                    return true;
                }
            } elseif ($bookName === $pattern) {
                return true;
            }
        }

        return false;
    }

    private function hasData($typeData, $typeName)
    {
        if (
            ! isset($typeData[$typeName]) ||
            $this->getParagraphNumber($typeData[$typeName]) === 0
        ) {
            Log::warning($typeName.' data is missing');

            return false;
        }

        return true;
    }

    private function getParagraphNumber($type)
    {
        if (! isset($type) || ! is_array($type)) {
            return 0;
        }
        $count = 0;
        foreach ($type as $bookId => $paragraphs) {
            $count += count($paragraphs);
        }

        return $count;
    }

    private function getBookType($bookId)
    {
        $bookTitle = BookTitle::where('sn', $bookId)->first();
        $paliTextUuid = PaliText::where('book', $bookTitle->book)->where('paragraph', $bookTitle->paragraph)->value('uid');
        $tagIds = TagMap::where('anchor_id', $paliTextUuid)->select('tag_id')->get();
        $tags = Tag::whereIn('id', $tagIds)->select('name')->get();
        foreach ($tags as $key => $tag) {
            if (in_array($tag->name, ['pāḷi', 'aṭṭhakathā', 'ṭīkā'])) {
                return $tag->name;
            }
        }

        return null;
    }

    private function getParaContent($book, $para)
    {
        $sentenceService = app(SearchPaliDataService::class);
        $sentences = PaliSentence::where('book', $book)
            ->where('paragraph', $para)
            ->orderBy('word_begin')
            ->get();
        if (! $sentences) {
            return null;
        }
        $json = [];
        foreach ($sentences as $key => $sentence) {
            $content = $sentenceService->getSentenceContent($book, $para, $sentence->word_begin, $sentence->word_end);
            $id = "{$book}-{$para}-{$sentence->word_begin}-{$sentence->word_end}";
            $json[] = ['id' => $id, 'content' => $content['markdown']];
        }

        return $json;
    }

    private function arrayIndexed(array $input): array
    {
        $output = [];
        foreach ($input as $key => $value) {
            $value['id'] = $key;
            $output[] = $value;
        }

        return $output;
    }

    private function arrayUnIndexed(array $input, array $original, array $commentary): array
    {
        $output = [];
        foreach ($input as $key => $value) {
            if (! isset($original[$key])) {
                Log::warning('no id');

                continue;
            }
            $value['id'] = $original[$key]['id'];
            if (isset($value['commentary'])) {
                $newCommentary = array_map(function ($n) use ($commentary) {
                    if (isset($commentary[$n])) {
                        return $commentary[$n]['id'];
                    }

                    return '';
                }, $value['commentary']);
                $value['commentary'] = $newCommentary;
            }
            $output[] = $value;
        }

        return $output;
    }

    private function textAlign(array $original, array $commentary)
    {
        if (! $this->model) {
            Log::error('model is invalid');

            return [];
        }
        $originalSn = $this->arrayIndexed($original);
        $commentarySn = $this->arrayIndexed($commentary);

        $originalText = "```jsonl\n".LlmResponseParser::jsonl_encode($originalSn)."\n```";
        $commentaryText = "```jsonl\n".LlmResponseParser::jsonl_encode($commentarySn)."\n```";

        Log::debug('ai request', [
            'original' => $originalText,
            'commentary' => $commentaryText,
        ]);

        $totalSentences = count($original) + count($commentary);
        $maxTokens = (int) ($this->tokensPerSentence * $totalSentences * 1.5);
        $this->info("requesting…… {$totalSentences} sentences {$this->tokensPerSentence}tokens/sentence set {$maxTokens} max_tokens");
        Log::debug('requesting…… '.$this->model['model']);
        $startAt = time();
        $response = $this->openAIService->setApiUrl($this->model['url'])
            ->setModel($this->model['model'])
            ->setApiKey($this->model['key'])
            ->setSystemPrompt($this->prompt)
            ->setTemperature(0.0)
            ->setStream(false)
            ->setMaxToken($maxTokens)
            ->send("# pali\n\n{$originalText}\n\n# commentary\n\n{$commentaryText}");
        $completeAt = time();
        $answer = $response['choices'][0]['message']['content'] ?? '[]';
        Log::debug('ai response', ['data' => $answer]);
        $message = ($completeAt - $startAt).'s';

        if (isset($response['usage']['completion_tokens'])) {
            Log::debug('usage', $response['usage']);
            $message .= ' completion_tokens:'.$response['usage']['completion_tokens'];
            $curr = (int) ($response['usage']['completion_tokens'] / $totalSentences);
            if ($curr > $this->tokensPerSentence) {
                $this->tokensPerSentence = $curr;
            }
        }
        $this->info($message);
        $json = [];
        if (is_string($answer)) {
            $json = LlmResponseParser::jsonl($answer);
            $json = $this->arrayUnIndexed($json, $original, $commentary);
            Log::debug(json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
        if (count($json) === 0) {
            Log::error('jsonl is empty');
        }

        return $json;
    }

    private function save($json, $channel)
    {
        if (! is_array($json)) {
            Log::warning('llm return null');

            return false;
        }
        foreach ($json as $key => $sentence) {
            if (! isset($sentence['commentary'])) {
                continue;
            }
            $sentId = explode('-', $sentence['id']);
            $arrCommentary = $sentence['commentary'];
            if (
                isset($arrCommentary) &&
                is_array($arrCommentary) &&
                count($arrCommentary) > 0
            ) {
                $content = array_map(function ($n) {
                    if (is_string($n)) {
                        return '{{'.$n.'}}';
                    } elseif (is_array($n) && isset($n['id']) && is_string($n['id'])) {
                        return '{{'.$n['id'].'}}';
                    } else {
                        return '';
                    }
                }, $arrCommentary);
                $this->sentenceService->save(
                    [
                        'book_id' => $sentId[0],
                        'paragraph' => $sentId[1],
                        'word_start' => $sentId[2],
                        'word_end' => $sentId[3],
                        'channel_uid' => $channel->uid,
                        'content' => implode("\n", $content),
                        'lang' => $channel->lang,
                        'status' => $channel->status,
                        'editor_uid' => $this->model['uid'],
                    ]
                );
                $this->info($sentence['id'].' saved');
            }
        }
    }
}
