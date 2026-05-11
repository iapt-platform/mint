<?php

namespace App\Services\AIAssistant;

use Illuminate\Support\Facades\Log;

use App\Services\OpenSearchService;
use App\Services\TermService;
use App\Services\OpenAIService;
use App\Services\AIModelService;
use App\Services\AuthService;

use App\Http\Resources\AiModelResource;
use App\DTO\Search\SearchDataDTO;

class AITermService
{
    protected $pageSize = 50;
    protected AiModelResource $model;


    protected $modelToken;


    private $sysPrompt = <<<md
    请根据提供的文献搜素结果，撰写一个巴利术语的简体中文百科词条。

    搜素结果是json数组
    字段
    - title:(标题)
    - content:(内容)
    - path:(章节路径)
    - link:(引用链接)

    link 是一个类似"{{para|id=202-1878|title=202-1878|style=reference}}" 的字符串，后面输出的时候请原样输出，不要做任何改变

    要求：
    1. 参考维基百科的形式和结构
    2. 所有观点必须标明巴利文出处，使用我提供的link
    3. 引用巴利文原文时使用引号并斜体
    4. 提供完整的参考文献列表
    5. 保持学术中立性和客观性
    6. 请引用我提供的全部内容，不要有任何遗漏
    7. 请在文档的开头输出一个模板 {{quality|pending}}

    **观点引用标准格式：**
    《文献中文名》在《章节中文名》中指出/解释/说明："巴利文原文"（中文翻译及必要说明）。[link]

        ### 引用标准与技术要求
    - **数据源绑定：** 请遍历我提供的 JSON 搜索结果。对于数组中的每一项，必须使用其对应的 `link` 字段值。
    - **硬性禁止：** 禁止在最终文档中出现 "link"、"引用链接" 或方括号占位符，必须替换为 JSON 中实际的链接文本。

    如果某个观点有多个出处，请分别列出巴利文引用链接。范例
    《文献中文名》在《章节中文名》中指出/解释/说明："巴利文原文"（中文翻译及必要说明）。[link引用链接1][link引用链接2]
    示例：
    《疑惑度脱新注》在《染色学处注释》中指出："*Kiriyākiriyanti nivāsanapārupanato, kappassa anādānato kiriyākiriyaṃ*"[9]（穿着下衣、披上衣是作为，不采取如法措施是不作为，故为作为-不作为）。{{para|id=202-1878|title=202-1878|style=reference}}

        **引用处理规则（按优先级）：**

    1. 【有明确论断句】直接引用该句巴利文：
    《X》在《Y》中指出："巴利文原文"（译文）。[link]

    2. 【叙事段落，无单一论断句】从段落中选取最能代表该段核心意思的
    一个完整句子作为代表句引用，不得跳过巴利文：
    《X》在《Y》中记载，[中文概述主要内容]；原文云："巴利文代表句"
    （该句译文）。(link)

    3. 【段落过长】从原文中截取开头或核心句，以省略号表示省略：
    "巴利文开头...（省略）"（译文说明省略范围）。[link]

    **绝对禁止：** 任何观点陈述只有中文转述而没有对应巴利文引用。
    如确实无法提取，须注明"（原文为纯叙事，节录如下）"并仍须给出
    原文片段。

    **输出前自检：**
    逐条检查每一个观点陈述，确认是否符合以下格式：
    [中文陈述] + "巴利文" + （译文） + [link]
    若有不符合的条目，返回修改后再输出。

    词条结构应包括：
    - 简短定义段落（不要标题直接输出段落内容）
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
    - 引用格式：《文献中文名》在《章节中文名》中 + 动词 + "巴利文"  + （巴利文的中文译文）[link]
    - 引用动词可用：指出、解释、说明、定义、描述、强调、阐述、论述等
    - 巴利文使用罗马转写
    - 关键术语首次出现时提供巴利文和中文对照

    参考文献格式：
    [序号] 文献全称缩写, 具体章节, 标题, 段落编号

    请在文档的底部输出分类标签

    请根据以下分类体系，为给定的词条打上分类标签。

    **分类体系：**

