<?php

namespace App\Services\V3;

use App\Models\Channel;
use App\Services\TipitakaCatalogService;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * 生成三藏下载的文件（由 TipitakaExportService::run() 在 worker 里调用）。
 *
 * 正文一律经 TipitakaReadingService::page() 取，和 `/v3/tipitaka-reading` 是同一份渲染：
 * html / offline 用 display（段落 HTML），markdown / epub / docx 用逐句的 markdown
 * （句末附脚注正文，这里整章重新编号）。epub、docx 由 pandoc 从 markdown 转换，
 * 这样脚注能变成电子书 / Word 里真正的脚注。
 *
 * 中间文件写在 storage/app/tmp/tipitaka-export/ 下的临时目录，每部作品写完就落盘，
 * zip 最后用 addFile 收集——整个 channel 的正文不会同时留在内存里。
 */
class TipitakaExportBuilder
{
    public function __construct(
        private readonly TipitakaReadingService $reading,
        private readonly TipitakaCatalogService $catalog,
        private readonly PaliTextService $paliTexts,
    ) {}

    /**
     * 生成文件，返回文件字节。
     *
     * @param  array<string, mixed>  $status  导出状态（TipitakaExportService::spec() 的字段）
     * @param  array{kind: string, works: list<array<string, mixed>>, title: string}  $scope
     * @param  callable(float, string): void  $progress  进度 0–1 与说明
     */
    public function build(array $status, array $scope, callable $progress): string
    {
        $dir = storage_path('app/tmp/tipitaka-export/'.$status['id'].'-'.Str::random(6));
        File::ensureDirectoryExists($dir);
        $context = $this->context($status['channel']);
        // 文件里的界面文字（目录、生成日期…）跟着译文的语言走，而不是 worker 进程的默认语言
        $previousLocale = App::getLocale();
        App::setLocale(self::locale($context['lang']));

        try {
            $format = $status['format'];
            $single = $scope['kind'] === 'work';
            $works = $scope['works'];
            $count = count($works);
            // 打包时 zip 里套一层与文件名同名的目录，解压后不会散落一地
            $root = $single ? '' : pathinfo(pathinfo($status['filename'], PATHINFO_FILENAME), PATHINFO_FILENAME).'/';
            $entries = [];

            foreach ($works as $i => $work) {
                $tick = function (float $fraction) use ($progress, $i, $count, $work): void {
                    $progress(0.9 * ($i + $fraction) / $count, ($i + 1)."/{$count} {$work['local_title']}");
                };
                $path = $single ? '' : $this->categoryFolder($work, $context['lang']).'/';
                $name = self::safeName("{$work['sn']}-{$work['local_title']}");

                match ($format) {
                    'offline' => $entries[''] = $this->offline($dir, $status, $work, $tick),
                    'html' => $entries[$single ? self::safeName($work['local_title']).'.html' : "{$root}works/{$name}.html"]
                        = $this->writeFile($dir, "w{$i}.html", $this->htmlWork($context, $work, $this->paragraphs($context['uid'], $work, 'html', $tick), $single ? null : '../index.html')),
                    'markdown' => $entries += $this->markdownWork($dir, $i, $context, $work, $this->paragraphs($context['uid'], $work, 'markdown', $tick), $single ? self::safeName($work['local_title']).'/' : "{$root}{$path}{$name}/"),
                    'epub', 'docx' => $entries[$single ? '' : "{$root}{$path}{$name}.{$format}"]
                        = $this->pandoc($dir, $i, $format, $context, $work, $this->paragraphs($context['uid'], $work, 'markdown', $tick)),
                };
            }

            $progress(0.92, 'packing');
            if ($format === 'offline' || ($single && in_array($format, ['epub', 'docx'], true))) {
                return (string) file_get_contents($entries['']);
            }
            if (! $single) {
                $entries += match ($format) {
                    'html' => ["{$root}index.html" => $this->writeFile($dir, 'index.html', $this->htmlIndex($context, $scope))],
                    default => ["{$root}README.md" => $this->writeFile($dir, 'index.md', $this->markdownIndex($context, $scope, $format))],
                };
            }

            return $this->zip($dir, $entries);
        } finally {
            App::setLocale($previousLocale);
            File::deleteDirectory($dir);
        }
    }

