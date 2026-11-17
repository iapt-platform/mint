{{-- resources/views/components/wiki/entry-actions.blade.php --}}
@props(['editUrl', 'title' => ''])

<div class="wiki-entry-actions">

    {{-- 分享到微信 --}}
    <button class="wiki-action-btn"
        id="wikiShareWechat"
        title="分享到微信"
        data-title="{{ $title }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="1.5"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M9.5 9a3.5 3.5 0 0 1 5 0" />
            <path d="M5.5 11.5C4 10 3 8.1 3 6c0-3.3 3.1-6 7-6s7 2.7 7 6c0 .6-.1 1.2-.3 1.7" />
            <path d="M12 20c-4.4 0-8-2.9-8-6.5S7.6 7 12 7s8 2.9 8 6.5c0 1.4-.5 2.7-1.4 3.8l.4 2.7-2.6-1.1A9 9 0 0 1 12 20z" />
        </svg>
    </button>

    {{-- 编辑 --}}
    <a class="wiki-action-btn"
        href="{{ $editUrl }}"
        target="_blank"
        rel="noopener"
        title="编辑条目">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="1.5"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
        </svg>
    </a>

</div>

{{-- 微信二维码弹窗 --}}
<div class="wiki-wechat-modal" id="wikiWechatModal" style="display:none;">
    <div class="wiki-wechat-modal-backdrop" id="wikiWechatBackdrop"></div>
    <div class="wiki-wechat-modal-box">
        <div class="wiki-wechat-modal-title">分享到微信</div>
        <div class="wiki-wechat-modal-desc">使用微信扫描二维码</div>
        <div id="wikiWechatQr" class="wiki-wechat-qr"></div>
        <button class="wiki-wechat-modal-close" id="wikiWechatClose">关闭</button>
    </div>
</div>

@push('scripts')
@vite('resources/js/modules/wiki-share.js')
@endpush