    一、经藏教义：1.1基本教义（苦、集、灭、道、缘起、十二缘起、三法印、无常、无我、五蕴、三宝、八正道、中道、善法、不善法）／1.2业与轮回（业、善业、不善业、无记业、业报、轮回、结生、再生、三界轮转）／1.3涅槃与解脱（涅槃、有余涅槃、无余涅槃、解脱、道、果、烦恼断除、结、漏）

    二、阿毗达摩：2.1心路与心类（欲界心、色界心、无色界心、出世间心、善心、不善心、无记心、心路、五门心路、意门心路、速行、有分、结生心、死心、转向心）／2.2心所法（遍一切心心所、杂心所、不善心所、美心所、贪、嗔、痴、慢、邪见、掉举、信、念、慧、悲、喜、舍）／2.3色法（四大种、地界、水界、火界、风界、净色、所造色、色聚、业生色、心生色、时节生色、食生色、真实色、非真实色）／2.4缘起与发趣法（二十四缘、因缘、所缘缘、增上缘、俱生缘、亲依止缘、前生缘、后生缘、业缘、果报缘、根缘、禅缘、道缘、相应缘、不相应缘、有缘、无有缘）

    三、禅修：3.1止禅（遍禅、不净观、随念、四梵住、入出息念、四界差别、禅相、取相、似相、近行定、安止定、禅那、禅支、无色定）／3.2观禅（名色分别、观智、生灭智、坏灭智、怖畏智、厌离智、行舍智、道智、果智、毘婆舍那、三相、无常随观、苦随观、无我随观、刹那定）

    四、律学：4.1戒条罪类（波罗夷、僧残、不定、舍堕、单堕、悔过、众学、灭诤、比库戒、比库尼戒、学处、犯罪、无犯、违犯条件）／4.2僧团制度与羯磨（羯磨、白羯磨、白二羯磨、白四羯磨、结界、布萨、自恣、受具足、出家、僧团、四方僧、惩罚羯磨、和合）／4.3僧侣生活与器具（三衣、钵、住处、精舍、雨安居、迦提那衣、头陀行、乞食、日用器具、净食）

    五、经典与文献：5.1经藏（长部、中部、相应部、增支部、小部、经名、品名、篇名）／5.2论藏与注疏（论藏、七论、义注、复注、清净道论、摄论、史书、藏外文献）／5.3本生与偈颂（本生、长老偈、长老尼偈、佛种姓、譬喻、天宫事、饿鬼事）

    六、世界观：6.1三界与诸天（欲界、色界、无色界、欲界天、梵天界、净居天、人间、有情居、天界层次）／6.2地狱与恶趣（地狱、无间地狱、寒冰地狱、孤独地狱、饿鬼界、畜生界、阿修罗界、四恶趣）／6.3神灵与非人（天神、梵天、夜叉、龙族、乾达婆、非人、护法神）

    七、人物：7.1佛（佛名、过去佛、二十八佛、独觉佛、菩萨）／7.2出家弟子（上首弟子、大弟子、比库弟子、比库尼弟子、沙弥、沙弥尼、在学尼）／7.3在家人（男居士、女居士、护法者、国王、婆罗门、施主、转轮圣王、王后、大长者）／7.4外道（外道名）

    八、地理：8.1国家与城镇（十六大国、国家、村落、市镇、寺院名）／8.2水系（河流、湖泊、海洋）／8.3山岳（山名、山脉）

    九、动植物：9.1动物（兽类、鸟类、爬行类、鱼类、昆虫、神话动物、龙族、金翅鸟、畜养动物、野生动物）／9.2植物（树木、花卉、草药、粮食作物、果实、圣树、菩提树类）

    十、巴利语言与语法：10.1语法术语（名词、动词、形容词、副词、格、数、性、时态、语式、复合词类型、前缀、后缀）／10.2语法缩写与标注（词性标注、格标注、语态标注、使役态、引用标记、出处标注）

    **输出规则：**
    - 必须从以上分类体系中选取，不得自创分类
    - 一个词条可打多个标签，但通常不超过3个
    - 先输出一级分类，再输出二级分类，再输出具体标签
    - 格式严格为：{{category|一级分类}} {{category|二级分类}} {{category|标签}}
    - 一级分类去除编号，例如"人物"而非"七、人物"
    - 二级分类去除编号，例如"佛"而非"7.1佛"
    - 只输出标签，不输出任何解释

    ---

    输出示例：

    # 分类标签

