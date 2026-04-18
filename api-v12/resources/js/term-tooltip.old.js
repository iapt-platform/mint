/**
 * <span
    class="term-ref"
    data-id="b45e7b10-2b75-4f5f-ac63-be686116043c"
    data-term="anicca"
>
    无常
</span>
 */
document.addEventListener("DOMContentLoaded", () => {
    const cache = {};

    const drawerEl = document.getElementById("termDrawer");

    const drawer = new bootstrap.Offcanvas(drawerEl);

    const drawerTitle = document.getElementById("termDrawerTitle");

    const drawerBody = document.getElementById("termDrawerBody");

    function isMobile() {
        return window.innerWidth < 768;
    }

    async function fetchTerm(term) {
        if (cache[term]) return cache[term];
        console.info("term", term);
        const res = await fetch(`/api/v2/terms/${term}`);

        const data = await res.json();

        cache[term] = data.data;

        return data.data;
    }

    document.querySelectorAll(".term-ref").forEach((el) => {
        let popover = null;

        el.addEventListener("mouseenter", async () => {
            console.info("mouseenter");
            if (isMobile()) return;

            if (popover) return;
            const pali = el.dataset.term;
            const data = await fetchTerm(el.dataset.id);

            popover = new bootstrap.Popover(el, {
                trigger: "manual",
                html: true,
                placement: "bottom",

                content: `
            <div style="max-width:300px">
                <h4>${data.word}</h4>
                <div>${data.summary}</div>
            </div>
        `,
            });

            popover.show();
        });

        el.addEventListener("mouseleave", () => {
            if (popover) {
                popover.dispose();
                popover = null;
            }
        });

        el.addEventListener("click", async () => {
            if (!isMobile()) return;

            const data = await fetchTerm(el.dataset.id);

            drawerTitle.innerHTML = data.word;

            drawerBody.innerHTML = data.summary;

            drawer.show();
        });
    });
});
