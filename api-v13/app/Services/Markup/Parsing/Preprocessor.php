<?php

namespace App\Services\Markup\Parsing;

use App\Services\Markup\Dialect;
use Closure;

/**
 * 预扫描：在交给 CommonMark 之前，把模板类语法整段换成占位符。
 *
 * 换掉的有：
 * - `{{…}}`：按 `{{` / `}}` 深度配对，可跨行、可含空行、可嵌套
 * - `[[x]]`：术语，单行
 * - ```note 围栏（旧方言里没有 info 的 ``` 围栏也是）：整块变成脚注，紧挨着的上下文合进同一段（见 {@see self::joinFence()}）
 * - 旧方言的 `` `x` ``：脚注，单行
 *
 * 跳过不扫的：代码围栏（含 ```mermaid，交给 CommonMark）；E2 方言的行内代码 `` `…` ``。
 * 反斜杠转义 `\{{`、`\[[`、`` \` `` 原样保留，由 CommonMark 去掉反斜杠。
 *
 * 为什么不写成 CommonMark 的 inline parser：inline parser 只看得到一个段落，模板里的空行、
 * 行首的 `- ` / `#`、表格里的 `|` 都会先被块解析切开。
 */
final class Preprocessor
{
    /** `{{` 的最大嵌套深度，与 v2 的 STACK_DEEP 一致 */
    private const MAX_BRACE_DEPTH = 8;

    private const CJK = '/[\p{Han}\p{Hiragana}\p{Katakana}\p{Hangul}\x{3000}-\x{303F}\x{FF00}-\x{FFEF}]/u';

    /** 下一行以这些开头时是新的块，不并进脚注所在段落 */
    private const BLOCK_START = '/^ {0,3}(#{1,6}(\s|$)|>|[-*+]\s|\d{1,9}[.)]\s|`{3,}|~{3,}|\||<|([-*_=])[ \t]*(\3[ \t]*){2,}$)/';

    private string $src = '';

    private int $len = 0;

    private string $out = '';

    /**
     * @param  Closure(string $kind, string $raw, string $inner, string $info): int  $register
     *                                                                                          登记一次调用并返回下标。kind：template / wikilink / note-backtick / note-fence
     */
    public function run(string $source, Dialect $dialect, Closure $register): string
    {
        $this->src = $source;
        $this->len = strlen($source);
        $this->out = '';
        $i = 0;
        $lineStart = true;

        while ($i < $this->len) {
            if ($lineStart) {
                $lineStart = false;
                $fence = $this->fenceAt($i);
                if ($fence !== null) {
                    [$marker, $info] = $fence;
                    [$contentStart, $contentEnd, $after] = $this->closingFence($i, $marker);
                    if ($this->isNoteFence($marker, $info, $dialect)) {
                        $inner = substr($this->src, $contentStart, max(0, $contentEnd - $contentStart));
                        $raw = substr($this->src, $i, $after - $i);
                        $index = $register('note-fence', rtrim($raw, "\n"), rtrim($inner, "\n"), $info);
                        [$i, $lineStart] = $this->joinFence($index, $after);
                    } else {
                        $this->out .= substr($this->src, $i, $after - $i);
                        $i = $after;
                        $lineStart = true;
                    }

                    continue;
                }
            }

            $char = $this->src[$i];

            if ($char === '\\' && $i + 1 < $this->len && in_array($this->src[$i + 1], ['{', '[', '`'], true)) {
                $this->out .= substr($this->src, $i, 2);
                $i += 2;

                continue;
            }

            if ($char === '{' && substr($this->src, $i, 2) === '{{') {
                $end = $this->matchBraces($i);
                if ($end !== null) {
                    $raw = substr($this->src, $i, $end + 2 - $i);
                    $this->out .= Placeholder::make($register('template', $raw, substr($raw, 2, -2), ''));
                    $i = $end + 2;
                } else {
                    $this->out .= '{{';
                    $i += 2;
                }

                continue;
            }

            if ($char === '[' && substr($this->src, $i, 2) === '[[') {
                $close = $this->sameLine($i + 2, ']]');
                if ($close !== null && $close > $i + 2) {
                    $raw = substr($this->src, $i, $close + 2 - $i);
                    $this->out .= Placeholder::make($register('wikilink', $raw, substr($raw, 2, -2), ''));
                    $i = $close + 2;

                    continue;
                }
            }

            if ($char === '`') {
                $i = $dialect === Dialect::MarkdownE2
                    ? $this->skipCodeSpan($i)
                    : $this->legacyBacktick($i, $register);

                continue;
            }

            $this->out .= $char;
            $i++;
            if ($char === "\n") {
                $lineStart = true;
            }
        }

        return $this->out;
    }