    {{category|经典与文献}} {{category|经藏}} {{category|中部}} {{category|义注}}
    md;

    private $sysPromptTags = <<<md
    你是一个巴利语佛教百科词条分类专家。请根据以下分类体系，为给定的词条打上分类标签。

    在分类标签的前面添加一个**词条概述**

    分类标签

    请根据以下分类体系，为给定的词条打上分类标签。

    **分类体系：**

    一、经藏教义：1.1基本教义（苦、集、灭、道、缘起、十二缘起、三法印、无常、无我、五蕴、三宝、八正道、中道、善法、不善法）／1.2业与轮回（业、善业、不善业、无记业、业报、轮回、结生、再生、三界轮转）／1.3涅槃与解脱（涅槃、有余涅槃、无余涅槃、解脱、道、果、烦恼断除、结、漏）

    二、阿毗达摩：2.1心路与心类（欲界心、色界心、无色界心、出世间心、善心、不善心、无记心、心路、五门心路、意门心路、速行、有分、结生心、死心、转向心）／2.2心所法（遍一切心心所、杂心所、不善心所、美心所、贪、嗔、痴、慢、邪见、掉举、信、念、慧、悲、喜、舍）／2.3色法（四大种、地界、水界、火界、风界、净色、所造色、色聚、业生色、心生色、时节生色、食生色、真实色、非真实色）／2.4缘起与发趣法（二十四缘、因缘、所缘缘、增上缘、俱生缘、亲依止缘、前生缘、后生缘、业缘、果报缘、根缘、禅缘、道缘、相应缘、不相应缘、有缘、无有缘）

    三、禅修：3.1止禅（遍禅、不净观、随念、四梵住、慈、悲、喜、舍、入出息念、四界差别、禅相、取相、似相、近行定、安止定、禅那、禅支、无色定）／3.2观禅（名色分别、观智、生灭智、坏灭智、怖畏智、厌离智、行舍智、道智、果智、毘婆舍那、三相、无常随观、苦随观、无我随观、刹那定）

    四、律学：4.1戒条罪类（波罗夷、僧残、不定、舍堕、单堕、悔过、众学、灭诤、比库戒、比库尼戒、学处、犯罪、无犯、违犯条件）／4.2僧团制度与羯磨（羯磨、白羯磨、白二羯磨、白四羯磨、结界、布萨、自恣、受具足、出家、僧团、四方僧、惩罚羯磨、和合）／4.3僧侣生活与器具（三衣、钵、住处、精舍、雨安居、迦提那衣、头陀行、乞食、日用器具、净食）

    五、经典与文献：5.1经藏（长部、中部、相应部、增支部、小部、经名、品名、篇名）／5.2论藏与注疏（论藏、七论、义注、复注、清净道论、摄论、史书、藏外文献）／5.3本生与偈颂（本生、长老偈、长老尼偈、佛种姓、譬喻、天宫事、饿鬼事）

    六、世界观：6.1三界与诸天（欲界、色界、无色界、欲界天、梵天界、净居天、人间、有情居、天界层次）／6.2地狱与恶趣（地狱、无间地狱、寒冰地狱、孤独地狱、饿鬼界、畜生界、阿修罗界、四恶趣）／6.3神灵与非人（天神、梵天、夜叉、龙族、乾达婆、非人、护法神）

    七、人物：7.1佛（佛名、过去佛、二十八佛、独觉佛、菩萨）／7.2出家弟子（上首弟子、大弟子、比库弟子、比库尼弟子、沙弥、沙弥尼、在学尼）／7.3在家人（男居士、女居士、护法者、国王、婆罗门、施主、转轮圣王、王后、大长者）／7.4外道（外道名）

    八、地理：8.1国家与城镇（十六大国、国家、村落、市镇、寺院名）／8.2水系（河流、湖泊、海洋）／8.3山岳（山名、山脉）

    九、动植物：9.1动物（兽类、鸟类、爬行类、鱼类、昆虫、神话动物、龙族、金翅鸟、畜养动物、野生动物）／9.2植物（树木、花卉、草药、粮食作物、果实、圣树、菩提树类）

    十、巴利语言与语法：10.1语法术语（名词、动词、形容词、副词、格、数、性、时态、语式、复合词类型、前缀、后缀）／10.2语法缩写与标注（词性标注、格标注、语态标注、使役态、引用标记、出处标注）

