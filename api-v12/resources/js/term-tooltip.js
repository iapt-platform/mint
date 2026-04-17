/**
 * resources/js/term-tooltip.js
 * WikiPāli 术语 tooltip（桌面 Popover）+ 抽屉（移动端 Offcanvas）
 *
 * 依赖：Bootstrap 5（bootstrap.Popover / bootstrap.Offcanvas）
 *       已由 Tabler 全局引入，无需重复 import
 */

(function () {
    "use strict";

    // ── 缓存层 ────────────────────────────────────────────────────────
    const cache = {};

    async function fetchTerm(id) {
        if (cache[id]) return cache[id];
        const res = await fetch(`/api/v2/terms/${id}`);
        if (!res.ok) throw new Error(`fetchTerm ${id} failed: ${res.status}`);
        const json = await res.json();
        cache[id] = json.data;
        return json.data;
    }

    // ── 设备判断 ──────────────────────────────────────────────────────
    const isMobile = () => window.innerWidth < 768;

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
            <div><a>查看完整条目</a></div>
        `;
    }

    // ── 桌面端：Bootstrap Popover ─────────────────────────────────────
    function initDesktopPopover(el, data) {
        // 防止重复初始化
        if (el._wikiPopover) return el._wikiPopover;

        const popover = new bootstrap.Popover(el, {
            trigger: "manual",
            html: true,
            placement: "bottom", // flip 会自动在空间不足时翻转为 top
            fallbackPlacements: ["top", "bottom"],
            customClass: "wiki-term-popover",
            content: buildPopoverContent(data),
            // 让 popover 自身也可以接收鼠标事件（解决移入气泡消失问题）
            // Bootstrap 5.2+ 支持 sanitize: false 保留自定义 html
            sanitize: false,
        });

        el._wikiPopover = popover;

        let hideTimer = null;

        function scheduleHide() {
            hideTimer = setTimeout(() => {
                // 如果鼠标此刻在 popover 内，不关闭
                const tipEl = document.querySelector(".wiki-term-popover.show");
                if (tipEl && tipEl.matches(":hover")) return;
                popover.hide();
            }, 120);
        }

        function cancelHide() {
            clearTimeout(hideTimer);
        }

        // 触发元素
        el.addEventListener("mouseenter", () => {
            cancelHide();
            popover.show();
        });

        el.addEventListener("mouseleave", scheduleHide);

        // Popover 显示后，给气泡本身绑定 mouseenter / mouseleave
        el.addEventListener("shown.bs.popover", () => {
            const tipEl = document.querySelector(".wiki-term-popover.show");
            if (!tipEl) return;
            tipEl.addEventListener("mouseenter", cancelHide);
            tipEl.addEventListener("mouseleave", scheduleHide);
        });

        return popover;
    }

    // ── 移动端：Bootstrap Offcanvas（全局共享一个） ────────────────────
    let offcanvasInstance = null;

    function getOffcanvas() {
        if (offcanvasInstance) return offcanvasInstance;
        const el = document.getElementById("wikiTermDrawer");
        if (!el) return null;
        offcanvasInstance = new bootstrap.Offcanvas(el, { scroll: false });
        return offcanvasInstance;
    }

    function showMobileDrawer(data) {
        const oc = getOffcanvas();
        if (!oc) return;

        const meaning = (data.meaning || "").trim();
        const summary = (data.summary || "").trim();
        const showSummary = summary && summary !== meaning;

        document.getElementById("wikiTermCardSlot").innerHTML = `
            <div class="wiki-term-card">
                <div class="wiki-term-card-word">${data.word || ""}</div>
                <div class="wiki-term-card-body">
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
            </div>
        `;

        document.getElementById("wikiTermDrawerLink").style.display = "none";
        oc.show();
    }

    // ── 入口：扫描所有 .term-ref ──────────────────────────────────────
    function init() {
        const refs = document.querySelectorAll(".term-ref[data-id]");
        if (!refs.length) return;

        refs.forEach((el) => {
            // 桌面：mouseenter 时懒加载并初始化 Popover
            el.addEventListener("mouseenter", async function onFirstEnter() {
                if (isMobile()) return;

                try {
                    const data = await fetchTerm(el.dataset.id);
                    const popover = initDesktopPopover(el, data);
                    // 首次加载后直接 show（mouseenter 已触发）
                    popover.show();
                } catch (e) {
                    console.warn(
                        "[WikiPāli] term fetch failed",
                        el.dataset.id,
                        e
                    );
                }

                // 移除首次监听，后续由 Popover 自身管理
                el.removeEventListener("mouseenter", onFirstEnter);
            });

            // 移动端：点击触发抽屉
            el.addEventListener("click", async (e) => {
                if (!isMobile()) return;
                e.preventDefault();

                try {
                    const data = await fetchTerm(el.dataset.id);
                    showMobileDrawer(data);
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

    // DOM 就绪后执行
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();