    /**
     * 行首是否是围栏开头。
     *
     * @return array{0: string, 1: string}|null [围栏符号串, info string]
     */
    private function fenceAt(int $i): ?array
    {
        $line = $this->lineAt($i);
        if (preg_match('/^ {0,3}(`{3,}|~{3,})(.*)$/', rtrim($line, "\r"), $m) !== 1) {
            return null;
        }
        // 反引号围栏的 info 里不能再有反引号（CommonMark 规则），否则这行只是行内代码
        if ($m[1][0] === '`' && str_contains($m[2], '`')) {
            return null;
        }

        return [$m[1], trim($m[2])];
    }

    /**
     * 找围栏的结束行。没有结束行就到文末（与 CommonMark 一致；v2 旧方言也是补到文末）。
     *
     * @return array{0: int, 1: int, 2: int} [内容起点, 内容终点（不含）, 结束行之后的位置]
     */
    private function closingFence(int $i, string $marker): array
    {
        $contentStart = $this->nextLine($i);
        $pos = $contentStart;
        $closing = '/^ {0,3}'.preg_quote($marker[0], '/').'{'.strlen($marker).',}[ \t]*\r?$/';
        while ($pos < $this->len) {
            $line = $this->lineAt($pos);
            if (preg_match($closing, $line) === 1) {
                return [$contentStart, $pos, $this->nextLine($pos)];
            }
            $pos = $this->nextLine($pos);
        }

        return [$contentStart, $this->len, $this->len];
    }

    private function isNoteFence(string $marker, string $info, Dialect $dialect): bool
    {
        $word = strtolower(explode(' ', $info, 2)[0]);
        if ($word === 'note') {
            return true;
        }

        // 旧方言：单独一行的 ``` 就是多行脚注
        return $dialect === Dialect::Markdown && $info === '' && $marker === '```';
    }

    /**
     * 脚注围栏的落点。
     *
     * 上一行有字：去掉换行，占位符直接贴在上一行末尾（角标贴着前面的字）。
     * 下一行有字且不是新块：把下一行接到占位符后面；两侧都是 CJK 时不加空格，否则加一个空格。
     * 这样「前半句 / ```note … ``` / 后半句」三段合成一个段落，脚注在句中（v2 旧方言就是这个效果）。
     * 两侧都是空行时占位符独占一段，由 IR 构建时并到上一段末尾。
     *
     * @return array{0: int, 1: bool} [继续扫描的位置, 是否在行首]
     */
    private function joinFence(int $index, int $after): array
    {
        $left = null;
        if (str_ends_with($this->out, "\n") && ! $this->previousLineBlank()) {
            $this->out = rtrim($this->out, "\r\n");
            $left = mb_substr($this->out, -1);
        }
        $this->out .= Placeholder::make($index);

        if ($after >= $this->len) {
            return [$after, false];
        }
        $next = $this->lineAt($after);
        if (trim($next) === '' || preg_match(self::BLOCK_START, $next) === 1) {
            $this->out .= "\n";

            return [$after, true];
        }

        $indent = strspn($next, " \t");
        $right = mb_substr(substr($next, $indent), 0, 1);
        $bothCjk = ($left === null || $this->isCjk($left)) && $this->isCjk($right);
        $this->out .= $bothCjk ? '' : ' ';

        return [$after + $indent, false];
    }

    private function previousLineBlank(): bool
    {
        $body = substr($this->out, 0, -1);
        $start = strrpos($body, "\n");
        $line = $start === false ? $body : substr($body, $start + 1);

        return trim($line) === '';
    }

    private function isCjk(string $char): bool
    {
        return $char !== '' && preg_match(self::CJK, $char) === 1;
    }

    /**
     * 从 `{{` 起按深度配对，返回配对的 `}}` 的位置；不配对或超深返回 null。
     */
    private function matchBraces(int $i): ?int
    {
        $depth = 0;
        $j = $i;
        while ($j < $this->len - 1) {
            $pair = substr($this->src, $j, 2);
            if ($pair === '{{') {
                $depth++;
                if ($depth > self::MAX_BRACE_DEPTH) {
                    return null;
                }
                $j += 2;

                continue;
            }
            if ($pair === '}}') {
                $depth--;
                if ($depth === 0) {
                    return $j;
                }
                $j += 2;

                continue;
            }
            $j++;
        }

        return null;
    }