    /**
     * channel 的语言（如 zh、zh-Hant、en-US）→ 受支持的界面语言，匹配不上用 en。
     */
    public static function locale(string $lang): string
    {
        $tag = strtolower($lang);
        if (str_starts_with($tag, 'zh')) {
            return preg_match('/hant|tw|hk|mo/', $tag) ? 'zh-Hant' : 'zh-Hans';
        }
        $primary = explode('-', $tag)[0];

        return array_key_exists($primary, (array) config('mint.languages')) ? $primary : 'en';
    }

    /**
     * 文件 / 目录名：去掉文件系统不允许的字符，限长。
     */
    public static function safeName(string $name): string
    {
        $name = trim((string) preg_replace('/[\\\\\/:*?"<>|\x00-\x1f]+/u', ' ', $name));
        $name = (string) preg_replace('/\s+/u', ' ', $name);

        return mb_substr($name === '' ? 'untitled' : $name, 0, 80);
    }

    /**
     * 一部作品的全部段落（按经文顺序），附上标题级别。没有译文的段落不在其中。
     *
     * @param  array<string, mixed>  $work
     * @param  callable(float): void  $tick
     * @return list<array{para: int, level: int, display?: string, sentences?: list<array{sid: string, html: string}>}>
     */
    private function paragraphs(string $channel, array $work, string $format, callable $tick): array
    {
        $levels = $this->headingLevels($work);
        $items = [];
        $after = null;
        do {
            $page = $this->reading->page($channel, array_filter([
                'book' => $work['book'],
                'para' => $work['para'],
                'to' => $work['end'],
                'unit' => 'para',
                'page_size' => TipitakaReadingService::MAX_PARAGRAPHS_PER_PAGE,
                'format' => $format,
                'include' => $format === 'html' ? 'display' : 'sentences',
                'after' => $after,
            ]));
            foreach ($page['items'] as $item) {
                unset($item['book']);
                $items[] = $item + ['level' => $levels[$item['para']] ?? 0];
            }
            $total = (int) ($page['meta']['total'] ?? 0);
            $tick($total > 0 ? 1 - ((int) ($page['meta']['remaining'] ?? 0)) / $total : 1.0);
            $after = $page['meta']['next_cursor'];
        } while ($after !== null);

        return $items;
    }

    /**
     * 作品区间里的标题段落：段落号 => 级别（1–7）。
     *
     * @param  array<string, mixed>  $work
     * @return array<int, int>
     */
    private function headingLevels(array $work): array
    {
        return array_map(fn (array $heading) => $heading['level'], $this->headingsIn($work));
    }

    /**
     * 作品区间里的标题：段落号 => [级别, 巴利标题]。译文没有译出标题时拿巴利标题顶上。
     *
     * @param  array<string, mixed>  $work
     * @return array<int, array{int, string}>
     */
    private function headings(array $work): array
    {
        return array_map(fn (array $heading) => [$heading['level'], $heading['toc']], $this->headingsIn($work));
    }

    /**
     * 作品区间里的标题段（缓存的经文静态数据），按段落号升序。
     *
     * @param  array<string, mixed>  $work
     * @return array<int, array{level: int, chapter_len: int, chapter_strlen: int, parent: int, toc: string}>
     */
    private function headingsIn(array $work): array
    {
        return array_filter(
            $this->paliTexts->headings((int) $work['book']),
            fn (int $para) => $para >= $work['para'] && $para <= $work['end'],
            ARRAY_FILTER_USE_KEY,
        );
    }

    /**
     * @return array{uid: string, name: string, lang: string, generated_at: string}
     */
    private function context(string $channel): array
    {
        $row = Channel::where('uid', $channel)->first(['uid', 'name', 'lang']);

        return [
            'uid' => $channel,
            'name' => (string) $row?->name,
            'lang' => (string) ($row?->lang ?: 'en'),
            'generated_at' => now()->toDateString(),
        ];
    }

