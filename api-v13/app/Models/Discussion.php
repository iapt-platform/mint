<?php

namespace App\Models;

use App\Services\PaliContentService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 表 `discussions`。
 *
 * 这一张表装着几种东西：评论线程（discussion / qa / help / issue）、阅读页批注
 * （note / commentary，会被注入阅读页），以及 v2 TaskStatusController 写进来的任务
 * 状态日志（res_type=task）。v3 的 `/v3/discussions` 管前两种（{@see self::COMMENT_TYPES}）。
 *
 * 2026-10-15 起软删除：删除话题时这一行留作「墓碑」，回复不受影响。
 */
class Discussion extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $primaryKey = 'id';

    /**
     * 主键是 uuid，由库的默认值生成（所以 incrementing 仍为 true：靠 insert … returning id
     * 拿回主键）。keyType 必须声明为 string：默认的 int 会让 whereIn 走 whereIntegerInRaw，
     * 把 uuid 转成整数（loadCount / 预加载时出现 `id in (2)`）。
     */
    protected $keyType = 'string';

    protected $casts = [
        'id' => 'string',
        'pos_start' => 'integer',
        'pos_end' => 'integer',
        'score' => 'integer',
    ];

    // 批量填充
    protected $fillable = [
        'res_id',
        'res_type',
        'score',
        'type',
        'tpl_id',
        'title',
        'content',
        'content_type',
        'parent',
        'editor_uid',
        'pos_start',
        'pos_end',
        'quote_exact',
        'quote_prefix',
        'quote_suffix',
    ];

    /**
     * 义注 / 复注对应（巴利注释书，不是普通评论）：content 是注释书的句子模板
     * `{{book-para-start-end}}`（可多句并列），渲染成那几句的译文并带 <cite> 出处。
     */
    public const TYPE_COMMENTARY = 'commentary';

    /** commentary 的 content 格式：一个或多个句子模板（与 PaliContentService 注入时的判定一致） */
    public const COMMENTARY_PATTERN = '/^(?:\{\{\d+-\d+-\d+-\d+\}\}\s*)+$/';

    /** 普通边注：content 就是注解正文，没有出处 */
    public const TYPE_NOTE = 'note';

    /** 批注类型：作为边注注入阅读页，没有回复以外的线程属性（开关、severity） */
    public const INJECTED_TYPES = [self::TYPE_COMMENTARY, self::TYPE_NOTE];

    /**
     * 外挂术语：不改译文原文，给锚点处的一段译文挂上术语（title 是术语词头），阅读页把那段
     * 原文渲染成 `[[title#原文]]`。带锚点、只能挂在句子上。与术语表（dhamma terms）是两回事。
     */
    public const TYPE_GLOSSARY = 'glossary';

    /**
     * 译文评估：用某种方法给一句译文打分。score 是归一到 0–100 的整数分数，severity 存评估方法
     * （{@see self::ASSESSMENT_METHOD_PATTERN}），content 写理由，title 可省。
     * 只能挂在句子上、只有该句的管理者能建（AI 凭授权代写）；一句只留一条，重新评估覆盖旧的。
     * 没有开关状态、不能与其它类型互转、不带锚点；注入阅读页，在句尾显示按分数着色的对勾。
     */
    public const TYPE_ASSESSMENT = 'assessment';

    /** 评估方法（存在 severity 列，varchar(16)）：字母数字开头，可含 . _ - */
    public const ASSESSMENT_METHOD_PATTERN = '/^[A-Za-z0-9][A-Za-z0-9._-]{0,15}$/';

    /** 没有开关状态的类型：批注 + 评估 */
    public const STATELESS_TYPES = [self::TYPE_COMMENTARY, self::TYPE_NOTE, self::TYPE_ASSESSMENT];

    /** 评论线程：话题 + 一层回复，有开关状态 */
    public const THREAD_TYPES = ['discussion', 'qa', 'help', 'issue', self::TYPE_GLOSSARY];

    /** 可以带锚点（pos_* / quote_*）、定位到句子文本某一段的类型：批注 + issue / glossary */
    public const ANCHORED_TYPES = [self::TYPE_COMMENTARY, self::TYPE_NOTE, 'issue', self::TYPE_GLOSSARY];

    /**
     * 会被注入阅读页的全部类型（{@see PaliContentService::injectAnnotationNotes()}）：
     * 批注插边注，glossary 换成术语，issue 高亮，assessment 句尾对勾。issue / glossary 只注入未关闭的。
     */
    public const READING_TYPES = [self::TYPE_COMMENTARY, self::TYPE_NOTE, 'issue', self::TYPE_GLOSSARY, self::TYPE_ASSESSMENT];

    /** 只能挂在句子上的类型：批注 + glossary + assessment */
    public const SENTENCE_ONLY_TYPES = [self::TYPE_COMMENTARY, self::TYPE_NOTE, self::TYPE_GLOSSARY, self::TYPE_ASSESSMENT];

    /**
     * `/v3/discussions` 管的全部类型：评论线程（{@see self::THREAD_TYPES}）+ 批注
     * （{@see self::INJECTED_TYPES}）+ 评估（{@see self::TYPE_ASSESSMENT}）。
     * 批注与评估都注入阅读页，可以回复，没有开关。
     */
    public const COMMENT_TYPES = ['discussion', 'qa', 'help', 'issue', 'glossary', 'note', 'commentary', 'assessment'];

    /**
     * 各类型允许的 severity 取值；不在表里的类型不允许设 severity。
     * assessment 例外：severity 存评估方法，按 {@see self::ASSESSMENT_METHOD_PATTERN} 校验，必填。
     */
    public const SEVERITIES = [
        'issue' => ['error', 'warning', 'suggestion'],
        'help' => ['urgent', 'normal'],
    ];

    /** 话题状态：active 未关闭，close 已关闭（v4 在读这两个值，不能改） */
    public const STATUSES = ['active', 'close'];

    /**
     * 挂靠目标的类型（res_type）。v3 对外叫 target_type。
     * chapter / channel / feedback 在两个前端里都没有调用方，不收。
     */
    public const TARGET_TYPES = ['sentence', 'wbw', 'article', 'term', 'task'];

    /**
     * 当前查看者能做的动作，由 `App\Services\V3\DiscussionService` 算好后挂上，
     * 供 Resource 读取（Resource 不查库）。声明成属性而不是 attribute，不会被存进表。
     *
     * @var array{update: bool, delete: bool, close: bool, reply: bool}|null
     */
    public ?array $abilities = null;

    /**
     * 正文渲染结果（只有挂在句子上的才有，需要句子所在的 channel 才能渲染模板），
     * 同样由 Service 算好后挂上。
     *
     * @var array{html: string, summary: string}|null
     */
    public ?array $rendered = null;

    /**
     * 编辑者（人类用户）。editor_uid 对 user_infos.userid。
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserInfo::class, 'editor_uid', 'userid');
    }

    /**
     * 编辑者（AI 助手）。与 {@see self::user()} 共用 uuid 空间，二者择一。
     */
    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class, 'editor_uid', 'uid');
    }

    /**
     * 协作者：人类授权代写的 AI 模型（co_editor_uid）。此时 editor 是授权人。
     * 存量数据里 AI 写的行没有它，editor_uid 直接是模型，见 {@see self::aiModel()}。
     */
    public function coEditor(): BelongsTo
    {
        return $this->belongsTo(AiModel::class, 'co_editor_uid', 'uid');
    }

    /**
     * 话题下的回复（只有一层）。软删的回复自动排除。
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Discussion::class, 'parent');
    }

    /**
     * 这几类会被注入到所挂句子的阅读页里（{@see self::READING_TYPES}），阅读页按
     * (book, para, channel) 缓存，所以增改删它们（含关闭 / 重开）都要清掉那一段的缓存。
     * 改之前是、改之后不是（或反过来）的也要清。
     */
    protected static function booted(): void
    {
        $forget = function (Discussion $discussion) {
            $wasInjected = in_array($discussion->getOriginal('type'), self::READING_TYPES, true);
            if (! in_array($discussion->type, self::READING_TYPES, true) && ! $wasInjected) {
                return;
            }
            if ($discussion->res_type !== 'sentence' || empty($discussion->res_id)) {
                return;
            }
            $sentence = Sentence::where('uid', $discussion->res_id)
                ->first(['book_id', 'paragraph', 'channel_uid']);
            if (! $sentence) {
                return;
            }
            PaliContentService::forgetParagraph(
                (int) $sentence->book_id,
                (int) $sentence->paragraph,
                (string) $sentence->channel_uid
            );
        };
        static::saved($forget);
        static::deleted($forget);
    }

    // 设置默认值
    protected $attributes = [
        'content_type' => 'markdown',
        'type' => 'discussion',
    ];
}
