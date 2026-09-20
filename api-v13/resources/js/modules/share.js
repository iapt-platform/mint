// resources/js/modules/share.js
// 阅读页「分享」入口：点击「二维码」弹出 Modal，用 qrcode 生成当前页面 URL 的二维码。
// 仅在有 #shareQrModal / #shareQrCanvas 的页面（library/book/read）生效。

import QRCode from "qrcode";

function initShare() {
    const modalEl = document.getElementById("shareQrModal");
    const canvas = document.getElementById("shareQrCanvas");

    if (!modalEl || !canvas) {
        return;
    }

    modalEl.addEventListener("show.bs.modal", async () => {
        try {
            await QRCode.toCanvas(canvas, window.location.href, {
                width: 240,
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
    });
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initShare);
} else {
    initShare();
}
