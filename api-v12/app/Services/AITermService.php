<?php

namespace App\Services;

use App\Services\OpenSearchService;
use App\Services\TermService;
use App\Services\OpenAIService;
use App\Services\AIModelService;
use App\Http\Resources\AiModelResource;
use App\Http\Controllers\AuthController;
use App\DTO\Search\SearchDataDTO;

class AITermService
{
    protected $pageSize = 20;
    protected AiModelResource $model;

    protected $modelService;
    protected $modelToken;
    protected $openAIService;

    private $sysPrompt = <<<md
    请根据提供的文献搜素结果，撰写一个巴利术语的简体中文百科词条。

    搜素结果是json数组
    字段
    - title(标题)
    - content:(内容)
    - path(章节路径)
    - link(引用链接)

    要求：
    1. 参考维基百科的形式和结构
    2. 所有观点必须标明巴利文出处，使用我提供的link
    3. 引用巴利文原文时使用引号并斜体
    4. 提供完整的参考文献列表
    5. 保持学术中立性和客观性
    6. 请引用我提供的全部内容，不要有任何遗漏

    **观点引用标准格式：**
    《文献中文名》在《章节中文名》中指出/解释/说明："巴利文原文"（中文翻译及必要说明）。(link引用链接)

    如果某个观点有多个出处，请分别列出巴利文引用链接。范例
    《文献中文名》在《章节中文名》中指出/解释/说明："巴利文原文"（中文翻译及必要说明）。(link引用链接1)(link引用链接2)
    示例：
    《疑惑度脱新注》在《染色学处注释》中指出："*Kiriyākiriyanti nivāsanapārupanato, kappassa anādānato kiriyākiriyaṃ*"[9]（穿着下衣、披上衣是作为，不采取如法措施是不作为，故为作为-不作为）。{{para|id=202-1878|title=202-1878|style=reference}}

    词条结构应包括：
    - 标题（术语的巴利语、字面含义）
    - 简短定义段落
    - 目录
    - 词源与定义
    - 其他的，文献中提及的内容分类
    - 参考文献
    - 相关条目
    - 分类标签

    格式要求：
    - 使用Markdown格式
    - 标题层级清晰（#, ##, ###）
    - 直接输出百科正文，无需大标题
    - 引用格式：《文献中文名》在《章节中文名》中 + 动词 + "巴利文"  + （巴利文的中文译文）(link引用链接)
    - 引用动词可用：指出、解释、说明、定义、描述、强调、阐述、论述等
    - 巴利文使用罗马转写
    - 关键术语首次出现时提供巴利文和中文对照

    参考文献格式：
    [序号] 文献全称缩写, 具体章节, 标题, 段落编号
    md;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        AIModelService $model,
        OpenAIService $openAI,
    ) {
        $this->modelService = $model;
        $this->openAIService = $openAI;
    }
    public function setModel($id)
    {
        $this->model = $this->modelService->getModelById($id);
        $this->modelToken = AuthController::getUserToken($id);
    }

    private function query(string $word): array
    {
        $search = app(OpenSearchService::class);
        // 组装搜索参数
        $params = [
            'query'        => $word,
            'pageSize'     => $this->pageSize,
        ];
        $result = $search->search($params);

        $dto = SearchDataDTO::fromArray($result);
        $res = array();
        foreach ($dto->hits->items as $key => $item) {
            $res[] = [
                'title' => $item->title,
                'content' => $item->content,
                'path' => $item->path,
                'link' => $item->getParaLink()
            ];
        }
        return $res;
    }

    public function create(string $id)
    {
        // 获取术语
        $term = app(TermService::class)->get($id);
        // 全文搜索
        $query = $this->query($term->word);
        $res = json_encode($query, JSON_UNESCAPED_UNICODE);
        //LLM 生成
        $response = $this->openAIService->setApiUrl($this->model['url'])
            ->setModel($this->model['model'])
            ->setApiKey($this->model['key'])
            ->setSystemPrompt($this->sysPrompt)
            ->setTemperature(0.5)
            ->setStream(false)
            ->send("# 文献搜素结果\n\n{$res}\n\n" .
                "# 巴利术语\n\n{$term->word}\n\n");

        $content = $response['choices'][0]['message']['content'] ?? '';
        return $content;
    }
    public function update() {}
}
