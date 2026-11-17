// resources/js/modules/term-tooltip.js
// 原 term-tooltip.js 移至此处（从 js/ 根目录移入 modules/）。
// 所有阅读页公用（tipitaka read、anthology read、wiki show、blog show）。
// 在 reader.js 中 import，不在 app.js 全局加载（仅阅读页需要）。
//
// 文件内容：将现有 term-tooltip.js 内容直接复制至此，不做修改。
// 待 reader.js 建立后在此 import：
//   import './modules/term-tooltip';

import * as bootstrap from "bootstrap";

(function () {
    "use strict";

    // ── 缓存层 ────────────────────────────────────────────────────────
    const cache = {};

    // 下面的模板用 innerHTML 拼接，取回的文字先转义
    const escapeHtml = (text) =>
        String(text ?? "").replace(/[&<>"']/g, (c) => ({
            "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;",
        })[c]);

    // 百科页上点术语直接跳转；其它页面（阅读页）不跳走，「查看完整条目」在新标签页打开
    const onWikiPage = /^\/library\/wiki\//.test(window.location.pathname);

    // 词条的百科页地址：语言取当前百科页的，其它页面取 <html lang>
    function wikiUrl(id) {
        const m = window.location.pathname.match(/^\/library\/wiki\/([^/]+)/);
        const lang = m
            ? decodeURIComponent(m[1])
            : document.documentElement.lang || "zh-Hans";
        return `/library/wiki/${encodeURIComponent(lang)}/${encodeURIComponent(id)}`;
    }

    const linkTarget = onWikiPage ? "" : ' target="_blank" rel="noopener"';

    // v3：summary_text 是纯文本摘要（有摘要用摘要，没有取正文第一段）
    async function fetchTerm(id) {
        if (cache[id]) return cache[id];
        const res = await fetch(`/api/v3/terms/${id}`, {
            headers: { Accept: "application/json" },
        });
        if (!res.ok) throw new Error(`fetchTerm ${id} failed: ${res.status}`);
        const { data } = await res.json();
        cache[id] = {
            word: escapeHtml(data.word),
            meaning: escapeHtml(data.meaning),
            summary: escapeHtml(data.summary_text),
            url: wikiUrl(id),
        };
        return cache[id];
    }

    // ── 设备判断 ──────────────────────────────────────────────────────
    const isMobile = () => window.innerWidth < 768;

    // ── Skeleton 模板 ─────────────────────────────────────────────────
    function buildSkeletonContent() {
        return `
            <div class="wiki-term-skeleton">
                <div class="wiki-term-skeleton-word"></div>
                <div class="wiki-term-skeleton-line"></div>
                <div class="wiki-term-skeleton-line short"></div>
            </div>
        `;
    }

    // ── Popover 内容模板 ──────────────────────────────────────────────
    function buildPopoverContent(data) {
        const meaning = (data.meaning || "").trim();
        const summary = (data.summary || "").trim();
        const showSummary = summary && summary !== meaning;

        return `
            <div class="wiki-term-card-word">${data.word || ""}</div>
            <div class="wiki-term-card-body" style="padding: 10px 14px 12px;">
                ${
                    meaning
                        ? `<div class="wiki-term-card-meaning">${meaning}</div>`
                        : ""
                }
                ${
                    showSummary
                        ? `<div class="wiki-term-card-summary">${summary}</div>`
                        : ""
                }
            </div>
            <div><a href="${data.url}"${linkTarget}>查看完整条目</a></div>
        `;
    }

    // ── 桌面端：Bootstrap Popover ─────────────────────────────────────
    function initDesktopPopover(el, content) {
        if (el._wikiPopover) return el._wikiPopover;

        const popover = new bootstrap.Popover(el, {
            trigger: "manual",
            html: true,
            placement: "bottom",
            fallbackPlacements: ["top", "bottom"],
            customClass: "wiki-term-popover",
            content: content,
            sanitize: false,
        });

        el._wikiPopover = popover;

        let hideTimer = null;

        function scheduleHide() {
            hideTimer = setTimeout(() => {
                const tipEl = document.querySelector(".wiki-term-popover.show");
                if (tipEl && tipEl.matches(":hover")) return;
                popover.hide();
            }, 120);
        }

        function cancelHide() {
            clearTimeout(hideTimer);
        }

        el.addEventListener("mouseenter", () => {
            cancelHide();
            popover.show();
        });

        el.addEventListener("mouseleave", scheduleHide);

        el.addEventListener("shown.bs.popover", () => {
            const tipEl = document.querySelector(".wiki-term-popover.show");
            if (!tipEl) return;
            tipEl.addEventListener("mouseenter", cancelHide);
            tipEl.addEventListener("mouseleave", scheduleHide);
        });

        return popover;
    }

    function updateDesktopPopover(el, data) {
        const popover = el._wikiPopover;
        if (!popover) return;
        // 更新内容
        const tip = document.querySelector(".wiki-term-popover.show");
        if (tip) {
            tip.querySelector(".popover-body").innerHTML =
                buildPopoverContent(data);
        }
        // 同步 popover 内部 config，供下次 show 使用
        popover._config.content = buildPopoverContent(data);
    }

    // ── 移动端：Bootstrap Offcanvas ───────────────────────────────────
    let offcanvasInstance = null;

    function getOffcanvas() {
        if (offcanvasInstance) return offcanvasInstance;
        const el = document.getElementById("wikiTermDrawer");
        if (!el) return null;
        offcanvasInstance = new bootstrap.Offcanvas(el, { scroll: false });

        el.addEventListener("hidden.bs.offcanvas", () => {
            document
                .querySelectorAll(".offcanvas-backdrop")
                .forEach((b) => b.remove());
            document.body.classList.remove("modal-open");
            document.body.style.removeProperty("overflow");
            document.body.style.removeProperty("padding-right");
        });

        return offcanvasInstance;
    }

    function showMobileDrawerSkeleton() {
        const oc = getOffcanvas();
        if (!oc) return;

        document.getElementById("wikiTermDrawerWord").innerHTML =
            '<div class="wiki-term-skeleton-word"></div>';
        document.getElementById("wikiTermDrawerMeaning").innerHTML =
            '<div class="wiki-term-skeleton-line short"></div>';
        document.getElementById(
            "wikiTermCardSlot"
        ).innerHTML = `<div class="wiki-term-skeleton">
                <div class="wiki-term-skeleton-line"></div>
                <div class="wiki-term-skeleton-line short"></div>
            </div>`;
        document.getElementById("wikiTermDrawerLink").style.display = "none";

        oc.show();
    }

    function fillMobileDrawer(data) {
        const meaning = (data.meaning || "").trim();
        const summary = (data.summary || "").trim();
        const showSummary = summary && summary !== meaning;

        document.getElementById("wikiTermDrawerWord").textContent =
            data.word || "";
        document.getElementById("wikiTermDrawerMeaning").textContent = meaning;
        document.getElementById("wikiTermCardSlot").innerHTML = showSummary
            ? `<div class="wiki-term-card-summary">${summary}</div>`
            : "";
        const link = document.getElementById("wikiTermDrawerLink");
        if (link && data.url) {
            link.href = data.url;
            if (!onWikiPage) {
                link.target = "_blank";
                link.rel = "noopener";
            }
            link.style.display = "";
        }
    }

    // ── 入口：扫描所有 .term-ref ──────────────────────────────────────
    function init() {
        const refs = document.querySelectorAll(".term-ref[data-id]");
        if (!refs.length) return;

        refs.forEach((el) => {
            // 桌面：mouseenter 时先显示 skeleton，数据回来后更新
            el.addEventListener("mouseenter", async function onFirstEnter() {
                if (isMobile()) return;

                // 立即显示 skeleton popover
                const popover = initDesktopPopover(el, buildSkeletonContent());
                popover.show();

                try {
                    const data = await fetchTerm(el.dataset.id);
                    updateDesktopPopover(el, data);
                } catch (e) {
                    console.warn(
                        "[WikiPāli] term fetch failed",
                        el.dataset.id,
                        e
                    );
                    popover.hide();
                }

                el.removeEventListener("mouseenter", onFirstEnter);
            });

            // 移动端：点击立即弹出 skeleton，数据回来后填充（抽屉里有「查看完整条目」）
            // 桌面端：百科页上点击直接跳到那条术语
            el.addEventListener("click", async (e) => {
                if (!isMobile()) {
                    if (onWikiPage) {
                        e.preventDefault();
                        window.location.href = wikiUrl(el.dataset.id);
                    }
                    return;
                }
                e.preventDefault();

                showMobileDrawerSkeleton();

                try {
                    const data = await fetchTerm(el.dataset.id);
                    fillMobileDrawer(data);
                } catch (err) {
                    console.warn(
                        "[WikiPāli] term fetch failed",
                        el.dataset.id,
                        err
                    );
                }
            });
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();