    /**
     * 打包时作品所在的目录：分类路径（本地化名字），没有分类的放「其他」。
     *
     * @param  array<string, mixed>  $work
     */
    private function categoryFolder(array $work, string $lang): string
    {
        return implode('/', array_map(self::safeName(...), $this->categoryNames($work, $lang)));
    }

    /**
     * @param  array<string, mixed>  $work
     * @return list<string>
     */
    private function categoryNames(array $work, string $lang): array
    {
        if ($work['category_id'] === null) {
            return [__('library.export_uncategorized')];
        }
        $path = $this->catalog->categoryPath($work['category_id']);
        // 分类名查术语表要用界面语言（zh-Hans），channel 的语言常写成 zh
        $names = $this->catalog->localNames($this->catalog->categories(), self::locale($lang));

        return array_map(fn ($c) => $names[$c['name']] ?? $c['name'], $path);
    }

    // ── html ────────────────────────────────────────────────────────────

    /**
     * 一部作品的单个 HTML 文件（样式内联，离线可用）。
     *
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $work
     * @param  list<array{para: int, level: int, display?: string}>  $paragraphs
     */
    private function htmlWork(array $context, array $work, array $paragraphs, ?string $indexHref): string
    {
        $baseLevel = $paragraphs === [] ? 1 : min(array_map(fn ($p) => $p['level'] ?: 99, $paragraphs));
        $toc = [];
        $body = '';
        foreach ($paragraphs as $p) {
            // pali_texts 的级别到 7，html 只有 h1–h6
            $body .= preg_replace('#<(/?)h7>#', '<$1h6>', $p['display']);
            if ($p['level'] > 0 && $p['level'] <= $baseLevel + 2) {
                $toc[] = ['id' => "para-{$p['para']}", 'depth' => $p['level'] - $baseLevel, 'title' => $this->plainText($p['display'])];
            }
        }

        return view('exports.tipitaka.work', [
            'context' => $context,
            'work' => $work,
            'toc' => $toc,
            'body' => $body,
            'indexHref' => $indexHref,
            'sourceUrl' => route('library.tipitaka.channels.work', ['channel' => $context['uid'], 'sn' => $work['sn']]),
        ])->render();
    }

    /**
     * 打包时的 index.html：按藏 / 分类分组列出作品，可搜索。
     *
     * @param  array<string, mixed>  $context
     * @param  array{kind: string, works: list<array<string, mixed>>, title: string}  $scope
     */
    private function htmlIndex(array $context, array $scope): string
    {
        $groups = [];
        foreach ($scope['works'] as $work) {
            $names = $this->categoryNames($work, $context['lang']);
            $top = $names[0];
            $sub = implode(' · ', array_slice($names, 1)) ?: $top;
            $groups[$top][$sub][] = [
                'title' => $work['local_title'],
                'pali' => $work['title'],
                'progress' => (int) round($work['progress'] * 100),
                'href' => 'works/'.rawurlencode(self::safeName("{$work['sn']}-{$work['local_title']}")).'.html',
            ];
        }

        return view('exports.tipitaka.index', [
            'context' => $context,
            'scope' => $scope,
            'groups' => $groups,
            'averageProgress' => (int) round(100 * array_sum(array_column($scope['works'], 'progress')) / max(1, count($scope['works']))),
            'sourceUrl' => route('library.tipitaka.channels.show', ['channel' => $context['uid']]),
        ])->render();
    }

    /**
     * HTML 片段的纯文字（目录用）：去掉边注、脚注列表这些不属于标题的部分。
     */
    private function plainText(string $html): string
    {
        $doc = new DOMDocument;
        @$doc->loadHTML('<?xml encoding="UTF-8"><div>'.$html.'</div>', LIBXML_NOERROR);
        $xpath = new DOMXPath($doc);
        foreach ($xpath->query('//*[contains(@class,"sidenote") or contains(@class,"anno-footnotes") or contains(@class,"margin-toggle")]') ?: [] as $node) {
            $node->parentNode?->removeChild($node);
        }

        return trim((string) preg_replace('/\s+/u', ' ', $doc->textContent));
    }

