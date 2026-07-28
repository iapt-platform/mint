<?php

namespace App\Services\AIAssistant;

use App\Helpers\LlmResponseParser;
use App\Http\Api\ChannelApi;
use App\Http\Resources\AiModelResource;
use App\Models\PaliSentence;
use App\Models\PaliText;
use App\Models\Sentence;
use App\Services\OpenAIService;
use App\Services\SearchPaliDataService;
use App\Services\TermService;
use Illuminate\Support\Facades\Log;

/**
 * 巴利原文 -> 简体中文 的多步骤翻译工作流。
 *
 * 支持五个步骤，可单独运行或按顺序串联：
 * - translate：根据巴利原文产出译文
 * - term：术语标注，根据 channel/社区术语表把译文中的中文术语替换为 [[巴利术语]] 标记
 * - review：对已有译文打分并给出问题清单（不修改译文）
 * - revise：根据 review 的问题清单产出改进后的译文
 * - evaluate：质量评估，对照原文找出译文的真实问题，按级别就地用 HTML span 标注译文（颜色=严重程度，title=问题+建议），作为工作流最后一步
 *
 * 单独运行 term / review / revise / evaluate 时，已有译文从输出 channel 读取。
 *
 * 工作流自动提取同段落的 nissaya（巴利逐词缅文释义）注入 translate / review / evaluate
 * 作为参考资料（见 PaliNissayaReferenceService）；无 nissaya 数据的段落不受影响。
 */
class PaliTranslateService
{
    /**
     * 可用的工作流步骤
     */
    public const STEPS = ['translate', 'term', 'review', 'revise', 'evaluate'];

    /**
     * 支持注入 nissaya 参考资料的步骤（revise 基于 review 意见工作，无需 nissaya）
     */
    public const NISSAYA_STEPS = ['translate', 'review', 'revise', 'evaluate'];

    protected AiModelResource $model;

    protected ?bool $thinking = null;

    protected bool $stream = false;

    /**
     * 输出 channel（用于单独运行 review / revise 时读取已有译文）
     *
     * @var array<string, mixed>
     */
    protected array $workChannel = [];

    /**
     * 启用 nissaya 参考资料的步骤；默认全部支持的步骤都注入
     *
     * @var string[]
     */
    protected array $nissayaSteps = self::NISSAYA_STEPS;

    /**
     * translate 步骤的提示词
     */
    protected string $translatePrompt = <<<'md'
        你是一个巴利语翻译助手。
        pali 是巴利原文的一个段落，json格式， 每条记录是一个句子。包括id 和 content 两个字段
        请翻译这个段落为简体中文。

        若用户额外提供 nissaya（巴利原文的逐词缅文释义，与 pali 通过 id 一一对应，按词列出每个巴利词的语法解析与缅文释义，形如「巴利词= 缅文释义」）：它是判断词义、修饰关系、指代关系和句子结构最权威的依据，翻译时应优先参照 nissaya 确定原意，遇到歧义时以 nissaya 为准。

        若用户额外提供 glossary 。请参考glossary处理术语。
        glossary：巴利语术语表，json 数组，每个元素含 word（巴利术语原形）、meaning（该术语的中文释义）、tag（分类标签）三个字段。
        请查看巴利原文，根据内容判断术语表中的术语是否适用于这个巴利文段落。翻译的时候使用术语标记代替译文，范例如下： `[[巴利术语原形]]`（双方括号包裹 glossary 中给出的 word 原形）
        若用户没有提供glossary , 正常翻译即可。

        翻译要求
        1. 语言风格为现代汉语，**绝对不要**使用古汉语或者半文半白。**不要参考**阿含经和元亨寺语言风格。
        2. 译文严谨，完全贴合巴利原文，不要加入自己的理解
        3. 经名、人名、地名等专有名词：有约定俗成的标准译名时优先使用标准译名；没有标准译名的，尽量按词义意译；意译确有困难的再使用音译。同一专有名词在全文中译名须前后一致
        4. 巴利原文中的黑体字在译文中也使用黑体。其他标点符号跟随巴利原文，但应该替换为相应的汉字全角符号

        输出格式jsonl
        输出id 和 content 两个字段，
        id 使用巴利原文句子的id ,
        content 为中文译文

        直接输出jsonl数据，无需解释


        **输出范例**
        {"id":"1-2-3-4","content":"译文"}
        {"id":"2-3-4-5","content":"译文"}
        md;

    /**
     * term 步骤的提示词：根据术语表把译文中的中文术语替换为 [[巴利术语]] 标记。
     */
    protected string $termPrompt = <<<'md'
        你是一个巴利语佛教术语标注助手。
        用户会提供：
        - pali：巴利原文段落，json，每条记录是一个句子，含 id 和 content 两个字段。
        - translation：已有的简体中文译文，json，与 pali 通过 id 一一对应。
        - glossary：巴利语术语表，json 数组，每个元素含 word（巴利术语原形）、meaning（该术语的中文释义）、tag（分类标签）三个字段。

