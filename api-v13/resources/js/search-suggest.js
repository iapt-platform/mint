// 搜索框的前缀补全，调 GET /api/v3/search-suggestions（{data: [{text, resource_type, …}], meta}）

/** 候选文本来自索引内容，插进 HTML 前必须转义 */
const escapeHtml = (value) =>
    String(value ?? "").replace(/[&<>"']/g, (ch) => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;",
    })[ch]);

document.addEventListener("DOMContentLoaded", () => {
    const inputs = document.querySelectorAll(".search-input");

    inputs.forEach((input) => {
        const form = input.closest(".search-input-form");
        const dropdown = form.querySelector(".search-suggest-dropdown");

        let controller = null;

        input.addEventListener("input", async () => {
            const q = input.value.trim();

            if (q.length < 2) {
                dropdown.classList.remove("show");
                return;
            }

            // 取消上一次请求（防抖 + 避免竞态）
            if (controller) controller.abort();
            controller = new AbortController();

            try {
                const url = new URL(
                    input.dataset.suggestUrl,
                    window.location.origin
                );
                url.searchParams.set("q", q);
                url.searchParams.set("limit", 10);

                const res = await fetch(url, {
                    signal: controller.signal,
                    headers: { Accept: "application/json" },
                });

                // 补全只是辅助：失败（422 / 503 …）就不显示下拉，不打断输入
                if (!res.ok) {
                    dropdown.classList.remove("show");
                    return;
                }

                const json = await res.json();

                renderSuggestions(json.data || []);
            } catch (e) {
                if (e.name !== "AbortError") {
                    console.error(e);
                }
            }
        });

        function renderSuggestions(list) {
            if (!list.length) {
                dropdown.classList.remove("show");
                return;
            }

            dropdown.innerHTML = list
                .map((item) => {
                    const text = escapeHtml(item.text);
                    return `
                    <button type="button"
                        class="dropdown-item"
                        data-text="${text}">

                        <div class="d-flex justify-content-between">
                            <span>${text}</span>
                            <small class="text-muted">${escapeHtml(item.resource_type)}</small>
                        </div>
                    </button>
                `;
                })
                .join("");

            dropdown.classList.add("show");
        }

        // 点击选择
        dropdown.addEventListener("click", (e) => {
            const btn = e.target.closest(".dropdown-item");
            if (!btn) return;

            input.value = btn.dataset.text;
            dropdown.classList.remove("show");

            form.submit(); // 或者只填充不提交
        });

        // 失焦隐藏
        input.addEventListener("blur", () => {
            setTimeout(() => dropdown.classList.remove("show"), 150);
        });

        input.addEventListener("focus", () => {
            if (dropdown.innerHTML.trim()) {
                dropdown.classList.add("show");
            }
        });
    });
});