    // ── markdown ────────────────────────────────────────────────────────

    /**
     * 一部作品的 markdown：README.md（目录）+ 每章一个文件。返回 zip 条目 => 临时文件。
     *
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $work
     * @param  list<array{para: int, level: int, sentences?: list<array{sid: string, html: string}>}>  $paragraphs
     * @return array<string, string>
     */
    private function markdownWork(string $dir, int $index, array $context, array $work, array $paragraphs, string $prefix): array
    {
        $chapters = $this->chapters($work, $paragraphs);
        $entries = [];
        $toc = [];
        // 00 只给序言（第一章之前的段落），正式的章从 01 起
        $offset = ($chapters[0]['start'] ?? null) === 0 ? 0 : 1;
        foreach ($chapters as $i => $chapter) {
            $file = sprintf('%02d-%s.md', $i + $offset, self::safeName($chapter['title']));
            $entries[$prefix.$file] = $this->writeFile($dir, "w{$index}-{$i}.md", $this->markdown($chapter['paragraphs'], $chapter['level']));
            $toc[] = '- ['.$this->escapeLink($chapter['title']).'](<'.$file.'>)';
        }
        $readme = "# {$work['local_title']}\n\n*{$work['title']}* · {$context['name']}\n\n"
            .__('library.export_generated_at', ['date' => $context['generated_at']])."\n\n"
            .implode("\n", $toc)."\n";
        $entries[$prefix.'README.md'] = $this->writeFile($dir, "w{$index}-readme.md", $readme);

        return $entries;
    }

    /**
     * 打包时的总目录 README.md：按分类列出各部作品（markdown 指向作品目录，epub / docx 指向文件）。
     *
     * @param  array<string, mixed>  $context
     * @param  array{kind: string, works: list<array<string, mixed>>, title: string}  $scope
     */
    private function markdownIndex(array $context, array $scope, string $format): string
    {
        $lines = ["# {$scope['title']}", '', "{$context['name']} · ".__('library.export_generated_at', ['date' => $context['generated_at']])];
        $current = null;
        foreach ($scope['works'] as $work) {
            $folder = $this->categoryFolder($work, $context['lang']);
            if ($folder !== $current) {
                $lines[] = '';
                $lines[] = '## '.str_replace('/', ' / ', $folder);
                $lines[] = '';
                $current = $folder;
            }
            $name = self::safeName("{$work['sn']}-{$work['local_title']}");
            $target = $format === 'markdown' ? "{$folder}/{$name}/README.md" : "{$folder}/{$name}.{$format}";
            $lines[] = '- ['.$this->escapeLink($work['local_title']).'](<'.$target.'>) · *'.$work['title'].'*';
        }

        return implode("\n", $lines)."\n";
    }

    /**
     * 按章切分：作品区间里比作品本身低一级的标题（实际出现的最高一级）各成一章；
     * 第一章之前的段落（作品标题、序言）归到第 0 章。没有译文的章不出现。
     *
     * @param  array<string, mixed>  $work
     * @param  list<array{para: int, level: int, sentences?: list<array{sid: string, html: string}>}>  $paragraphs
     * @return list<array{start: int, title: string, level: int, paragraphs: list<array<string, mixed>>}>
     */
    private function chapters(array $work, array $paragraphs): array
    {
        $headings = $this->headings($work);
        $inner = array_filter($headings, fn ($h, $para) => $para > $work['para'], ARRAY_FILTER_USE_BOTH);
        $splitLevel = $inner === [] ? 99 : min(array_column($inner, 0));
        $starts = array_keys(array_filter($inner, fn ($h) => $h[0] === $splitLevel));

        $chapters = [];
        foreach ($paragraphs as $p) {
            $start = 0;
            foreach ($starts as $s) {
                if ($s > $p['para']) {
                    break;
                }
                $start = $s;
            }
            $chapters[$start] ??= ['start' => $start, 'title' => $start === 0 ? $work['local_title'] : $headings[$start][1], 'level' => $splitLevel, 'paragraphs' => []];
            if ($p['para'] === $start) {
                // 章标题段有译文就用译文作章名
                $chapters[$start]['title'] = $this->markdownPlain($p) ?: $chapters[$start]['title'];
            }
            if ($start === 0 && $p['para'] === $work['para']) {
                // 作品标题是 README 的标题，不在第 0 章里重复
                continue;
            }
            $chapters[$start]['paragraphs'][] = $p;
        }

        return array_values(array_filter($chapters, fn ($c) => $c['paragraphs'] !== []));
    }