        你的任务：在**不改变译文其他内容**的前提下，把 translation 中对应这些术语的中文词语替换为术语标记 `[[巴利术语原形]]`（双方括号包裹 glossary 中给出的 word 原形），其余内容、措辞、标点、黑体等格式**一字不动**地保留。

        判断与替换规则：
        1. 只有当某个 glossary 术语在该句的巴利原文（pali）中确实出现（包括其屈折变化、变格、复合、连音等形式），且译文中存在与之对应的中文译词时，才进行替换。原文中并未出现的术语，一律不替换。
        2. **语境判断（最重要）**：必须结合 glossary 中该术语的 meaning，判断它在当前句子语境下是否确实用作该术语义。同一个巴利词在论藏（阿毗达摩）中可能是专门术语，在经藏中却只是普通词汇。若根据 meaning 与上下文判断该词在此处并非用作术语义，则**不要**替换，保持原中文译文。
        3. 宁缺毋滥：没有十足把握时不要标记。不确定的一律保持原译文，不加双方括号。
        4. 不要新增术语表中没有的标记，也不要把已经正确翻译的普通词误标为术语。
        5. 译文中可能已存在 `[[...]]` 术语标记，原样保留即可，不要重复包裹。

        输出格式jsonl，每行对应一个句子，包含两个字段：
        id：与原文相同的句子id
        content：标注后的中文译文（无需替换的句子与原译文完全一致）

        直接输出jsonl数据，无需解释

        **输出范例**
        {"id":"1-2-3-4","content":"住在[[rājagaha]]"}
        {"id":"2-3-4-5","content":"原样保留的译文"}
        md;

    /**
     * review 步骤的提示词：对已有译文打分并指出问题，不修改译文。
     */
    protected string $reviewPrompt = <<<'md'
        你是一个资深的巴利语翻译审校专家。
        用户会提供巴利原文（pali）以及一份待审校的简体中文译文（translation），两者均为 json，通过 id 一一对应。
        用户还可能提供 nissaya（巴利原文的逐词缅文释义，与 pali 通过 id 对应，按词列出每个巴利词的语法解析与缅文释义，形如「巴利词= 缅文释义」）：它是判断词义、修饰关系、指代关系和句子结构最权威的依据，审校时应以 nissaya 为准核对译文是否贴合原意，发现译文与 nissaya 冲突的，须在 issues 中指出。

        译文中可能出现 `[[巴利术语]]`（双方括号包裹巴利语原词）形式的标记：这是**有意保留**的术语标记，表示该词按术语表以巴利原形呈现、刻意不译为中文，属于正确处理。审校时**完全忽略**这些 `[[...]]` 标记，**不要**把它当作漏译、未翻译、误译或格式问题，不要因此扣分，也不要在 issues 中提及；只需正常审校其余中文译文。

        请逐句审校译文，但**不要修改译文**，只输出审校意见。
        审校维度：
        1. 准确性：译文是否完全贴合巴利原文，有无漏译、增译、误译
        2. 专有名词：人名、地名、经名等专有名词的译名是否正确、是否使用约定俗成的标准译名，有无与读音相近的其他专名混淆（如把 Aṭṭhakanāgara“八城”误作 Āṭānāṭiya“阿吒曩胝”），同一专名在段落内译名是否前后一致
        3. 语言：是否为规范的现代汉语书面语，有无古汉语或半文半白
        4. 格式：黑体、全角标点是否符合要求

        输出格式jsonl，每条记录对应一个句子，包含三个字段：
        id：与原文相同的句子id
        score：译文质量评分，整数 0-100
        issues：问题清单，简明中文描述；若没有问题则输出空字符串

        直接输出jsonl数据，无需解释

        **输出范例**
        {"id":"1-2-3-4","score":85,"issues":"漏译了 bhagavā；标点未使用全角"}
        {"id":"2-3-4-5","score":100,"issues":""}
        md;

    /**
     * revise 步骤的提示词：根据审校意见产出改进后的译文。
     */
    protected string $revisePrompt = <<<'md'
        你是一个巴利语翻译助手。
        用户会提供巴利原文（pali）、当前译文（translation）以及审校意见（review），均为 json，通过 id 一一对应。

