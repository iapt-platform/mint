<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Services\OpenAIService;
use App\Services\AIModelService;
use App\Services\SentenceService;
use App\Services\SearchPaliDataService;
use App\Services\AIAssistant\NissayaTranslateService;
use App\Services\AuthService;

use App\Http\Resources\AiModelResource;

use App\Models\PaliText;
use App\Models\PaliSentence;
use App\Models\Sentence;

use App\Helpers\LlmResponseParser;

use App\Http\Api\ChannelApi;
use App\Tools\Tools;

class UpgradeAITranslation extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan upgrade:ai.translation translation --book=141 --para=535
     * php artisan upgrade:ai.translation nissaya --book=207 --para=1247
     * @var string
     */
    protected $signature = 'upgrade:ai.translation {type} {--book=} {--para=} {--resume} {--model=} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    protected $sentenceService;
    protected $modelService;
    protected $openAIService;
    protected $nissayaTranslateService;
    protected AiModelResource $model;
    protected $modelToken;
    protected $workChannel;
    protected $accessToken;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        AIModelService $model,
        SentenceService $sent,
        OpenAIService $openAI,
        NissayaTranslateService $nissayaTranslate
    ) {
        $this->modelService = $model;
        $this->sentenceService = $sent;
        $this->openAIService = $openAI;
        $this->nissayaTranslateService = $nissayaTranslate;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('model')) {
            $this->model = $this->modelService->getModelById($this->option('model'));
            $this->info("model:{$this->model['model']}");
            $this->modelToken = AuthService::getUserToken($this->model['uid']);
        }
        $this->workChannel = ChannelApi::getById($this->ask('请输入结果channel'));

        $books = [];
        if ($this->option('book')) {
            $books = [$this->option('book')];
        } else {
            $books = range(1, 217);
        }
        foreach ($books as $key => $book) {
            $maxParagraph = PaliText::where('book', $book)->max('paragraph');
            $paragraphs = range(1, $maxParagraph);
            if ($this->option('para')) {
                $paragraphs = [$this->option('para')];
            }
            foreach ($paragraphs as $key => $paragraph) {
                $this->info($this->argument('type') . " {$book}-{$paragraph}");
                $data = [];
                switch ($this->argument('type')) {
                    case 'translation':
                        $data = $this->aiPaliTranslate($book, $paragraph);
                        break;
                    case 'nissaya':
                        $data = $this->aiNissayaTranslate($book, $paragraph);
                        break;
                    case 'wbw':
                        $data = $this->aiWBW($book, $paragraph);
                        break;
                    default:
                        # code...
                        break;
                }
                $this->save($data);
            }
        }
        return 0;
    }

    private function getPaliContent($book, $para)
    {
        $sentenceService = app(SearchPaliDataService::class);
        $sentences = PaliSentence::where('book', $book)
            ->where('paragraph', $para)
            ->orderBy('word_begin')
            ->get();
        if (!$sentences) {
            return null;
        }
        $json = [];
        foreach ($sentences as $key => $sentence) {
            $content = $sentenceService->getSentenceText($book, $para, $sentence->word_begin, $sentence->word_end);
            $id = "{$book}-{$para}-{$sentence->word_begin}-{$sentence->word_end}";
            $json[] = ['id' => $id, 'content' => $content['markdown']];
        }
        return $json;
    }

    private function aiPaliTranslate($book, $para)
    {
        $prompt = <<<md
        你是一个巴利语翻译助手。
        pali 是巴利原文的一个段落，json格式， 每条记录是一个句子。包括id 和 content 两个字段
        请翻译这个段落为简体中文。

        翻译要求
        1. 语言风格为现代汉语书面语，不要使用古汉语或者半文半白。
        2. 译文严谨，完全贴合巴利原文，不要加入自己的理解
        3. 巴利原文中的黑体字在译文中也使用黑体。其他标点符号跟随巴利原文，但应该替换为相应的汉字全角符号

        输出格式jsonl
        输出id 和 content 两个字段，
        id 使用巴利原文句子的id ,
        content 为中文译文

        直接输出jsonl数据，无需解释


    **输出范例**
    {"id":"1-2-3-4","content":"译文"}
    {"id":"2-3-4-5","content":"译文"}
    md;

        $pali = $this->getPaliContent($book, $para);
        $originalText = "```json\n" . json_encode($pali, JSON_UNESCAPED_UNICODE) . "\n```";
        Log::debug($originalText);
        if (!$this->model) {
            Log::error('model is invalid');
            return [];
        }
        $startAt = time();
        $response = $this->openAIService->setApiUrl($this->model['url'])
            ->setModel($this->model['model'])
            ->setApiKey($this->model['key'])
            ->setSystemPrompt($prompt)
            ->setTemperature(0.0)
            ->setStream(false)
            ->send("# pali\n\n{$originalText}\n\n");
        $complete = time() - $startAt;
        $translationText = $response['choices'][0]['message']['content'] ?? '[]';
        Log::debug("complete in {$complete}s", $translationText);
        $json = [];
        if (is_string($translationText)) {
            $json = LlmResponseParser::jsonl($translationText);
        }
        return $json;
    }
    private function aiWBW($book, $para)
    {
        $sysPrompt = <<<md
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
                    !empty($word->real->value) &&
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
            $response = $this->openAIService->setApiUrl($this->model['url'])
                ->setModel($this->model['model'])
                ->setApiKey($this->model['key'])
                ->setSystemPrompt($sysPrompt)
                ->setTemperature(0.7)
                ->setStream(false)
                ->send("```json\n{$tplText}\n```");
            $complete = time() - $startAt;
            $content = $response['choices'][0]['message']['content'] ?? '[]';
            Log::debug("ai response in {$complete}s content=" . $content);

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
        $sysPrompt = <<<md
        你是一个佛教翻译专家，精通巴利文和缅文
        ## 翻译要求：
        - 请将nissaya单词表中的巴利文和缅文分别翻译为中文
        - 输入格式为 巴利文:缅文
        - 一行是一条记录，翻译的时候，请不要拆分一行中的巴利文单词或缅文单词，一行中出现多个单词的，一起翻译
        - 输出csv格式内容，分隔符为"$"，
        - 字段如下：巴利文\$巴利文的中文译文\$缅文\$缅文的中文译文 #两个译文的语义相似度（%）

        **范例**：

        pana\$然而\$ဝါဒန္တရကား\$教义之说 #60%

        直接输出csv, 无需其他内容
        用```包裹的行为注释内容，也需要翻译和解释。放在最后面。如果没有```，无需处理
        md;

        $sentences = Sentence::nissaya()
            ->language('my') // 过滤缅文
            ->where('book_id', $book)
            ->where('paragraph', $para)
            ->orderBy('strlen')
            ->get();
        $result = [];
        foreach ($sentences as $key => $sentence) {
            $id = "{$sentence->book_id}-{$sentence->paragraph}-{$sentence->word_start}-{$sentence->word_end}";
            /*
            $nissaya = [];
            $rows = explode("\n", $sentence->content);
            foreach ($rows as $key => $row) {
                if (strpos('=', $row) >= 0) {
                    $factors = explode("=", $row);
                    $nissaya[] = Tools::MyToRm($factors[0]) . ':' . end($factors);
                } else {
                    $nissaya[] = $row;
                }
            }
            $nissayaText = json_encode(implode("\n", $nissaya), JSON_UNESCAPED_UNICODE);
            Log::debug($nissayaText);
            $startAt = time();
            $response = $this->openAIService->setApiUrl($this->model['url'])
                ->setModel($this->model['model'])
                ->setApiKey($this->model['key'])
                ->setSystemPrompt($sysPrompt)
                ->setTemperature(0.7)
                ->setStream(false)
                ->send("# nissaya\n\n{$nissayaText}\n\n");
            $complete = time() - $startAt;
            $content = $response['choices'][0]['message']['content'] ?? '';
            Log::debug("ai response in {$complete}s content=" . $content);
            */
            $aiNissaya = $this->nissayaTranslateService
                ->setModel($this->model)
                ->translate($sentence->content, false);
            Log::debug("ai response ", ['content' => $aiNissaya['data']]);
            $result[] = [
                'id' => $id,
                'content' => json_encode($aiNissaya['data'] ?? [], JSON_UNESCAPED_UNICODE),
                'content_type' => 'json'
            ];
        }
        return $result;
    }

    private function save($data)
    {
        //写入句子库
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
