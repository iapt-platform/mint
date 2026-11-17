<?php

namespace App\Services\Markup\Html;

/**
 * 正文里原始 HTML 的白名单。
 *
 * v2 把原始 HTML 原样交给前端（XmlToReact 逐个 createElement），任何标签、任何属性都能进 DOM。
 * 这里收紧：
 * - 认识的标签保留；脚本、iframe、svg、表单等连同内容一起丢掉
 * - 不认识的标签（库里有 `<nt>`、`<r>`、`<commentary>` 等自定义标签）改成 `span`，原名放 `data-tag`
 * - 属性只留白名单，`style` 与 `on*` 一律去掉；链接与图片地址只允许 http(s)、mailto、站内相对路径
 */
final class HtmlPolicy
{
    /** 认识的标签：保留原名 */
    private const ALLOWED = [
        'a', 'abbr', 'b', 'bdi', 'bdo', 'blockquote', 'br', 'caption', 'center', 'cite', 'code', 'col', 'colgroup',
        'dd', 'del', 'details', 'dfn', 'div', 'dl', 'dt', 'em', 'figcaption', 'figure', 'font', 'h1', 'h2', 'h3',
        'h4', 'h5', 'h6', 'hr', 'i', 'img', 'ins', 'kbd', 'li', 'mark', 'ol', 'p', 'pre', 'q', 'rp', 'rt', 'ruby',
        's', 'samp', 'small', 'span', 'strong', 'sub', 'summary', 'sup', 'table', 'tbody', 'td', 'tfoot', 'th',
        'thead', 'tr', 'u', 'ul', 'var', 'wbr', 'video', 'audio', 'source',
    ];

    /** 已废弃的展示类标签：换成等价的容器 */
    private const RENAMED = ['center' => 'div', 'font' => 'span'];

    /** 连同内容一起丢掉 */
    private const DROPPED = [
        'script', 'style', 'iframe', 'frame', 'frameset', 'object', 'embed', 'applet', 'svg', 'math', 'form',
        'input', 'button', 'select', 'textarea', 'option', 'template', 'noscript', 'link', 'meta', 'base',
        'title', 'head', 'html', 'body', 'canvas', 'dialog', 'portal',
    ];

    /** 自闭合标签 */
    public const VOID = ['br', 'hr', 'img', 'wbr', 'col', 'source'];

    /** role / tabindex / aria-*：阅读页注入的可点击图标（评估对勾、issue 叹号）要靠它们支持键盘与读屏 */
    private const GLOBAL_ATTRS = ['class', 'title', 'lang', 'dir', 'role', 'tabindex'];

    private const TAG_ATTRS = [
        'a' => ['href'],
        'img' => ['src', 'alt', 'width', 'height'],
        'video' => ['src', 'controls', 'width', 'height', 'poster'],
        'audio' => ['src', 'controls'],
        'source' => ['src', 'type'],
        'td' => ['colspan', 'rowspan', 'align'],
        'th' => ['colspan', 'rowspan', 'align'],
        'ol' => ['start'],
        'details' => ['open'],
    ];

    private const URL_ATTRS = ['href', 'src', 'poster'];

    /**
     * 标签的去向。
     *
     * @return string|false|null string：保留并用这个名字；null：改成 span（原名放 data-tag）；false：连同内容丢掉
     */
    public static function tag(string $tag): string|false|null
    {
        $tag = strtolower($tag);
        if (in_array($tag, self::DROPPED, true)) {
            return false;
        }
        if (in_array($tag, self::ALLOWED, true)) {
            return self::RENAMED[$tag] ?? $tag;
        }

        return null;
    }

    /**
     * 过滤属性。
     *
     * @param  array<string, string>  $attrs  原始属性（键已小写）
     * @return array<string, string>
     */
    public static function attrs(string $tag, array $attrs): array
    {
        $allowed = array_merge(self::GLOBAL_ATTRS, self::TAG_ATTRS[$tag] ?? []);
        $clean = [];
        foreach ($attrs as $name => $value) {
            $name = strtolower($name);
            $isData = preg_match('/^(data|aria)-[a-z0-9][a-z0-9-]*$/', $name) === 1;
            if (! $isData && ! in_array($name, $allowed, true)) {
                continue;
            }
            if (in_array($name, self::URL_ATTRS, true)) {
                $value = self::url($value);
                if ($value === null) {
                    continue;
                }
            }
            $clean[$name] = $value;
        }

        return $clean;
    }

    /**
     * 地址白名单：http(s)、mailto、站内绝对 / 相对路径、锚点。其余（javascript:、data: 等）返回 null。
     */
    public static function url(string $url): ?string
    {
        $url = trim($url);
        // 去掉控制字符与空白后再判断协议，防 `java\tscript:` 之类绕过
        $probe = strtolower((string) preg_replace('/[\x00-\x20]+/', '', $url));
        if (preg_match('/^[a-z][a-z0-9+.-]*:/', $probe, $m) !== 1) {
            return $url;
        }

        return in_array($m[0], ['http:', 'https:', 'mailto:'], true) ? $url : null;
    }
}
