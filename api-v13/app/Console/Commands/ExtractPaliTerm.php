<?php

namespace App\Console\Commands;

use App\Helpers\LlmResponseParser;
use App\Http\Api\ChannelApi;
use App\Http\Resources\AiModelResource;
use App\Models\DhammaTerm;
use App\Models\PaliText;
use App\Models\UserDict;
use App\Services\AIModelService;
use App\Services\OpenAIService;
use App\Services\SentenceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ExtractPaliTerm extends Command
{
    // 断点缓存键：记录最后一个已完成的 chapter 游标 ['book'=>int,'paragraph'=>int]，
    // 使命令可重入——中断后重跑自动跳过已完成的 chapter
    private const CURSOR_KEY = 'extract:pali.term:cursor';

    // 术语表额外纳入的 user_dicts 词典 id（其 word 去除连字符与空格后加入）
    private const USER_DICT_IDS = [
        'b089de57-f146-4095-b886-057863728c43',
        '0bfd87ec-f3ac-49a2-985e-28388779078d',
        '7c7ee287-35ba-4cf3-b87b-30f1fa6e57c9',
        '4ac8a0d5-9c6f-4b9f-983d-84288d47f993',
    ];

    /**
     * 用 LLM 从巴利文经文中提取术语。
     *
     * 以 pali_texts 表为准，扁平遍历每个 book 的 chapter（level 1-7）。从每个 book
     * 的第一个 chapter 开始，按 paragraph 顺序，每个 chapter 覆盖「本 chapter 起始段落
     * → 下一个 chapter 起始段落之前」的所有段落（含正文 bodytext）。把该 chapter 的
     * 巴利文（pali_texts.text 拼接）连同完整社区术语表发给大模型，引导其输出与本章相关
     * 的全部术语的巴利语单词（JSON 字符串数组），保存到系统术语 channel。
     *
     * 测试：
     *   php artisan extract:pali.term 554cdbc1-e170-4145-a808-3cc2cfa721c7 --book=93 --para=8
     *
     * @var string
     */
    protected $signature = 'extract:pali.term
    {model : LLM 模型 id}
    {--book= : 仅处理该 book；不传则遍历全部 book}
    {--para= : 仅处理包含该段落的 chapter（向下取所在 chapter）；需配合 --book}
    {--thinking= : 开启/关闭 deepseek thinking，true | false}
    {--fresh : 清除断点缓存，从头开始}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '用 LLM 逐 chapter 提取巴利文相关术语并保存到系统术语 channel';

    /**
     * LLM 模型配置（含 model/url/key/uid，支持数组访问）。
     */
    protected AiModelResource $model;

    /**
     * 保存目标 channel（系统术语 channel）。
     */
    protected array $glossaryChannel;

    /**
     * 术语表词条原形集合（word => true），用于后置过滤：
     * 只保留确实存在于术语表的词，丢弃 LLM 输出的屈折变形或臆造词。
     *
     * @var array<string, bool>
     */
    protected array $glossarySet = [];

    protected ?bool $thinking = null;

    public function __construct(
        protected AIModelService $modelService,
        protected OpenAIService $openAIService,
        protected SentenceService $sentenceService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // ---------- 模型 ----------
        $model = $this->modelService->getModelById($this->argument('model'));
        if (! $model instanceof AiModelResource || $model->resource === null) {
            $this->error('invalid model id');

            return 1;
        }
        $this->model = $model;
        $this->info("model: {$this->model['model']}");

        if ($this->option('thinking') !== null) {
            $this->thinking = $this->option('thinking') === 'true';
        }

        // ---------- 保存目标 channel ----------
        $channelId = ChannelApi::getSysChannel('_system_glossary_');
        if (! $channelId) {
            $this->error('system glossary channel "_system_glossary_" not found');

            return 1;
        }
        $this->glossaryChannel = ChannelApi::getById($channelId);
        Log::info('extract:pali.term glossary channel', ['channel' => $channelId]);

        // ---------- 完整术语表（一次性加载，所有 chapter 共用）----------
        $glossaryJson = $this->loadGlossaryJson();
        if ($glossaryJson === null) {
            return 1;
        }

        if ($this->option('fresh')) {
            Cache::forget(self::CURSOR_KEY);
            $this->info('cleared checkpoint');
        }

        // ---------- 模式三：book + paragraph —— 忽略断点，只跑该 chapter ----------
        if ($this->option('book') && $this->option('para')) {
            $this->runSingleChapter((int) $this->option('book'), (int) $this->option('para'), $glossaryJson);

            return 0;
        }

        // --para 必须配合 --book
        if ($this->option('para')) {
            $this->error('--para must be used together with --book');

            return 1;
        }

        $cursor = Cache::get(self::CURSOR_KEY, []);

        // ---------- 模式二：仅 book —— 同一 book 从断点续，跑完即止，不跳到下一 book ----------
        if ($this->option('book')) {
            $book = (int) $this->option('book');
            // 断点是同一 book 才续跑，否则整本从头
            $resumeCursor = (! empty($cursor) && (int) $cursor['book'] === $book) ? $cursor : [];
            if (! empty($resumeCursor)) {
                $this->info("resume book {$book} from para {$resumeCursor['paragraph']}");
            }
            $this->runBook($book, $glossaryJson, $resumeCursor);

            return 0;
        }

        // ---------- 模式一：无参数 —— 从断点继续，跨 book 直到末尾 ----------
        $startBook = ! empty($cursor) ? (int) $cursor['book'] : 1;
        if (! empty($cursor)) {
            $this->info("resume from book {$cursor['book']} para {$cursor['paragraph']}");
        }
        for ($book = $startBook; $book <= 217; $book++) {
            // 仅断点所在的首 book 受游标限制，之后的 book 全量处理
            $this->runBook($book, $glossaryJson, $book === $startBook ? $cursor : []);
        }
        // 全部完成，清空断点
        Cache::forget(self::CURSOR_KEY);
        $this->info('all books completed');

        return 0;
    }

    /**
     * 加载完整术语表（dhamma_terms 全表、跨所有 channel 去重）并编码为发送给 LLM 的紧凑 JSON。
     *
     * @return string|null 编码后的术语表 JSON；术语表为空时返回 null
     */
    private function loadGlossaryJson(): ?string
    {
        // 1) dhamma_terms 全表 distinct 词条原形（不限 channel），排除 word 含空格的短语条目
        $dhammaWords = DhammaTerm::query()
            ->whereNotNull('word')
            ->where('word', '!=', '')
            ->where('word', 'not like', '% %')
            ->distinct()
            ->pluck('word')
            ->all();

        // 2) user_dicts 指定词典的词，去除连字符与空格后加入
        $userWords = UserDict::query()
            ->whereIn('dict_id', self::USER_DICT_IDS)
            ->whereNotNull('word')
            ->where('word', '!=', '')
            ->pluck('word')
            ->map(fn ($w) => str_replace(['-', ' '], '', (string) $w))
            ->all();

        // 合并 + NFC 归一（避免巴利文变音符 NFC/NFD 不一致）+ 去空 + 去重
        $terms = array_map(fn ($w) => $this->toNfc(trim((string) $w)), array_merge($dhammaWords, $userWords));
        $terms = array_values(array_unique(array_filter($terms, fn ($w) => $w !== '')));

        if (empty($terms)) {
            $this->error('glossary is empty');

            return null;
        }

        // 词条原形集合，供 askLlm 后置过滤（O(1) 查找）
        $this->glossarySet = array_fill_keys($terms, true);

        $this->info('glossary loaded: '.count($terms).' terms (dhamma_terms + user_dicts)');
        Log::info('extract:pali.term glossary loaded', [
            'total' => count($terms),
            'dhamma_terms' => count($dhammaWords),
            'user_dicts' => count($userWords),
        ]);

        return json_encode($terms, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 处理单个 book 的全部 chapter（扁平遍历），逐 chapter 写入断点以支持重入。
     *
     * @param  array{book?: int, paragraph?: int}  $resumeCursor  断点游标；非空时跳过其
     *                                                            （含）之前已完成的 chapter
     */
    private function runBook(int $book, string $glossaryJson, array $resumeCursor = []): void
    {
        // 本书全部 chapter 节点（level 1-7），按 paragraph 顺序——扁平遍历，非树遍历
        $chapters = PaliText::where('book', $book)
            ->whereBetween('level', [1, 7])
            ->orderBy('paragraph')
            ->get(['paragraph', 'level', 'toc']);

        if ($chapters->isEmpty()) {
            Log::info('extract:pali.term no chapter', ['book' => $book]);

            return;
        }

        $maxParagraph = (int) PaliText::where('book', $book)->max('paragraph');

        foreach ($chapters as $chapter) {
            $start = (int) $chapter->paragraph;

            // 断点跳过：游标（含）之前的 chapter 视为已完成
            if ($this->isDone($resumeCursor, $book, $start)) {
                $this->info("skip {$book}-{$start} (done)");

                continue;
            }

            $end = $this->chapterEnd($book, $start, $maxParagraph);
            $this->processChapter($book, $start, $end, $glossaryJson);

            // 该 chapter 完成后写入断点（中途中断时不会误标记未完成的 chapter）
            Cache::put(self::CURSOR_KEY, ['book' => $book, 'paragraph' => $start], now()->addHours(48));
        }
    }

    /**
     * 模式三：处理 --para 所在的单个 chapter（向下取所在 chapter），不读写断点。
     */
    private function runSingleChapter(int $book, int $para, string $glossaryJson): void
    {
        // 向下取所在 chapter：≤para 的最近 chapter 节点（level 1-7）
        $target = PaliText::where('book', $book)
            ->where('paragraph', '<=', $para)
            ->whereBetween('level', [1, 7])
            ->orderBy('paragraph', 'desc')
            ->first(['paragraph']);

        if (! $target) {
            $this->error("no chapter found for book={$book} para={$para}");

            return;
        }

        $start = (int) $target->paragraph;
        $maxParagraph = (int) PaliText::where('book', $book)->max('paragraph');
        $end = $this->chapterEnd($book, $start, $maxParagraph);

        $this->processChapter($book, $start, $end, $glossaryJson);
    }

    /**
     * chapter 区间终点 = 下一个 chapter 起始段落之前；末章到本书结尾。
     */
    private function chapterEnd(int $book, int $start, int $maxParagraph): int
    {
        $next = PaliText::where('book', $book)
            ->where('paragraph', '>', $start)
            ->whereBetween('level', [1, 7])
            ->orderBy('paragraph')
            ->value('paragraph');

        return $next ? (int) $next - 1 : $maxParagraph;
    }

    /**
     * Unicode 规范化为 NFC（巴利文变音符匹配需统一 NFC/NFD）。intl 不可用时原样返回。
     */
    private function toNfc(string $s): string
    {
        if (class_exists(\Normalizer::class)) {
            $normalized = \Normalizer::normalize($s, \Normalizer::FORM_C);
            if ($normalized !== false) {
                return $normalized;
            }
        }

        return $s;
    }

    /**
     * 判断某 chapter 是否在断点游标（含）之前——即已完成、应跳过。
     *
     * @param  array{book?: int, paragraph?: int}  $cursor
     */
    private function isDone(array $cursor, int $book, int $para): bool
    {
        if (empty($cursor)) {
            return false;
        }

        return $book < (int) $cursor['book']
            || ($book === (int) $cursor['book'] && $para <= (int) $cursor['paragraph']);
    }

    /**
     * 处理单个 chapter：拼接巴利文 → 调 LLM → 解析 → 保存。
     */
    private function processChapter(int $book, int $start, int $end, string $glossaryJson): void
    {
        $startAt = time();

        // chapter 巴利文 = 区间内全部段落 pali_texts.text 顺序拼接
        $paliText = PaliText::where('book', $book)
            ->whereBetween('paragraph', [$start, $end])
            ->orderBy('paragraph')
            ->pluck('text')
            ->filter(fn ($t) => filled($t))
            ->implode("\n");

        Log::info('extract:pali.term chapter', [
            'book' => $book,
            'start' => $start,
            'end' => $end,
            'pali_strlen' => mb_strlen($paliText),
        ]);

        if ($paliText === '') {
            $this->warn("skip empty chapter {$book}-{$start}");

            return;
        }

        $words = $this->askLlm($glossaryJson, $paliText);

        $this->saveChapterGlossary($book, $start, $words);

        $time = time() - $startAt;
        $this->info("chapter {$book}-{$start} (para {$start}-{$end}) => ".count($words)." terms, time={$time}s");
    }

    /**
     * 把术语表与 chapter 巴利文发给 LLM，返回相关术语的巴利语单词数组。
     *
     * @return array<int, string>
     */
    private function askLlm(string $glossaryJson, string $paliText): array
    {
        $sysPrompt = <<<'md'
        你是一位精通巴利语的佛教术语专家。

        ## 任务
        给定一段巴利文原文，以及一份术语表（巴利语单词列表），
        请从术语表中找出与这段经文相关的全部术语，并且输出。

        ## 要求
        - 输出的每个单词必须是给你的术语表中的单词。
        - 经文中的术语往往以屈折变化（变格、变位、复合、连音等）形式出现。你必须把这些
          变形还原、找到对应术语表中的词条，输出术语表里的那个原形，而不是经文中出现的变形。
          例如：经文中的 "brahmadattena"（具格）对应词条 "brahmadatta"，应输出 "brahmadatta"；
          "sattānaṃ" 对应 "satta"，输出 "satta"；"buddhassa" 对应 "buddha"，输出 "buddha"。
        - 绝不要输出经文里的屈折变形，也不要输出术语表中不存在的单词。
        - 复合词中局部包含术语的，也要输出。不要输出整个复合词，只输出其中包含的术语。
        - 只输出与本段经文内容确实相关的术语，不要输出无关术语。
        - 输出 JSON 数组，元素为术语表中的巴利语单词原形，不要包含释义或其他字段。

        ## 只保留有实义的术语，排除非术语词（即使它们出现在术语表中）
        术语指有独立含义的佛教/巴利专有概念、法相名词，或人名、地名、专有名词。
        请排除以下这类“非术语”的功能词与泛用词：
        - 语法虚词、不变词、连词、语气词：如 ca、vā、pana、kho、iti、hi、api、eva、ce、tu 等。
        - 代词、指示词、疑问词：如 so、ayaṃ、taṃ、idha、kiṃ、ya- 等。
        - 极常见的系动词、泛用动词及其变位：如 hoti、atthi、karoti 等纯功能性用法。
        - 其他你判断不构成独立术语的高频功能词、助词、量词等。
        判断标准：若去掉该词后不影响“这是一个值得收录为词条的概念/名相”，则视为非术语并排除。

        ## 输出格式（直接输出 JSON，无需任何额外说明）
        ["dukkha","nibbāna","saṅkhāra"]
        md;

        $userMessage = "## 术语表\n```json\n{$glossaryJson}\n```\n\n## 巴利文经文\n{$paliText}";

        $llm = $this->openAIService->setApiUrl($this->model['url'])
            ->setModel($this->model['model'])
            ->setApiKey($this->model['key'])
            ->setSystemPrompt($sysPrompt)
            ->setTemperature(0.3)
            ->setStream(false);
        if ($this->thinking !== null) {
            $llm = $llm->setThinking($this->thinking);
        }

        $response = $llm->send($userMessage);
        $content = is_array($response)
            ? ($response['choices'][0]['message']['content'] ?? '[]')
            : (string) $response;
        Log::debug('extract:pali.term llm response', ['content' => $content]);

        $parsed = LlmResponseParser::json($content);

        // 规范化：非空字符串、去重、去空白
        $candidates = [];
        foreach ($parsed as $item) {
            if (is_string($item) && trim($item) !== '') {
                $candidates[] = trim($item);
            }
        }
        $candidates = array_values(array_unique($candidates));

        // 后置过滤：归一为 NFC 后比对，只保留确实存在于术语表的词条原形，丢弃屈折变形/臆造词
        $words = [];
        $dropped = [];
        foreach ($candidates as $word) {
            $nfc = $this->toNfc($word);
            if (isset($this->glossarySet[$nfc])) {
                $words[] = $nfc;
            } else {
                $dropped[] = $word;
            }
        }

        if (! empty($dropped)) {
            Log::warning('extract:pali.term dropped non-glossary words', ['dropped' => $dropped]);
        }

        return $words;
    }

    /**
     * 将 chapter 术语（巴利语单词数组）以 JSON 保存为一条 Sentence 记录。
     *
     * paragraph 用 chapter 起始段落，整章一条记录故 word_start/word_end 均为 0。
     *
     * @param  array<int, string>  $words
     */
    private function saveChapterGlossary(int $book, int $startPara, array $words): void
    {
        try {
            $this->sentenceService->saveWithHistory([
                'book_id' => $book,
                'paragraph' => $startPara,
                'word_start' => 0,
                'word_end' => 0,
                'channel_uid' => $this->glossaryChannel['id'],
                'content' => json_encode($words, JSON_UNESCAPED_UNICODE),
                'content_type' => 'json',
                'lang' => $this->glossaryChannel['lang'] ?? 'pli',
                'status' => $this->glossaryChannel['status'] ?? 10,
                'editor_uid' => $this->model['uid'],
            ]);
        } catch (\Exception $e) {
            $this->error("save {$book}-{$startPara} failed: ".$e->getMessage());
            Log::error('extract:pali.term save failed', [
                'book' => $book,
                'paragraph' => $startPara,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