        请根据审校意见（review）修订当前译文（translation），产出改进后的译文。
        修订要求：
        1. 针对 review 中 issues 指出的问题进行修正
        2. issues 为空、且 score 较高的句子可保持原译文
        3. 语言风格为现代汉语书面语，不要使用古汉语或者半文半白
        4. 译文严谨，完全贴合巴利原文，不要加入自己的理解
        5. 经名、人名、地名等专有名词：有约定俗成的标准译名时优先使用标准译名；没有标准译名的，尽量按词义意译；意译确有困难的再使用音译。同一专有名词在全文中译名须前后一致
        6. 巴利原文中的黑体字在译文中也使用黑体。其他标点符号跟随巴利原文，但应替换为相应的汉字全角符号

        输出格式jsonl
        输出id 和 content 两个字段，
        id 使用巴利原文句子的id ,
        content 为修订后的中文译文

        直接输出jsonl数据，无需解释

        **输出范例**
        {"id":"1-2-3-4","content":"译文"}
        {"id":"2-3-4-5","content":"译文"}
        md;

    /**
     * revise 步骤的提示词：根据审校意见产出改进后的译文。
     */
    protected string $reviseWithNissayaPrompt = <<<'md'
    你是一个巴利语翻译助手。
    用户会提供巴利原文（pali）、当前译文（translation）以及缅文逐词解析（nissaya），均为 json，通过 id 一一对应。
    nissaya（巴利原文的逐词缅文释义，与 pali 通过 id 对应，按词列出每个巴利词的语法解析与缅文释义，形如「巴利词= 缅文释义」）：它是判断词义、修饰关系、指代关系和句子结构最权威的依据，审校时应以 nissaya 为准核对译文是否贴合原意，发现译文与 nissaya 冲突的，须修改译文。

    请根据缅文逐词解析（nissaya）修订当前译文（translation），产出改进后的译文。
    修订要求：
    1. 针对 nissaya 中给出的的代词指代，修改译文，将译文中对应的代词后面加上代词指代的对象。使用中文小括号包裹代词指代对象。范例：他（婆罗门）
    2. 检查准确性：译文是否完全贴合巴利原文，有无漏译、增译、误译。
    3. 词义抉择有没有和nissaya中的缅文解释有冲突的。如果有，需要更正。请注意nissaya中的缅文，有时候是巴利文的意思，有些是对巴利文的解释说明。请根据具体情况抉择。比较长的解释说明，可以插入为脚注。格式： `脚注内容`. 脚注内容中不要有换行。
    3. 语言风格为现代汉语书面语，不要使用古汉语或者半文半白
    4. 译文严谨，完全贴合巴利原文，不要加入自己的理解
    5. 译文中的术语标记是 [[巴利文术语]] 请保留，不要修改。
    6. 巴利原文中的黑体字在译文中也使用黑体。其他标点符号跟随巴利原文，但应替换为相应的汉字全角符号

    输出格式jsonl
    输出id 和 content 两个字段，
    id 使用巴利原文句子的id ,
    content 为修订后的中文译文

    直接输出jsonl数据，无需解释

    **输出范例**
    {"id":"1-2-3-4","content":"译文"}
    {"id":"2-3-4-5","content":"译文"}
    md;
    /**
     * evaluate 步骤的提示词：对照原文找出译文真实问题，按级别就地标注译文。
     */
    protected string $evaluatePrompt = <<<'md'
        你是一位资深的巴利语译文质量检查员，精通巴利原典与注释书传统。
        用户会提供巴利原文（pali）与对应的简体中文译文（translation），两者均为 json，通过 id 一一对应。
        用户还可能提供 nissaya（巴利原文的逐词缅文释义，与 pali 通过 id 对应，按词列出每个巴利词的语法解析与缅文释义，形如「巴利词= 缅文释义」）：它是判断词义、修饰关系、指代关系和句子结构最权威的依据，审查时应以 nissaya 为准核对译文，凡译文与 nissaya 冲突处即为真实问题，须按级别标注。

        你的任务：逐句对照原文审查译文，找出其中**确实存在**的翻译问题，按严重程度分级，并把问题**就地标注在译文上**，最后输出标注后的译文。

        # 问题分级与类型
        问题分为四个**级别**（severity），每个级别下又分若干**类型**（type）。请先判断片段属于哪个级别，再从该级别下选出最贴切的类型名。**级别从高到低，标注时只取最严重的一级。**

