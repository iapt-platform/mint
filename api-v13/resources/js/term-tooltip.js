/**
 * resources/js/term-tooltip.js
 */
import * as bootstrap from "bootstrap";

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
            <div><a>查看完整条目</a></div>
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

            // 移动端：点击立即弹出 skeleton，数据回来后填充
            el.addEventListener("click", async (e) => {
                if (!isMobile()) return;
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
