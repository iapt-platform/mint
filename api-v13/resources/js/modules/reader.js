// resources/js/modules/reader.js

export function initReader() {
    injectCommentaryMarkers();
    injectEvaluateMarkers();
    initTocToggle();
}

// TOC 折叠/展开：点击按钮切换所在 .toc-tree 的 .toc-expanded 类
// offcanvas 与侧边栏各有一份 toc，故对每个按钮分别绑定
function initTocToggle() {
    document.querySelectorAll('[data-toc-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const tree = btn.closest('[data-toc-tree]');
            if (tree) {
                tree.classList.toggle('toc-expanded');
            }
        });
    });
}

function injectCommentaryMarkers() {
    let counter = 0;

    document
        .querySelectorAll('div.sentence[data-note-id]')
        .forEach((sentenceEl) => {
            const uid = sentenceEl.dataset.noteId;
            counter++;
            const id = `commentary-${counter}`;

            // checkbox：控制展开，紧跟在 sentence 后
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'commentary-toggle';
            checkbox.id = id;

            // label（icon）：行内，插在句子文字末尾
            const label = document.createElement('label');
            label.htmlFor = id;
            label.className = 'commentary-icon';
            label.innerHTML = '<i class="ti ti-message-circle"></i>';

            // 注释块：紧跟在 checkbox 后（CSS 相邻选择器依赖此顺序）
            const note = document.createElement('div');
            note.className = 'commentary-note';
            note.dataset.uuid = uid;
            note.dataset.loaded = 'false';

            // label 插入句子内文字末尾（行内不打断文字流）
            const innerSpan = sentenceEl.querySelector(':scope > span');
            (innerSpan ?? sentenceEl).appendChild(label);

            // sentence 后：先插 note，再插 checkbox（after 逆序）
            // 最终顺序：div.sentence → input.commentary-toggle → div.commentary-note
            sentenceEl.after(note);
            sentenceEl.after(checkbox);

            // 点击时懒加载
            checkbox.addEventListener('change', async () => {
                if (!checkbox.checked) {
                    return;
                }

                if (note.dataset.loaded === 'true') {
                    return;
                }

                note.innerHTML =
                    '<span class="text-muted small">加载中…</span>';
                await fetchCommentary(note);
            });
        });
}

// 译文质量评估标注：把 <span class="evaluate evaluate-级别" title="…"> 就地
// 升级为 Tufte 旁注。保留原 span（含背景色高亮）不动，在其后注入
//   span.evaluate → label.margin-toggle → input.margin-toggle → span.marginnote
// 复用既有旁注样式：桌面端 marginnote 自动浮到右栏，手机端点击 label 展开。
// 用 marginnote（而非 sidenote）以避免 ::before 计数器的 [N] 编号。
function injectEvaluateMarkers() {
    let counter = 0;

    document
        .querySelectorAll('article.reader-body span.evaluate[title]')
        .forEach((evalEl) => {
            const info = evalEl.getAttribute('title').trim();
            if (info === '') {
                return;
            }

            counter++;
            const id = `evaluate-${counter}`;

            // label：手机端点击触发（桌面隐藏，marginnote 自动浮动）
            const label = document.createElement('label');
            label.htmlFor = id;
            label.className = 'margin-toggle';
            label.innerHTML = '<i class="ti ti-alert-triangle"></i>';

            // checkbox：控制手机端展开
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'margin-toggle';
            checkbox.id = id;

            // 旁注内容：取自 title，｜ 分隔的「问题简述／建议」分行显示
            const note = document.createElement('span');
            note.className = 'marginnote evaluate-note';
            info.split('｜').forEach((part, index) => {
                if (index > 0) {
                    note.appendChild(document.createElement('br'));
                }
                note.appendChild(document.createTextNode(part.trim()));
            });

            // 顺序：span.evaluate → label → input → span.marginnote
            // （input 与 marginnote 相邻，CSS :checked + .marginnote 依赖此顺序）
            evalEl.after(label, checkbox, note);
        });
}

async function fetchCommentary(noteEl) {
    const uuid = noteEl.dataset.uuid;

    try {
        const res = await fetch(`/api/v2/sentence/${uuid}?format=html`);

        if (!res.ok) {
            throw new Error(res.status);
        }

        const json = await res.json();

        if (!json.ok) {
            throw new Error('api error');
        }

        noteEl.innerHTML = json.data.html;
        noteEl.dataset.loaded = 'true';
    } catch {
        noteEl.innerHTML = '<span class="text-muted small">加载失败</span>';
    }
}
