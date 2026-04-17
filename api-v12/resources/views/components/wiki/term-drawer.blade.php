{{-- resources/views/components/wiki/term-drawer.blade.php --}}
{{--
    全局唯一的术语抽屉（移动端）。
    JS 在点击 .term-ref 时填充内容并调用 show()。
--}}
<div class="offcanvas offcanvas-bottom wiki-term-drawer"
    tabindex="-1"
    id="wikiTermDrawer"
    aria-labelledby="wikiTermDrawerLabel">

    <div class="offcanvas-header">
        <div>
            <div id="wikiTermDrawerWord" class="wiki-drawer-word"></div>
            <div id="wikiTermDrawerMeaning" class="wiki-drawer-meaning"></div>
        </div>
        <button type="button"
            class="btn-close"
            data-bs-dismiss="offcanvas"
            aria-label="关闭">
        </button>
    </div>

    <div class="offcanvas-body">
        <div id="wikiTermCardSlot"></div>
        <a id="wikiTermDrawerLink" class="wiki-drawer-link" href="#" style="display:none;">
            查看完整条目 →
        </a>
    </div>

</div>