    /**
     * 把段落拼成一篇 markdown：标题段加 #（$baseLevel 那一级是 #），脚注整篇重新编号、正文放在文末。
     *
     * @param  list<array{para: int, level: int, sentences?: list<array{sid: string, html: string}>}>  $paragraphs
     */
    private function markdown(array $paragraphs, int $baseLevel): string
    {
        $number = 0;
        $notes = [];
        $blocks = [];
        foreach ($paragraphs as $p) {
            $parts = [];
            foreach ($p['sentences'] ?? [] as $sentence) {
                $pieces = preg_split('/\n\n(?=\[\^\d+\]: )/', $sentence['html']) ?: [$sentence['html']];
                $map = [];
                foreach (array_slice($pieces, 1) as $piece) {
                    if (preg_match('/^\[\^(\d+)\]: (.*)$/s', $piece, $m)) {
                        $map[$m[1]] = ++$number;
                        // 脚注正文多行时后续行要缩进，否则 pandoc 只认第一行
                        $notes[] = "[^{$number}]: ".str_replace("\n", "\n    ", trim($m[2]));
                    }
                }
                $parts[] = preg_replace_callback('/\[\^(\d+)\]/', fn ($m) => isset($map[$m[1]]) ? "[^{$map[$m[1]]}]" : '', $pieces[0]);
            }
            $text = trim(implode("\n", $parts));
            if ($text === '') {
                continue;
            }
            if ($p['level'] > 0) {
                $text = str_repeat('#', max(1, min(6, $p['level'] - $baseLevel + 1))).' '.str_replace("\n", ' ', $text);
            }
            $blocks[] = $text;
        }

        return implode("\n\n", $blocks)."\n".($notes === [] ? '' : "\n".implode("\n\n", $notes)."\n");
    }

    /**
     * 段落 markdown 的纯文字（章名用）：去掉脚注标记与正文。
     *
     * @param  array{sentences?: list<array{html: string}>}  $paragraph
     */
    private function markdownPlain(array $paragraph): string
    {
        $text = implode(' ', array_map(
            fn ($s) => (string) preg_replace('/\[\^\d+\]/', '', (preg_split('/\n\n(?=\[\^\d+\]: )/', $s['html']) ?: [''])[0]),
            $paragraph['sentences'] ?? [],
        ));

        return trim((string) preg_replace('/[*_`#]+|\s+/u', ' ', $text));
    }

    private function escapeLink(string $text): string
    {
        return str_replace(['[', ']'], ['\\[', '\\]'], $text);
    }

    // ── pandoc ──────────────────────────────────────────────────────────

