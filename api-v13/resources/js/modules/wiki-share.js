// resources/js/modules/wiki-share.js
// 百科词条页「分享到微信」：弹窗里用 qrcode 在本地生成当前页面 URL 的二维码（不再请求第三方服务）。
// 仅在有 #wikiShareWechat / #wikiWechatModal / #wikiWechatQr 的页面（library/wiki/show）生效。

import QRCode from "qrcode";

function initWikiShare() {
    const btn = document.getElementById("wikiShareWechat");
    const modal = document.getElementById("wikiWechatModal");
    const backdrop = document.getElementById("wikiWechatBackdrop");
    const closeBtn = document.getElementById("wikiWechatClose");
    const qrEl = document.getElementById("wikiWechatQr");

    if (!btn || !modal || !qrEl) {
        return;
    }

    const canvas = document.createElement("canvas");
    qrEl.replaceChildren(canvas);

    async function open() {
        try {
            await QRCode.toCanvas(canvas, window.location.href, {
                width: 180,
                margin: 2,
                errorCorrectionLevel: "M",
                color: {
                    dark: "#1d273b",
                    light: "#ffffff",
                },
            });
        } catch (err) {
            console.warn("[WikiPāli] QR code generation failed", err);
        }
        modal.style.display = "flex";
    }

    function close() {
        modal.style.display = "none";
    }

    btn.addEventListener("click", open);
    closeBtn?.addEventListener("click", close);
    backdrop?.addEventListener("click", close);
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            close();
        }
    });
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initWikiShare);
} else {
    initWikiShare();
}
