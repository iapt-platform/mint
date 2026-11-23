<?php

namespace App\Services\Markup\Templates;

/**
 * 模板名 → 模板类。别名直接登记成另一个名字。
 *
 * 新增模板：写一个 {@see Template} 子类，在这里加一行。
 */
final class TemplateRegistry
{
    /** @var array<string, class-string<Template>> */
    private const TEMPLATES = [
        'term' => TermTemplate::class,
        'note' => NoteTemplate::class,
        'n' => NoteTemplate::class,
        'sent' => SentTemplate::class,
        'quote' => QuoteTemplate::class,
        'ql' => QuoteLinkTemplate::class,
        'exercise' => ExerciseTemplate::class,
        'article' => ArticleTemplate::class,
        'chapter' => ChapterTemplate::class,
        'nissaya' => NissayaTemplate::class,
        'mermaid' => MermaidTemplate::class,
        'qa' => QaTemplate::class,
        'v' => VideoTemplate::class,
        'g' => GrammarTemplate::class,
        'ref' => ReferenceTemplate::class,
        'dict-pref' => DictPrefTemplate::class,
        'ai' => AiTemplate::class,
        'para' => ParaTemplate::class,
        'category' => CategoryTemplate::class,
        'quality' => QualityTemplate::class,
        'toggle' => ToggleTemplate::class,
        'toc' => TocTemplate::class,
    ];

    /**
     * 内部模板：只由程序生成（批注注入），不在源码语法里——正文里写 `{{anno-issue|…}}` 得到的是 unknown。
     *
     * @var array<string, class-string<Template>>
     */
    private const INTERNAL = [
        'anno-issue' => Internal\AnnoIssueTemplate::class,
        'anno-issue-mark' => Internal\AnnoIssueMarkTemplate::class,
        'anno-assessment' => Internal\AnnoAssessmentTemplate::class,
    ];

    /** `{{Tpl:名字|…}}`：以文章为模板（标题就是模板名） */
    public const TPL_PREFIX = 'Tpl:';

    /** @var array<class-string<Template>, Template> */
    private array $instances = [];

    public function get(string $name): Template
    {
        $class = self::TEMPLATES[$name] ?? null;
        if ($class === null) {
            $class = str_starts_with($name, self::TPL_PREFIX) ? TplTemplate::class : UnknownTemplate::class;
        }

        return $this->instances[$class] ??= app($class);
    }

    /**
     * 内部模板（{@see self::INTERNAL}）；不是内部模板名时退回 {@see self::get()}。
     */
    public function internal(string $name): Template
    {
        $class = self::INTERNAL[$name] ?? null;

        return $class === null ? $this->get($name) : ($this->instances[$class] ??= app($class));
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        return array_keys(self::TEMPLATES);
    }
}
