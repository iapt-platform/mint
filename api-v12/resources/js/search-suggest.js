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
                });

                const json = await res.json();

                renderSuggestions(json.data.suggestions || []);
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
                    return `
                    <button type="button"
                        class="dropdown-item"
                        data-text="${item.text}">

                        <div class="d-flex justify-content-between">
                            <span>${item.text}</span>
                            <small class="text-muted">${item.resource_type}</small>
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
