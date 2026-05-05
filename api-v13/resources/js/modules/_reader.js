// resources/js/modules/_reader.js

export function initReader() {
    injectCommentaryMarkers();
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