        - fatal（严重错误）：会让读者对经文意思有严重误解。类型：
            - 严重失真：主、谓（含非谓语动词）、宾中有一项判断错误，导致句子意思严重失真
            - 教理违背：句子意思违背基本教理原则
        - error（错误，有举必究，必须修改）：类型：
            - 漏译：原文中有的内容在译文中缺失
            - 多译：译文中增加了原文没有的内容
            - 词义误译：词语的意思翻译错误
            - 修饰错误：修饰关系判断错误
            - 误解表达：表达方式会导致读者误解
            - 义理不符：义理与注释书不符
            - 用词不符：用词与注释书不符
            - 指代错误：代词指代的对象错误
        - warning（待提升）：类型：
            - 语意不明：关键词语意不明确
            - 缺少注释：二意场合没有添加注释
            - 指代不明：代词指代不够明确
            - 汉语语病：不导致误解的汉语语病
            - 标点错误：标点符号使用错误
            - 误用标记：不该使用术语标记时使用了术语标记
            - 逻辑不规范：整句逻辑表达不规范
        - suggestion（可提升，仅影响阅读体验）：类型：
            - 表达晦涩：语言表达不够流畅
            - 代词指代：代词指代不够明确
            - 风格统一：语言风格不统一
            - 术语标记：该使用而没有使用术语标记
            - 缺少注释：不常用术语需要编写注释或者百科
            - 句式复杂：复杂的嵌套句整句语言逻辑理解困难（对读者不友好）

        若同一句子存在多重问题，只按其中最严重的一级标注，并选用该级别下最贴切的类型名。

        # 标注方法
        只对译文中**有问题的最小片段**，用如下 span 原地包裹（不改动译文本身的文字与黑体等格式，仅在外层套标签）：

        <span class='evaluate evaluate-级别'  title='类型·级别：问题简述｜建议：修改建议'>有问题的译文片段</span>

        其中 class 里的「级别」与 title 里的「级别」都必须是 fatal / error / warning / suggestion 之一；title 里的「类型」必须是上面对应级别下列出的类型名（例如 语意不明、漏译、严重失真）。**级别在前判定，类型在该级别内挑选**，不要张冠李戴（例如 语意不明 只能配 warning）。