    /**
     * E2：行内代码原样跳过（里面的 `{{`、`[[` 不是模板）。反引号串长度 n，找下一个恰好 n 个的串。
     */
    private function skipCodeSpan(int $i): int
    {
        $n = strspn($this->src, '`', $i);
        $pos = $i + $n;
        while (($found = strpos($this->src, str_repeat('`', $n), $pos)) !== false) {
            $run = strspn($this->src, '`', $found);
            if ($run === $n) {
                $end = $found + $n;
                $this->out .= substr($this->src, $i, $end - $i);

                return $end;
            }
            $pos = $found + $run;
        }
        $this->out .= substr($this->src, $i, $n);

        return $i + $n;
    }

    /**
     * 旧方言：`` `x` `` 是脚注。配对口径同 v2 的正则 /`(.+?)`/：正文至少一个字符，且可以以反引号开头
     * （``` ``x`` ``` 的正文是 `` `x ``，剩下一个反引号是普通字符）。
     *
     * 与 v2 不同的两点（都按作者本意，v2 在这些写法上配错了对）：
     * - 可以跨行（不跨空行），反引号后可以直接换行。库里有大量「词`多行注释`」的写法，v2 的正则不跨行，
     *   配错了对，剩下的反引号又被 CommonMark 配成跨行的代码 span
     * - ``` ``x`` ```（两个以上成对）：整对是边界，正文是 x（v2 的正文是「`x」）
     * 单行内写对的单反引号，配对结果与 v2 相同。
     *
     * @param  Closure(string, string, string, string): int  $register
     */
    private function legacyBacktick(int $i, Closure $register): int
    {
        // 两个以上的反引号成对（``x``）：作者把整对当作脚注边界，正文是中间的部分
        $n = strspn($this->src, '`', $i);
        if ($n >= 2 && ($pair = $this->matchingRun($i + $n, $n)) !== null) {
            $raw = substr($this->src, $i, $pair + $n - $i);
            $this->out .= Placeholder::make($register('note-backtick', $raw, substr($raw, $n, -$n), ''));

            return $pair + $n;
        }

        $close = $i + 2 <= $this->len ? strpos($this->src, '`', $i + 2) : false;
        $spansParagraph = $close !== false
            && preg_match('/\n[ \t]*\n/', substr($this->src, $i + 1, $close - $i - 1)) === 1;
        if ($close === false || $spansParagraph) {
            // 配不上对的反引号是普通字符：旧方言里反引号从来不是代码，转义掉免得 CommonMark 配成代码 span
            $this->out .= '\\`';

            return $i + 1;
        }
        $raw = substr($this->src, $i, $close + 1 - $i);
        $this->out .= Placeholder::make($register('note-backtick', $raw, substr($raw, 1, -1), ''));

        return $close + 1;
    }

    /**
     * 从 $from 起找恰好 $n 个反引号的串（不跨空行），返回它的起点。
     */
    private function matchingRun(int $from, int $n): ?int
    {
        $pos = $from;
        while (($found = strpos($this->src, str_repeat('`', $n), $pos)) !== false) {
            if (preg_match('/\n[ \t]*\n/', substr($this->src, $from, $found - $from)) === 1) {
                return null;
            }
            $run = strspn($this->src, '`', $found);
            if ($run === $n) {
                return $found;
            }
            $pos = $found + $run;
        }

        return null;
    }

    /**
     * 在同一行内找 $needle，找不到返回 null。
     */
    private function sameLine(int $from, string $needle): ?int
    {
        $found = strpos($this->src, $needle, $from);
        if ($found === false) {
            return null;
        }
        $newline = strpos($this->src, "\n", $from);

        return ($newline === false || $found < $newline) ? $found : null;
    }

    private function lineAt(int $i): string
    {
        $end = strpos($this->src, "\n", $i);

        return substr($this->src, $i, ($end === false ? $this->len : $end) - $i);
    }

    private function nextLine(int $i): int
    {
        $end = strpos($this->src, "\n", $i);

        return $end === false ? $this->len : $end + 1;
    }
}
