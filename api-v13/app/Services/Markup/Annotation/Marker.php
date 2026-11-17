<?php

namespace App\Services\Markup\Annotation;

/**
 * 一个批注标记：{@see AnnotationSet} 在源码里留下的占位符对应的结构化内容。
 *
 * - point：一个点，解析成一个模板调用（脚注、句尾图标）
 * - open / close：一对区间边界（同一个 pair），解析后包住中间的内容（issue 高亮）
 */
final readonly class Marker
{
    public const OPEN = 'open';

    public const CLOSE = 'close';

    public const POINT = 'point';

    /**
     * @param  string  $template  模板名：point 与 open 用（close 跟随 open）
     * @param  array<string, string>  $params  命名参数
     * @param  string|null  $body  note 的正文（markdown），解析成 text slot
     * @param  string  $bodyContentType  note 正文的方言（批注是 discussions 的一行，方言跟着它，不跟句子）
     */
    public function __construct(
        public string $kind,
        public int $pair = 0,
        public string $template = '',
        public array $params = [],
        public ?string $body = null,
        public string $bodyContentType = 'markdown',
    ) {}
}