        **span 的属性一律用单引号**（class='...'  title='...'），不要用双引号——因为 content 整体是 JSON 字符串、本身由双引号包裹，属性再用双引号极易因转义出错导致整行 JSON 解析失败、整句被丢弃。title 等属性值内若要引用文字，请使用中文全角引号「」或‘’，**严禁**出现 ASCII 双引号(")或单引号(')。

        title 写法：先写「类型·级别」，再用一句话说清问题是什么，最后用「｜建议：」给出具体可操作的修改建议。

        # 必须遵守的原则
        1. 只标注你**确有把握**的真实问题；拿不准就不标。
        2. 宁缺毋滥：不要为凑数而标注，不要把正确译文误标为问题，严禁过度标注。没有问题的句子，content 与原译文**一字不差**地原样返回，不加任何标签。
        3. 标注片段尽量短，精准定位到出问题的词或短语，不要整句包裹。
        4. 完整保留译文原有的文字与格式（黑体 ** **、全角标点等）。
        5. 给出「建议」前必须自检：建议的译文是否与被标注的原文片段（忽略加粗符号）不同。若完全相同，说明这不构成真实问题，请不要标注，直接原样返回该片段。

        # 输出格式 jsonl
        每行对应一个句子，包含两个字段：
        id：与原文相同的句子 id
        content：标注后的译文（无问题则与原译文完全一致）

        直接输出 jsonl 数据，无需解释。

        **输出范例**（注意 span 属性用单引号，整行是合法 JSON）
        {"id":"1-2-3-4","content":"他于<span class='evaluate evaluate-error'  title='漏译·error：原文 bhagavā 未译出｜建议：补译为‘世尊’'>那时</span>住在王舍城。"}
        {"id":"2-3-4-5","content":"完全正确的译文原样返回。"}
        md;

    public function __construct(
        protected OpenAIService $openAIService,
        protected SearchPaliDataService $searchPaliDataService,
        protected PaliNissayaReferenceService $nissayaReference,
        protected TermService $termService,
    ) {}

    /**
     * 设置模型配置
     */
    public function setModel(AiModelResource $model): self
    {
        $this->model = $model;

        return $this;
    }

    /**
     * 设置 deepseek thinking 开关；传入 null 时保持默认（不改动）
     */
    public function setThinking(?bool $thinking): self
    {
        if ($thinking === null) {
            return $this;
        }
        $this->thinking = $thinking;

        return $this;
    }

    /**
     * 设置是否流式输出
     */
    public function setStream(bool $stream): self
    {
        $this->stream = $stream;

        return $this;
    }

    /**
     * 设置输出 channel（用于单独运行 review / revise 时读取已有译文）
     *
     * @param  array<string, mixed>  $channel
     */
    public function setChannel(array $channel): self
    {
        $this->workChannel = $channel;

        return $this;
    }

    /**
     * 设置启用 nissaya 参考资料的步骤（可单独开关 translate / review / evaluate）；
     * 仅保留 NISSAYA_STEPS 支持的步骤，非法值自动忽略。
     *
     * @param  string[]  $steps
     */
    public function setNissayaSteps(array $steps): self
    {
        $this->nissayaSteps = array_values(array_intersect($steps, self::NISSAYA_STEPS));

        return $this;
    }

    /**
     * 设置 translate 步骤的提示词
     */
    public function setTranslatePrompt(string $prompt): self
    {
        $this->translatePrompt = $prompt;

        return $this;
    }

    /**
     * 设置 review 步骤的提示词
     */
    public function setReviewPrompt(string $prompt): self
    {
        $this->reviewPrompt = $prompt;

        return $this;
    }

    /**
     * 设置 revise 步骤的提示词
     */
    public function setRevisePrompt(string $prompt): self
    {
        $this->revisePrompt = $prompt;

        return $this;
    }

    /**
     * 设置 evaluate 步骤的提示词
     */
    public function setEvaluatePrompt(string $prompt): self
    {
        $this->evaluatePrompt = $prompt;

        return $this;
    }

    /**
     * 执行多步骤工作流，返回最终译文（list of ['id' => ..., 'content' => ...]）。
     *
     * @param  string[]  $steps  translate / review / revise 的有序子集
     * @return array<int, array{id: string, content: string}>
     */
    public function run(array $steps, int $book, int $para): array
    {
        if (! isset($this->model)) {
            Log::error('PaliTranslate: model is invalid');

            return [];
        }

        $pali = $this->getPaliContent($book, $para);

        // 提取同段落的 nissaya（巴利逐词缅文释义）作为参考资料，按 nissayaSteps 注入对应步骤
        $nissaya = $this->nissayaReference->forParagraph($book, $para);
        Log::debug('PaliTranslate: nissaya 参考', ['count' => count($nissaya), 'steps' => $this->nissayaSteps]);

        // 工作流不以 translate 开头时，从输出 channel 读取已有译文作为输入
        $translation = in_array('translate', $steps, true)
            ? []
            : $this->existingTranslation($book, $para);

        $review = [];

        foreach ($steps as $step) {
            switch ($step) {
                case 'translate':
                    $glossary = $this->buildTermGlossary($book, $para);
                    Log::debug('PaliTranslate: term 术语表', ['count' => count($glossary)]);
                    $translation = $this->translate($pali, $this->nissayaFor('translate', $nissaya), $glossary);
                    break;
                case 'term':
                    // 构造 channel + 社区术语表（经系统术语表过滤、channel 优先），按语境把译文中的中文术语替换为 [[巴利术语]]
                    $glossary = $this->buildTermGlossary($book, $para);
                    Log::debug('PaliTranslate: term 术语表', ['count' => count($glossary)]);
                    $translation = $this->markTerms($pali, $translation, $glossary);
                    break;
                case 'review':
                    $review = $this->review($pali, $translation, $this->nissayaFor('review', $nissaya));
                    Log::debug('PaliTranslate: review 完成', ['review' => $review]);
                    break;
                case 'revise':
                    $nsy = $this->nissayaFor('revise', $nissaya);
                    Log::debug('PaliTranslate: revise nissaya', ['count' => count($nsy)]);
                    if (!empty($nsy)) {
                        $translation = $this->reviseWithNissaya($pali, $translation, $nsy);
                    } else if (!empty($review)) {
                        $translation = $this->revise($pali, $translation, $review);
                    } else {
                        $translation = [];
                    }
                    break;
                case 'evaluate':
                    $translation = $this->evaluate($pali, $translation, $this->nissayaFor('evaluate', $nissaya));
                    break;
            }
        }

        // 只有产出译文的步骤（translate / term / revise / evaluate）才返回可写库的数据；
        // evaluate 写库内容为带 HTML 标注的译文；仅 review 时报告已写入日志，无需重新保存原译文
        $producesTranslation = (bool) array_intersect($steps, ['translate', 'term', 'revise', 'evaluate']);

        return $producesTranslation ? $translation : [];
    }

    /**
     * 提取段落的巴利原文，按句子返回 ['id' => ..., 'content' => ...]
     *
     * @return array<int, array{id: string, content: string}>
     */
    public function getPaliContent(int $book, int $para): array
    {
        $sentences = PaliSentence::where('book', $book)
            ->where('paragraph', $para)
            ->orderBy('word_begin')
            ->get();

        $json = [];
        foreach ($sentences as $sentence) {
            $content = $this->searchPaliDataService->getSentenceContent($book, $para, $sentence->word_begin, $sentence->word_end);
            $id = "{$book}-{$para}-{$sentence->word_begin}-{$sentence->word_end}";
            $json[] = ['id' => $id, 'content' => $content['markdown']];
        }

        return $json;
    }

    /**
     * translate 步骤：根据巴利原文产出译文
     *
     * @param  array<int, array{id: string, content: string}>  $pali
     * @param  array<int, array{id: string, content: string}>  $nissaya  巴利逐词缅文释义参考资料，可空
     * @return array<int, array{id: string, content: string}>
     */
    public function translate(array $pali, array $nissaya = [], array $glossary = []): array
    {
        $userText = "# pali\n\n" . $this->jsonBlock($pali) . "\n\n"
            . $this->nissayaSection($nissaya);
        if (!empty($glossary)) {
            $userText .= "# glossary\n\n" . $this->jsonBlock($glossary) . "\n\n";
        }
        Log::debug('PaliTranslate: translate', ['input' => $userText]);

        return $this->sendForIds('translate', $this->translatePrompt, $userText, $this->idsOf($pali));
    }

    /**
     * term 步骤：根据术语表，把已有译文中对应的中文术语替换为 [[巴利术语]] 标记。
     * 由大模型结合巴利原文与术语 meaning 做语境判断；译文或术语表为空时原样返回。
     *
     * @param  array<int, array{id: string, content: string}>  $pali
     * @param  array<int, array{id: string, content: string}>  $translation
     * @param  array<int, array{word: string, meaning: string, tag: string}>  $glossary
     * @return array<int, array{id: string, content: string}>
     */
    public function markTerms(array $pali, array $translation, array $glossary): array
    {
        if (empty($translation) || empty($glossary)) {
            return $translation;
        }

        $userText = "# pali\n\n" . $this->jsonBlock($pali) . "\n\n"
            . "# translation\n\n" . $this->jsonBlock($translation) . "\n\n"
            . "# glossary\n\n" . $this->jsonBlock($glossary) . "\n\n";
        Log::debug('PaliTranslate: term', ['input' => $userText]);

        return $this->sendForIds('term', $this->termPrompt, $userText, $this->idsOf($translation));
    }

    /**
     * review 步骤：对已有译文打分并给出问题清单（不修改译文）
     *
     * @param  array<int, array{id: string, content: string}>  $pali
     * @param  array<int, array{id: string, content: string}>  $translation
     * @param  array<int, array{id: string, content: string}>  $nissaya  巴利逐词缅文释义参考资料，可空
     * @return array<int, array{id: string, score: int, issues: string}>
     */
    public function review(array $pali, array $translation, array $nissaya = []): array
    {
        $userText = "# pali\n\n" . $this->jsonBlock($pali) . "\n\n"
            . "# translation\n\n" . $this->jsonBlock($translation) . "\n\n"
            . $this->nissayaSection($nissaya);
        Log::debug('PaliTranslate: review', ['input' => $userText]);

        return $this->sendForIds('review', $this->reviewPrompt, $userText, $this->idsOf($translation));
    }

    /**
     * revise 步骤：根据审校意见产出改进后的译文
     *
     * @param  array<int, array{id: string, content: string}>  $pali
     * @param  array<int, array{id: string, content: string}>  $translation
     * @param  array<int, array{id: string, score: int, issues: string}>  $review
     * @return array<int, array{id: string, content: string}>
     */
    public function revise(array $pali, array $translation, array $review): array
    {
        $userText = "# pali\n\n" . $this->jsonBlock($pali) . "\n\n"
            . "# translation\n\n" . $this->jsonBlock($translation) . "\n\n"
            . "# review\n\n" . $this->jsonBlock($review) . "\n\n";
        Log::debug('PaliTranslate: revise', ['input' => $userText]);

        return $this->sendForIds('revise', $this->revisePrompt, $userText, $this->idsOf($translation));
    }

    /**
     * revise 步骤：根据审校意见产出改进后的译文
     *
     * @param  array<int, array{id: string, content: string}>  $pali
     * @param  array<int, array{id: string, content: string}>  $translation
     * @param  array<int, array{id: string, score: int, issues: string}>  $review
     * @return array<int, array{id: string, content: string}>
     */
    public function reviseWithNissaya(array $pali, array $translation, array $nissaya = []): array
    {
        $userText = "# pali\n\n" . $this->jsonBlock($pali) . "\n\n"
            . "# translation\n\n" . $this->jsonBlock($translation) . "\n\n"
            . "# nissaya\n\n" . $this->jsonBlock($nissaya) . "\n\n";
        Log::debug('PaliTranslate: revise', ['input' => $userText]);

        return $this->sendForIds('revise', $this->reviseWithNissayaPrompt, $userText, $this->idsOf($translation));
    }

    /**
     * evaluate 步骤：对照原文找出译文真实问题，按级别就地用 HTML span 标注译文。
     * 返回标注后的译文（无问题的句子原样返回），作为工作流最后一步写库。
     *
     * @param  array<int, array{id: string, content: string}>  $pali
     * @param  array<int, array{id: string, content: string}>  $translation
     * @param  array<int, array{id: string, content: string}>  $nissaya  巴利逐词缅文释义参考资料，可空
     * @return array<int, array{id: string, content: string}>
     */
    public function evaluate(array $pali, array $translation, array $nissaya = []): array
    {
        $userText = "# pali\n\n" . $this->jsonBlock($pali) . "\n\n"
            . "# translation\n\n" . $this->jsonBlock($translation) . "\n\n"
            . $this->nissayaSection($nissaya);
        Log::debug('PaliTranslate: evaluate', ['input' => $userText]);

        return $this->sendForIds('evaluate', $this->evaluatePrompt, $userText, $this->idsOf($translation));
    }

    /**
     * 从输出 channel 读取已有译文，按句子返回 ['id' => ..., 'content' => ...]
     *
     * @return array<int, array{id: string, content: string}>
     */
    protected function existingTranslation(int $book, int $para): array
    {
        $channelId = $this->workChannel['id'] ?? null;
        if (! $channelId) {
            Log::warning('PaliTranslate: 未设置输出 channel，无法读取已有译文');

            return [];
        }

        $sentences = Sentence::where('channel_uid', $channelId)
            ->where('book_id', $book)
            ->where('paragraph', $para)
            ->orderBy('word_start')
            ->get();

        $result = [];
        foreach ($sentences as $sentence) {
            $id = "{$sentence->book_id}-{$sentence->paragraph}-{$sentence->word_start}-{$sentence->word_end}";
            $result[] = ['id' => $id, 'content' => $sentence->content];
        }

        return $result;
    }

    /**
     * 调用 LLM，返回响应文本
     */
    protected function send(string $systemPrompt, string $userText): string
    {
        $startAt = time();
        $response = $this->openAIService
            ->setApiUrl($this->model['url'])
            ->setModel($this->model['model'])
            ->setApiKey($this->model['key'])
            ->setSystemPrompt($systemPrompt)
            ->setTemperature(0.0)
            ->setThinking($this->thinking)
            ->setStream($this->stream)
            ->send($userText);
        $complete = time() - $startAt;

        $content = $response['choices'][0]['message']['content'] ?? '[]';
        Log::debug("PaliTranslate: complete in {$complete}s", ['content' => $content]);

        return is_string($content) ? $content : '[]';
    }

    /**
     * 调用 LLM 并解析 jsonl；若返回结果缺失部分期望 id（大模型漏句），则整体重试，
     * 直到全部 id 补齐或达到最大尝试次数；最终仍缺失时返回最后一次（覆盖最全）的结果。
     *
     * @param  string  $step  步骤名，仅用于日志
     * @param  string[]  $expectedIds  期望覆盖的句子 id 列表
     * @return array<int, array<string, mixed>>
     */
    protected function sendForIds(string $step, string $systemPrompt, string $userText, array $expectedIds, int $maxAttempts = 2): array
    {
        $best = [];
        $bestMissing = $expectedIds;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $result = LlmResponseParser::jsonl($this->send($systemPrompt, $userText));
            $missing = $this->missingIds($expectedIds, $result);

            // 保留缺失最少的一次结果，避免重试反而更差
            if (count($missing) < count($bestMissing)) {
                $best = $result;
                $bestMissing = $missing;
            }

            if (empty($missing)) {
                return $result;
            }

            Log::warning('PaliTranslate: LLM 返回缺失 id，重试', [
                'step' => $step,
                'attempt' => $attempt,
                'missing' => $missing,
            ]);
        }

        Log::warning('PaliTranslate: 重试后仍缺失 id', [
            'step' => $step,
            'missing' => $bestMissing,
        ]);

        return $best;
    }