    /**
     * 整部作品拼成一篇 markdown，交给 pandoc 转 epub / docx，返回生成的临时文件路径。
     *
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $work
     * @param  list<array<string, mixed>>  $paragraphs
     */
    private function pandoc(string $dir, int $index, string $format, array $context, array $work, array $paragraphs): string
    {
        $chapters = $this->chapters($work, $paragraphs);
        $source = '';
        foreach ($chapters as $chapter) {
            // 第 0 章（序言）没有自己的标题段，补一个，epub 目录里才有它
            $first = $chapter['paragraphs'][0];
            $needsTitle = $first['level'] !== $chapter['level'] && $chapter['level'] !== 99;
            $source .= ($needsTitle ? '# '.$chapter['title']."\n\n" : '').$this->markdown($chapter['paragraphs'], $chapter['level'])."\n";
        }
        $input = $this->writeFile($dir, "w{$index}.md", $source === '' ? "\n" : $source);
        $output = "{$dir}/w{$index}.{$format}";

        $command = [
            (string) config('mint.tipitaka_export.pandoc'),
            $input,
            // 译文里的 $、~、^ 与 --- 不是数学公式 / 上下标 / 元数据块
            '--from=markdown+east_asian_line_breaks-tex_math_dollars-raw_tex-subscript-superscript-yaml_metadata_block',
            '--output='.$output,
            '--toc',
            '--toc-depth=2',
            '--metadata=title:'.$work['local_title'],
            '--metadata=subtitle:'.$work['title'],
            '--metadata=author:'.$context['name'],
            '--metadata=lang:'.$context['lang'],
            '--metadata=date:'.$context['generated_at'],
        ];
        if ($format === 'epub') {
            $command[] = '--css='.resource_path('template/epub/tipitaka.css');
            $command[] = '--split-level=1';
        } else {
            $command[] = '--reference-doc='.resource_path('template/docx/paper.docx');
        }

        $process = new Process($command, $dir, null, null, (float) config('mint.tipitaka_export.pandoc_timeout'));
        $process->run();
        if (! $process->isSuccessful()) {
            throw new RuntimeException('pandoc failed: '.mb_substr($process->getErrorOutput(), 0, 2000));
        }

        return $output;
    }

    // ── offline ─────────────────────────────────────────────────────────

    /**
     * mobile 离线包：NDJSON，首行头、每段一行、末行 end（客户端用 count 校验完整）。
     * 存的是 gzip 后的字节，下载接口以 `Content-Encoding: gzip` 发出。
     * `html` 与 `/v3/tipitaka-reading?format=html&include=display` 的 display 完全相同。
     *
     * @param  array<string, mixed>  $status
     * @param  array<string, mixed>  $work
     * @param  callable(float): void  $tick
     */
    private function offline(string $dir, array $status, array $work, callable $tick): string
    {
        $paragraphs = $this->paragraphs($status['channel'], $work, 'html', $tick);
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        $lines = [json_encode([
            'type' => 'header',
            'format' => 1,
            'id' => $status['id'],
            'channel' => $status['channel'],
            'sn' => $work['sn'],
            'book' => $work['book'],
            'para' => $work['para'],
            'end' => $work['end'],
            'paragraphs' => count($paragraphs),
        ], $flags)];
        foreach ($paragraphs as $p) {
            $lines[] = json_encode(['type' => 'para', 'book' => $work['book'], 'para' => $p['para'], 'html' => $p['display']], $flags);
        }
        $lines[] = json_encode(['type' => 'end', 'count' => count($paragraphs)], $flags);

        return $this->writeFile($dir, 'offline.ndjson.gz', (string) gzencode(implode("\n", $lines)."\n", 6));
    }

    // ── files ───────────────────────────────────────────────────────────

    private function writeFile(string $dir, string $name, string $content): string
    {
        $path = "{$dir}/{$name}";
        file_put_contents($path, $content);

        return $path;
    }

    /**
     * 把临时文件按条目名打成 zip，返回 zip 字节。条目名按 UTF-8 标记，中文文件名解压不乱码。
     *
     * @param  array<string, string>  $entries  zip 里的路径 => 临时文件
     */
    private function zip(string $dir, array $entries): string
    {
        $path = "{$dir}/_bundle.zip";
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("cannot create zip {$path}");
        }
        ksort($entries, SORT_NATURAL);
        foreach ($entries as $name => $file) {
            $zip->addFile($file, $name, 0, 0, ZipArchive::FL_OVERWRITE | ZipArchive::FL_ENC_UTF_8);
        }
        $zip->close();

        return (string) file_get_contents($path);
    }
}