    **输出规则：**
    - 必须从以上分类体系中选取，不得自创分类
    - 一个词条可打多个标签，但通常不超过3个
    - 先输出一级分类，再输出二级分类，再输出具体标签
    - 格式严格为：`{{category|一级分类}}` `{{category|二级分类}}` `{{category|标签}}`
    - 一级分类去除编号，例如"人物"而非"七、人物"
    - 二级分类去除编号，例如"佛"而非"7.1佛"
    - 只输出标签，不输出任何解释

    ---

    输出示例：

    (词条的概述)

    # 分类标签

    {{category|经典与文献}} {{category|经藏}} {{category|中部}} {{category|义注}}
    md;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        protected AIModelService $modelService,
        protected OpenAIService $openAIService,
        protected TermService $termService
    ) {}
    public function setModel($id)
    {
        $this->model = $this->modelService->getModelById($id);
        $this->modelToken = AuthService::getUserToken($id);
        return $this;
    }

    private function query(string $word): array
    {
        $search = app(OpenSearchService::class);
        // 组装搜索参数
        $params = [
            'query'        => $word,
            'resourceType' => 'tipitaka',
            'granularity' => 'paragraph',
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
                'pid' => $item->getParaId(),
                'link' => $item->getParaLink()
            ];
        }
        Log::debug('query ' . count($res));
        return $res;
    }

    public function update(string $id)
    {
        // 获取术语
        $term = $this->termService->getRaw($id);
        // 全文搜索
        $query = $this->query($term->word);

        $res = json_encode($query, JSON_UNESCAPED_UNICODE);
        $resText = "# 搜索结果\n```json\n{$res}\n```\n";
        $termText = "# 巴利术语\n\n{$term->word}\n\n";
        //LLM 生成
        $response = $this->openAIService->setApiUrl($this->model['url'])
            ->setModel($this->model['model'])
            ->setApiKey($this->model['key'])
            ->setSystemPrompt($this->sysPrompt)
            ->setTemperature(0.5)
            ->setStream(false)
            ->send($resText . $termText);

        $content = $response['choices'][0]['message']['content'] ?? '';

        //输出自检报告
        Log::debug('llm response', ['strlen' => $content]);
        $paraIds = $this->extractAllParaIds($content);
        Log::debug('has paragraph ref ', ['total' => count($paraIds), 'id' => $paraIds]);
        $searchPid = array_map(fn($item) => $item['pid'], $query);
        $diff = array_values(array_diff($paraIds, $searchPid));
        Log::debug('diff', ['total' => count($diff), 'data' => $diff]);

        $this->termService->update($id, ['note' => $content]);
        return $content;
    }

    public function create(string $word) {}

    /**
     * Extract all unique ID values from MediaWiki template parameter strings
     *
     * Parses a string that may contain multiple "{{para|...}}" templates
     * and returns an array of unique 'id' parameter values found.
     *
     * @param string $str The input string containing zero or more {{para|...}} templates
     * @return array<int, string> Array of unique extracted ID values (e.g., ['16-1376', 'ABC-123'])
     *                            Returns empty array if no IDs are found
     *
     * @example
     *   // Single template
     *   extractAllParaIds('{{para|id=16-1376|title=test}}')
     *   // returns ['16-1376']
     *
     *   // Multiple templates with duplicates
     *   extractAllParaIds('{{para|id=16-1376}} and {{para|id=16-1376|style=ref}}')
     *   // returns ['16-1376'] (duplicate removed)
     *
     *   // Multiple unique IDs
     *   extractAllParaIds('{{para|id=16-1376}} {{para|id=ABC-123}} {{para|id=16-1376}}')
     *   // returns ['16-1376', 'ABC-123']
     */
    public function extractAllParaIds(string $str): array
    {
        $ids = [];

        // Find all {{para|...}} patterns
        if (preg_match_all('/{{para\|(.*?)}}/', $str, $matches)) {
            foreach ($matches[1] as $content) {
                // Extract id= value from each template content
                if (preg_match('/id=([^|&}]+)/', $content, $idMatch)) {
                    $ids[] = $idMatch[1];
                }
            }
        }

        // Remove duplicates and preserve order of first occurrence
        return array_values(array_unique($ids));
    }
}