    /**
     * 提取记录列表中的 id（非空），保持顺序。
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return string[]
     */
    protected function idsOf(array $items): array
    {
        $ids = [];
        foreach ($items as $item) {
            if (isset($item['id']) && $item['id'] !== '') {
                $ids[] = (string) $item['id'];
            }
        }

        return $ids;
    }

    /**
     * 返回 expectedIds 中、在 LLM 结果里缺失的 id。
     *
     * @param  string[]  $expectedIds
     * @param  array<int, array<string, mixed>>  $result
     * @return string[]
     */
    protected function missingIds(array $expectedIds, array $result): array
    {
        $returned = [];
        foreach ($result as $item) {
            if (isset($item['id'])) {
                $returned[(string) $item['id']] = true;
            }
        }

        $missing = [];
        foreach ($expectedIds as $id) {
            if (! isset($returned[(string) $id])) {
                $missing[] = (string) $id;
            }
        }

        return $missing;
    }

    /**
     * 按开关返回某步骤应使用的 nissaya；未启用该步骤时返回空数组。
     *
     * @param  array<int, array{id: string, content: string}>  $nissaya
     * @return array<int, array{id: string, content: string}>
     */
    protected function nissayaFor(string $step, array $nissaya): array
    {
        return in_array($step, $this->nissayaSteps, true) ? $nissaya : [];
    }

