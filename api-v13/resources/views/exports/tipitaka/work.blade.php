{{-- 三藏下载：一部作品的单个 HTML 文件（TipitakaExportBuilder::htmlWork()）。
     样式与脚本全部内联，离线可用。正文 $body 是 PaliContentService::readParagraph() 的 display，原样输出。
     变量：$context{uid,name,lang,generated_at}、$work（作品 + local_title）、$toc[{id,depth,title}]、
           $body、$indexHref（打包时指回 index.html，单独下载时为 null）、$sourceUrl
--}}
<!doctype html>
<html lang="{{ $context['lang'] }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="generator" content="WikiPāli">
<title>{{ $work['local_title'] }} · {{ $context['name'] }}</title>
@include('exports.tipitaka._style')
<style>
.layout{display:grid;grid-template-columns:240px minmax(0,1fr);gap:40px;max-width:1080px;margin:0 auto;padding:24px 16px 80px}
@media(max-width:860px){.layout{grid-template-columns:1fr}.toc{display:none}.toc.open{display:block;position:static;max-height:none}}
.toc{position:sticky;top:60px;align-self:start;font-size:13px;max-height:calc(100vh - 80px);overflow:auto}
.toc h4{margin:0 0 8px;font-size:12px;letter-spacing:.15em;color:var(--accent)}
.toc a{display:block;color:inherit;padding:4px 10px;border-left:2px solid var(--line);opacity:.82}
.toc a.d1{padding-left:22px;font-size:12.5px}
.toc a.d2{padding-left:34px;font-size:12px}
.toc a:hover,.toc a.on{opacity:1;border-color:var(--gold);color:var(--link);text-decoration:none}
article{font:17px/1.95 var(--serif);max-width:42em;counter-reset:sn}
.titlepage{text-align:center;padding:16px 0 36px;border-bottom:1px solid var(--line);margin-bottom:28px}
.titlepage h1{font-size:30px;margin:0;line-height:1.3}
.titlepage .pali{font-style:italic;color:var(--accent);margin-top:4px}
.titlepage .meta{font:13px var(--sans);color:var(--muted);margin-top:12px}
article h1,article h2,article h3,article h4,article h5,article h6{line-height:1.35;margin:1.8em 0 .6em;scroll-margin-top:64px;font-weight:600}
article h1{font-size:26px}article h2{font-size:23px}article h3{font-size:19px}article h4,article h5,article h6{font-size:17px}
article div.sentence{display:inline}
article .para-block{margin:0 0 1em;text-align:justify}
article .origin{color:#8b2a1e}
article input.margin-toggle,article .anno-fn-toggle{display:none}
article label.sidenote-number{cursor:pointer;counter-increment:sn}
article label.sidenote-number::after{content:"[" counter(sn) "]";font:11px var(--sans);color:var(--link);vertical-align:.5em;margin:0 1px}
article .sidenote,article .marginnote{display:none}
article .margin-toggle:checked + .sidenote,article .margin-toggle:checked + .marginnote{display:block;margin:.6em 0;padding:6px 10px;border:1px solid var(--note-line);border-radius:8px;background:var(--note-bg);font-size:14px;line-height:1.65}
article.notes-open .sidenote,article.notes-open .marginnote{display:block;margin:.6em 0;padding:6px 10px;border:1px solid var(--note-line);border-radius:8px;background:var(--note-bg);font-size:14px;line-height:1.65}
article .anno-footnotes{margin:.4em 0 1em}
article .anno-footnote{margin:.3em 0;padding:5px 10px;font:13.5px/1.6 var(--sans);color:var(--muted);border:1px solid var(--line);border-radius:6px}
article .anno-fn-label{display:block;cursor:pointer}
article .anno-jump{display:none}
</style>
</head>
<body>
<div class="topbar">
    @if($indexHref)
    <a href="{{ $indexHref }}">‹ {{ __('library.export_index') }}</a>
    @endif
    <span class="title">{{ $work['local_title'] }} · {{ $context['name'] }}</span>
    <button type="button" data-toc-toggle class="no-print">{{ __('library.toc') }}</button>
    <button type="button" data-notes-toggle class="no-print">{{ __('library.export_show_notes') }}</button>
    <button type="button" data-font class="no-print">A±</button>
    <button type="button" data-theme-toggle class="no-print" aria-label="theme">◐</button>
</div>
<div class="layout">
    <nav class="toc" data-toc>
        <h4>{{ __('library.toc') }}</h4>
        @foreach($toc as $item)
        <a href="#{{ $item['id'] }}" class="d{{ min(2, max(0, $item['depth'])) }}">{{ $item['title'] }}</a>
        @endforeach
    </nav>
    <article data-article>
        <div class="titlepage">
            <h1>{{ $work['local_title'] }}</h1>
            @if($work['title'] !== $work['local_title'])
            <div class="pali">{{ $work['title'] }}</div>
            @endif
            <div class="meta">{{ $context['name'] }} · {{ __('library.export_generated_at', ['date' => $context['generated_at']]) }}</div>
        </div>
        {!! $body !!}
    </article>
</div>
<div class="foot">{{ __('library.export_source') }} <a href="{{ $sourceUrl }}">{{ $sourceUrl }}</a></div>
<script>
(function () {
    var q = function (s) { return document.querySelector(s); };
    q('[data-toc-toggle]').onclick = function () { q('[data-toc]').classList.toggle('open'); };
    q('[data-notes-toggle]').onclick = function () { q('[data-article]').classList.toggle('notes-open'); };
    var size = 17;
    q('[data-font]').onclick = function () { size = size >= 21 ? 15 : size + 2; q('[data-article]').style.fontSize = size + 'px'; };
    q('[data-theme-toggle]').onclick = function () {
        var root = document.documentElement;
        var dark = root.dataset.theme ? root.dataset.theme === 'dark' : matchMedia('(prefers-color-scheme: dark)').matches;
        root.dataset.theme = dark ? 'light' : 'dark';
    };
    var links = document.querySelectorAll('[data-toc] a');
    if (!('IntersectionObserver' in window) || !links.length) return;
    var byId = {};
    links.forEach(function (a) { byId[a.getAttribute('href').slice(1)] = a; });
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (!e.isIntersecting) return;
            links.forEach(function (a) { a.classList.remove('on'); });
            byId[e.target.id].classList.add('on');
        });
    }, { rootMargin: '0px 0px -70% 0px' });
    Object.keys(byId).forEach(function (id) {
        var el = document.getElementById(id);
        if (el) observer.observe(el);
    });
})();
</script>
</body>
</html>
