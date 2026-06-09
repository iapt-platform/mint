<?php

namespace App\Console\Commands;

use App\Helpers\LlmResponseParser;
use App\Http\Api\ChannelApi;
use App\Http\Resources\AiModelResource;
use App\Models\PaliText;
use App\Models\Sentence;
use App\Services\AIAssistant\NissayaTranslateService;
use App\Services\AIAssistant\PaliTranslateService;
use App\Services\AIModelService;
use App\Services\AuthService;
use App\Services\OpenAIService;
use App\Services\SentenceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UpgradeAITranslation extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan upgrade:ai.translation translation --book=131 --para=27
     * php artisan upgrade:ai.translation nissaya --book=207 --para=1247
     *
     * nissaya 参考资料用法示例（--nissaya 指定哪些步骤注入 nissaya 逐词缅文释义）：
     * - 默认（不传）            translate/review/evaluate 全部注入 nissaya
     *   php artisan upgrade:ai.translation translation {channel} --book=131 --para=27 --steps=translate,review,revise,evaluate
     * - 仅 review 注入
     *   php artisan upgrade:ai.translation translation {channel} --book=131 --para=27 --steps=translate,review,evaluate --nissaya=review
     * - review + evaluate 注入，translate 不注入
     *   php artisan upgrade:ai.translation translation {channel} --book=131 --para=27 --steps=translate,review,evaluate --nissaya=review,evaluate
     * - 全部不注入
     *   php artisan upgrade:ai.translation translation {channel} --book=131 --para=27 --steps=translate,review,evaluate --nissaya=
     *
     * @var string
     */
    protected $signature = 'upgrade:ai.translation
    {type}
    {channel}
    {--book=}
    {--para=}
    {--resume}
    {--model=}
    {--thinking= : 开启和关闭deepseek thinking true | false}
    {--steps=translate : translation 工作流步骤，逗号分隔，可选 translate,review,revise,evaluate（evaluate 为质量评估，须放最后）}
    {--nissaya=translate,review,evaluate : 启用 nissaya 参考资料的步骤，逗号分隔，可选 translate,review,evaluate；传空字符串则全部不注入}
    {--fresh : 清除缓存断点，从头开始}';

    // 缓存键前缀：以 type、channel 区分，记录已完成的 "book|para" 集合，中断后重跑自动跳过
    private const CACHE_KEY_PREFIX = 'upgrade:ai.translation:done';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected AiModelResource $model;

    protected string $modelToken;

    protected array $workChannel;

    protected string $accessToken;

    protected bool $thinking;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        protected AIModelService $modelService,
        protected SentenceService $sentenceService,
        protected OpenAIService $openAIService,
        protected NissayaTranslateService $nissayaTranslateService,
        protected PaliTranslateService $paliTranslateService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /**
         * model
         */
        if (! $this->option('model')) {
            $this->error('model is request');

            return 1;
        }
        $this->model = $this->modelService->getModelById($this->option('model'));
        $this->info("model:{$this->model['model']}");
        $this->modelToken = AuthService::getUserToken($this->model['uid']);

        // channel
        $this->workChannel = ChannelApi::getById($this->argument('channel'));
        // 需要判断输入channel 与翻译类型是否一致 nissaya -> nissaya channel
        if ($this->workChannel['type'] !== $this->argument('type')) {
            $this->error('channel type not match request '.$this->argument('type').' input is '.$this->workChannel['type']);

            return 1;
        }

        if ($this->option('thinking')) {
            $this->thinking = $this->option('thinking') === 'true';
            $this->line('thinking is '.$this->option('thinking'));
        }

        // translation 工作流步骤校验
        $steps = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('steps')))));
        $invalid = array_diff($steps, PaliTranslateService::STEPS);
        if (! empty($invalid)) {
            $this->error('invalid steps: '.implode(',', $invalid).'. allowed: '.implode(',', PaliTranslateService::STEPS));

            return 1;
        }

        // nissaya 参考资料注入步骤校验（哪些步骤启用 nissaya）
        $nissayaSteps = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('nissaya')))));
        $invalidNissaya = array_diff($nissayaSteps, PaliTranslateService::NISSAYA_STEPS);
        if (! empty($invalidNissaya)) {
            $this->error('invalid nissaya steps: '.implode(',', $invalidNissaya).'. allowed: '.implode(',', PaliTranslateService::NISSAYA_STEPS));

            return 1;
        }

        $type = $this->argument('type');
        $channelId = $this->workChannel['id'] ?? '';

        // 缓存键：按 type、channel 区分不同任务的断点
        $cacheKey = self::CACHE_KEY_PREFIX.':'.$type.':'.$channelId;

        if ($this->option('fresh')) {
            Cache::forget($cacheKey);
            $this->info('Cleared cached cursor.');
        }

        // 是否为完整遍历（未指定 book/para），仅此情形在结束后清空断点缓存
        $isFullRun = ! $this->option('book') && ! $this->option('para');

        // 从缓存恢复已完成的 (book, para) 集合，作为重入时的稳定游标
        $done = Cache::get($cacheKey, []);

        $books = [];
        if ($this->option('book')) {
            $books = [$this->option('book')];
        } else {
            // 未指定 book 时，若已有断点缓存，从上次处理到的 book 继续，无需从 1 开始
            $startBook = 1;
            if (! empty($done)) {
                $doneBooks = array_map(fn ($cursor) => (int) explode('|', $cursor)[0], array_keys($done));
                $startBook = max($doneBooks);
                $this->info("resume from book {$startBook}");
            }
            $books = range($startBook, 217);
        }
        foreach ($books as $key => $book) {
            $maxParagraph = PaliText::where('book', $book)->max('paragraph');
            $paragraphs = range(1, $maxParagraph);
            if ($this->option('para')) {
                $paragraphs = [$this->option('para')];
            }
            foreach ($paragraphs as $key => $paragraph) {
                // 稳定游标：缓存键已含 type、channel，此处仅以 book|para 标识处理单元
                $cursor = $book.'|'.$paragraph;
                if (isset($done[$cursor])) {
                    $this->info("skip {$cursor}");

                    continue;
                }
                $start = time();
                $data = [];
                switch ($this->argument('type')) {
                    case 'translation':
                        $data = $this->paliTranslateService
                            ->setModel($this->model)
                            ->setChannel($this->workChannel)
                            ->setThinking($this->thinking ?? null)
                            ->setNissayaSteps($nissayaSteps)
                            ->run($steps, (int) $book, (int) $paragraph);
                        break;
                    case 'nissaya':
                        $data = $this->aiNissayaTranslate($book, $paragraph);
                        break;
                    case 'wbw':
                        $data = $this->aiWBW($book, $paragraph);
                        break;
                    default:
                        // code...
                        break;
                }
                $this->save($data);
                $time = time() - $start;
                $this->info($this->argument('type')." {$book}-{$paragraph} ".count($data).' sentences time='.$time);
                // 该处理单元全部写库完成后再标记游标，确保中途中断不会误跳过
                $done[$cursor] = true;
                Cache::put($cacheKey, $done, now()->addHours(24));
            }
        }

        // 完整遍历正常结束，清空断点缓存
        if ($isFullRun) {
            Cache::forget($cacheKey);
        }

        return 0;
    }

    private function aiWBW($book, $para)
    {
        $sysPrompt = <<<'md'
        你是一个佛教翻译专家，精通巴利文和缅文，精通巴利文逐词解析
        ## 翻译要求：
        - 请将用户提供的巴利句子单词表中的每个巴利文单词翻译为中文
        - 这些单词是一个完整的句子，请根据单词的上下文翻译
        - original 里面的数据是巴利文单词
        - 输入格式为 json 数组
        - 输出jsonl格式

        在原来的数据中添加下列输出字段
        1. meaning:单词的中文意思，如果有两个可能的意思，两个意思之间用/符号分隔
        5. confidence:你认为你给出的这个单词的信息的信心指数（准确程度） 数值1-100 如果觉得非常有把握100, 如果觉得把握不大，适当降低信心指数
        6. note:如果你认为信心指数很低，这个是疑难单词，请在note字段写明原因，如果不是疑难单词，请不要填写note


        **范例**：

        {"id":1,"original":"bhikkhusanghassa","meaning":"比库僧团[的]","confidence":100}

        直接输出jsonl, 无需其他内容
        md;
        $channelId = ChannelApi::getSysChannel('_System_Wbw_VRI_');
        $sentences = Sentence::where('channel_uid', $channelId)
            ->where('book_id', $book)
            ->where('paragraph', $para)
            ->get();
        $result = [];
        foreach ($sentences as $key => $sentence) {
            $wbw = json_decode($sentence->content);
            $tpl = [];
            foreach ($wbw as $key => $word) {
                if (
                    ! empty($word->real->value) &&
                    $word->type->value !== '.ctl.'
                ) {
                    $tpl[] = [
                        'id' => $word->sn[0],
                        'original' => $word->real->value,
                    ];
                }
            }

            $tplText = json_encode($tpl, JSON_UNESCAPED_UNICODE);
            Log::debug($tplText);
            $startAt = time();
            $llm = $this->openAIService->setApiUrl($this->model['url'])
                ->setModel($this->model['model'])
                ->setApiKey($this->model['key'])
                ->setSystemPrompt($sysPrompt)
                ->setTemperature(0.7)
                ->setStream(false);
            if (isset($this->thinking)) {
                $llm = $llm->setThinking($this->thinking);
            }
            $response = $llm->send("```json\n{$tplText}\n```");
            $complete = time() - $startAt;
            $content = $response['choices'][0]['message']['content'] ?? '[]';
            Log::debug("ai response in {$complete}s content=".$content);

            $json = LlmResponseParser::jsonl($content);

            $id = "{$sentence->book_id}-{$sentence->paragraph}-{$sentence->word_start}-{$sentence->word_end}";
            $result[] = [
                'id' => $id,
                'content' => json_encode($json, JSON_UNESCAPED_UNICODE),
            ];
        }

        return $result;
    }

    private function aiNissayaTranslate($book, $para)
    {
        $sentences = Sentence::nissaya()
            ->language('my') // 过滤缅文
            ->where('book_id', $book)
            ->where('paragraph', $para)
            ->orderBy('word_start')
            ->get();
        $result = [];
        foreach ($sentences as $key => $sentence) {
            if (! empty($sentence->content)) {
                $id = "{$sentence->book_id}-{$sentence->paragraph}-{$sentence->word_start}-{$sentence->word_end}";

                $aiNissaya = $this->nissayaTranslateService
                    ->setModel($this->model)
                    ->translate($sentence->content, false);
                Log::debug('ai response ', ['content' => $aiNissaya['data']]);
                $result[] = [
                    'id' => $id,
                    'content' => json_encode($aiNissaya['data'] ?? [], JSON_UNESCAPED_UNICODE),
                    'content_type' => 'json',
                ];
            }
        }

        return $result;
    }

    private function save($data)
    {
        // 写入句子库
        $sentData = [];
        $sentData = array_map(function ($n) {
            $sId = explode('-', $n['id']);

            return [
                'book_id' => $sId[0],
                'paragraph' => $sId[1],
                'word_start' => $sId[2],
                'word_end' => $sId[3],
                'channel_uid' => $this->workChannel['id'],
                'content' => $n['content'],
                'content_type' => $n['content_type'] ?? 'markdown',
                'lang' => $this->workChannel['lang'],
                'status' => $this->workChannel['status'],
                'editor_uid' => $this->model['uid'],
            ];
        }, $data);
        foreach ($sentData as $key => $value) {
            $this->sentenceService->save($value);
        }
    }
}