    /**
     * 构造 nissaya 参考资料区块；无数据时返回空串（不污染提示词）。
     * 与 pali / translation 一致使用 [{id, content}] json，便于模型按 id 对应句子。
     *
     * @param  array<int, array{id: string, content: string}>  $nissaya
     */
    protected function nissayaSection(array $nissaya): string
    {
        if (empty($nissaya)) {
            return '';
        }

        return "# nissaya\n\n" . $this->jsonBlock($nissaya) . "\n\n";
    }

    /**
     * 加载本段所属 chapter 的巴利术语表。
     *
     * 术语表由 extract:pali.term 命令按 chapter 生成，保存在 _system_glossary_ channel，
     * 以 chapter 起始段落（level 1-7 节点）为 paragraph、word_start/word_end 均为 0、
     * content 为巴利术语原形的 JSON 字符串数组。这里先把当前段落向下归到所属 chapter，
     * 再读取该 chapter 的术语表。加载失败或无数据时返回空数组（不影响翻译）。
     *
     * @return string[] 巴利术语原形列表
     */
    protected function loadGlossary(int $book, int $para): array
    {
        $channelId = ChannelApi::getSysChannel('_system_glossary_');
        if (! $channelId) {
            return [];
        }

        // 术语表按 chapter 起始段落存储：向下取 ≤para 的最近 chapter 节点（level 1-7）
        $chapterStart = PaliText::where('book', $book)
            ->where('paragraph', '<=', $para)
            ->whereBetween('level', [1, 7])
            ->orderBy('paragraph', 'desc')
            ->value('paragraph');
        if ($chapterStart === null) {
            return [];
        }

        $content = Sentence::where('channel_uid', $channelId)
            ->where('book_id', $book)
            ->where('paragraph', $chapterStart)
            ->where('word_start', 0)
            ->where('word_end', 0)
            ->value('content');
        if (empty($content)) {
            return [];
        }

        $words = json_decode($content, true);
        if (! is_array($words)) {
            return [];
        }

        return array_values(array_filter($words, fn($w) => is_string($w) && $w !== ''));
    }

