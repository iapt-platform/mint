{{-- 三藏下载：打包下载时的总目录 index.html（TipitakaExportBuilder::htmlIndex()）。
     按藏 → 分类分组列出作品，可搜索（不分大小写与变音符号）、可按藏筛选。样式与脚本全部内联，离线可用。
     变量：$context{uid,name,lang,generated_at}、$scope{kind,title,works}、
           $groups[藏][分类][{title,pali,progress,href}]、$averageProgress、$sourceUrl
--}}
<!doctype html>
<html lang="{{ $context['lang'] }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="generator" content="WikiPāli">
<title>{{ $scope['title'] }} · {{ $context['name'] }}</title>
@include('exports.tipitaka._style')
<style>
body{background:#faf7f1}
@media (prefers-color-scheme: dark){:root:not([data-theme="light"]) body{background:#16130f}}
:root[data-theme="dark"] body{background:#16130f}
.hero{position:relative;padding:48px 16px 32px;text-align:center;border-bottom:1px solid var(--line)}
.hero .kicker{letter-spacing:.3em;font-size:12px;color:var(--accent);text-transform:uppercase}
.hero h1{font:600 34px/1.25 var(--serif);margin:10px 0 6px}
.hero p{margin:0;color:var(--muted)}
.hero [data-theme-toggle]{position:absolute;right:16px;top:16px;border:1px solid var(--line);background:transparent;border-radius:6px;padding:3px 10px;cursor:pointer}
.stats{display:flex;justify-content:center;gap:28px;margin-top:18px;flex-wrap:wrap;font-size:13px;color:var(--muted)}
.stats b{display:block;font:600 22px var(--serif);color:var(--ink)}
.tools{max-width:980px;margin:0 auto;padding:20px 16px 4px;display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.search{flex:1;min-width:220px;position:relative}
.search input{width:100%;padding:10px 14px 10px 36px;border-radius:24px;border:1px solid var(--line);background:var(--card);font:inherit;color:inherit}
.search::before{content:"⌕";position:absolute;left:14px;top:7px;font-size:18px;color:var(--accent)}
.seg{display:flex;gap:4px;flex-wrap:wrap}
.seg button{border:1px solid var(--line);background:transparent;border-radius:20px;padding:6px 12px;cursor:pointer;font-size:13px}
.seg button.on{background:#6b4a26;border-color:#6b4a26;color:#fff8ec}
main{max-width:980px;margin:0 auto;padding:8px 16px 40px}
.pitaka{margin-top:26px}
.pitaka>h2{font:600 20px var(--serif);display:flex;align-items:baseline;gap:10px;margin:0 0 4px;border-bottom:2px solid var(--gold);padding-bottom:6px}
.pitaka>h2 small{font:13px var(--sans);color:var(--muted)}
.cat{margin:16px 0 6px;font-size:13px;color:var(--accent);letter-spacing:.05em}
.works{display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:10px}
.work{display:block;padding:12px 14px;border-radius:8px;background:var(--card);border:1px solid var(--line);color:inherit;transition:transform .12s,box-shadow .12s}
.work:hover{transform:translateY(-1px);box-shadow:0 4px 14px rgba(90,60,20,.12);text-decoration:none}
.work .t{font:600 15px/1.35 var(--serif)}
.work .p{font:italic 12.5px var(--serif);color:var(--accent)}
.work .m{display:flex;align-items:center;gap:8px;margin-top:8px;font-size:12px;color:var(--muted)}
.work .pb{flex:1;height:4px;border-radius:2px;background:var(--line);overflow:hidden}
.work .pb i{display:block;height:100%;background:var(--gold)}
.empty{text-align:center;color:var(--muted);padding:40px 0;display:none}
</style>
</head>
<body>
<header class="hero">
    <button type="button" data-theme-toggle aria-label="theme">◐</button>
    <div class="kicker">WikiPāli · Tipiṭaka</div>
    <h1>{{ $scope['title'] }}</h1>
    <p>{{ $context['name'] }} · {{ __('library.export_generated_at', ['date' => $context['generated_at']]) }}</p>
    <div class="stats">
        <div><b>{{ count($scope['works']) }}</b>{{ __('library.export_works') }}</div>
        <div><b>{{ count($groups) }}</b>{{ __('library.export_groups') }}</div>
        <div><b>{{ $averageProgress }}%</b>{{ __('library.export_average_progress') }}</div>
    </div>
</header>
<div class="tools">
    <div class="search"><input type="search" data-q placeholder="{{ __('library.export_search_placeholder') }}"></div>
    @if(count($groups) > 1)
    <div class="seg" data-seg>
        <button type="button" class="on" data-k="">{{ __('library.all') }}</button>
        @foreach(array_keys($groups) as $top)
        <button type="button" data-k="{{ $top }}">{{ $top }}</button>
        @endforeach
    </div>
    @endif
</div>
<main data-body>
    @foreach($groups as $top => $subs)
    <section class="pitaka" data-pitaka="{{ $top }}">
        <h2>{{ $top }} <small>{{ array_sum(array_map('count', $subs)) }} {{ __('library.export_works') }}</small></h2>
        @foreach($subs as $sub => $works)
        @if($sub !== $top)
        <div class="cat">{{ $sub }}</div>
        @endif
        <div class="works">
            @foreach($works as $w)
            <a class="work" href="{{ $w['href'] }}" data-s="{{ mb_strtolower($w['title'].' '.$w['pali'].' '.$top.' '.$sub) }}">
                <div class="t">{{ $w['title'] }}</div>
                @if($w['pali'] !== $w['title'])
                <div class="p">{{ $w['pali'] }}</div>
                @endif
                <div class="m"><span class="pb"><i style="width: {{ $w['progress'] }}%"></i></span><span>{{ $w['progress'] }}%</span></div>
            </a>
            @endforeach
        </div>
        @endforeach
    </section>
    @endforeach
</main>
<div class="empty" data-empty>{{ __('library.export_no_match') }}</div>
<div class="foot">{{ __('library.export_source') }} <a href="{{ $sourceUrl }}">{{ $sourceUrl }}</a></div>
<script>
(function () {
    var fold = function (s) { return s.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, ''); };
    var input = document.querySelector('[data-q]');
    var seg = '';
    var cards = Array.prototype.slice.call(document.querySelectorAll('.work'));
    cards.forEach(function (c) { c.dataset.f = fold(c.dataset.s); });
    var apply = function () {
        var q = fold(input.value.trim());
        var shown = 0;
        document.querySelectorAll('[data-pitaka]').forEach(function (section) {
            var any = 0;
            section.querySelectorAll('.works').forEach(function (grid) {
                var visible = 0;
                grid.querySelectorAll('.work').forEach(function (card) {
                    var ok = !q || card.dataset.f.indexOf(q) !== -1;
                    card.style.display = ok ? '' : 'none';
                    visible += ok ? 1 : 0;
                });
                grid.style.display = visible ? '' : 'none';
                var label = grid.previousElementSibling;
                if (label && label.classList.contains('cat')) label.style.display = visible ? '' : 'none';
                any += visible;
            });
            var show = any > 0 && (!seg || section.dataset.pitaka === seg);
            section.style.display = show ? '' : 'none';
            shown += show ? 1 : 0;
        });
        document.querySelector('[data-empty]').style.display = shown ? 'none' : 'block';
    };
    input.addEventListener('input', apply);
    document.querySelectorAll('[data-seg] button').forEach(function (b) {
        b.addEventListener('click', function () {
            seg = b.dataset.k;
            document.querySelectorAll('[data-seg] button').forEach(function (x) { x.classList.toggle('on', x === b); });
            apply();
        });
    });
    document.querySelector('[data-theme-toggle]').onclick = function () {
        var root = document.documentElement;
        var dark = root.dataset.theme ? root.dataset.theme === 'dark' : matchMedia('(prefers-color-scheme: dark)').matches;
        root.dataset.theme = dark ? 'light' : 'dark';
    };
})();
</script>
</body>
</html>
