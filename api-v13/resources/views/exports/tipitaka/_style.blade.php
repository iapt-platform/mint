{{-- 三藏下载导出 HTML 的公共样式（work / index 共用），全部内联：文件要能离线单独打开。 --}}
<style>
:root{
  --paper:#fdfbf7; --ink:#2a2219; --muted:#8a7760; --accent:#9a7b52; --gold:#c9a66b; --line:rgba(120,90,50,.18);
  --card:rgba(255,255,255,.75); --note-bg:#f5deb330; --note-line:wheat; --link:#8a5a2b;
  --serif:"Noto Serif SC","Noto Serif","Source Han Serif SC","Songti SC",Georgia,serif;
  --sans:-apple-system,BlinkMacSystemFont,"Segoe UI","PingFang SC","Microsoft YaHei",sans-serif;
}
@media (prefers-color-scheme: dark){:root:not([data-theme="light"]){
  --paper:#15120f; --ink:#e6ddcf; --muted:#a8977f; --accent:#c4a172; --line:rgba(200,170,120,.18);
  --card:rgba(255,255,255,.04); --note-bg:rgba(139,109,56,.15); --note-line:#6b5a3e; --link:#d9b27c;
}}
:root[data-theme="dark"]{
  --paper:#15120f; --ink:#e6ddcf; --muted:#a8977f; --accent:#c4a172; --line:rgba(200,170,120,.18);
  --card:rgba(255,255,255,.04); --note-bg:rgba(139,109,56,.15); --note-line:#6b5a3e; --link:#d9b27c;
}
*{box-sizing:border-box}
html{scroll-behavior:smooth}
body{margin:0;background:var(--paper);color:var(--ink);font:15px/1.6 var(--sans)}
a{color:var(--link);text-decoration:none}
a:hover{text-decoration:underline}
button{font:inherit;color:inherit}
.topbar{position:sticky;top:0;z-index:5;display:flex;gap:12px;align-items:center;padding:10px 16px;border-bottom:1px solid var(--line);background:var(--paper);font-size:13px}
.topbar .title{flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-family:var(--serif)}
.topbar button{border:1px solid var(--line);background:transparent;border-radius:6px;padding:3px 10px;cursor:pointer}
.foot{text-align:center;font-size:12px;color:var(--muted);padding:24px 16px}
@media print{.topbar,.toc,.no-print{display:none!important}.layout{display:block!important}}
</style>