    /**
     * 构造 term 步骤的术语表：channel 术语表 + 同语言社区术语表，
     * 经本段所属 chapter 的系统术语表（loadGlossary）过滤——只保留系统术语表里存在的术语，
     * 再合并去重（channel 优先），每项含 word / meaning / tag 三个字段。
     *
     * @return array<int, array{word: string, meaning: string, tag: string}>
     */
    protected function buildTermGlossary(int $book, int $para): array
    {
        $channelId = $this->workChannel['id'] ?? null;
        if (! $channelId) {
            Log::warning('PaliTranslate: term 步骤未设置 channel，跳过术语注入');

            return [];
        }
        $lang = $this->workChannel['lang'] ?? 'zh-Hans';

        // 系统术语表（本段所属 chapter 命中的巴利术语原形）作为过滤白名单，算法同 loadGlossary
        $allowed = $this->loadGlossary($book, $para);
        if (empty($allowed)) {
            return [];
        }
        $allowedSet = [];
        foreach ($allowed as $word) {
            $allowedSet[$this->toNfc($word)] = true;
        }

        // channel 术语表优先，社区术语表（同语言）补充；只保留系统术语表里有的术语
        $channelTerms = $this->termService->getChannelGlossary($channelId);
        $communityTerms = $this->termService->getCommunityGlossary($lang)['items'] ?? [];

        $merged = [];
        foreach ([$channelTerms, $communityTerms] as $source) {
            foreach ($source as $term) {
                $word = $this->toNfc(trim((string) (is_array($term) ? ($term['word'] ?? '') : ($term->word ?? ''))));
                // 系统术语表里没有、word 为空、或 channel 已提供（优先）则跳过
                if ($word === '' || ! isset($allowedSet[$word]) || isset($merged[$word])) {
                    continue;
                }
                $merged[$word] = [
                    'word' => $word,
                    'meaning' => (string) (is_array($term) ? ($term['meaning'] ?? '') : ($term->meaning ?? '')),
                    'tag' => (string) (is_array($term) ? ($term['tag'] ?? '') : ($term->tag ?? '')),
                ];
            }
        }

        return array_values($merged);
    }

    /**
     * Unicode 规范化为 NFC（巴利文变音符匹配需统一 NFC/NFD）。intl 不可用时原样返回。
     */
    protected function toNfc(string $s): string
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
     * 将数组包裹为 ```json ... ``` 代码块
     *
     * @param  array<int, mixed>  $data
     */
    protected function jsonBlock(array $data): string
    {
        return "```json\n" . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n```";
    }
}
