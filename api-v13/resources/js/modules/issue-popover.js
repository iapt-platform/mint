// resources/js/modules/issue-popover.js
//
// 阅读页 issue 标注的点击查看。正文里的标注由后端注入
// （PaliContentService::injectAnnotationNotes → AnnotationLayout）：
//   span.anno-issue       有位置的 issue，包住被指出的原文（交叠的已合并成一个 span）
//   span.anno-issue-mark  没有位置的 issue，句尾的三角叹号图标
// 两者都带 data-discussion-ids="id1,id2"。点击后逐条取 GET /api/v3/discussions/{id}
// （issue 对匿名公开），桌面端用 Bootstrap Popover，手机端用 #annoIssueDrawer 抽屉。

import * as bootstrap from 'bootstrap';

const isMobile = () => window.innerWidth < 768;
const cache = new Map();

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';

    return div.innerHTML;
}

async function fetchIssue(id) {
    if (!cache.has(id)) {
        cache.set(
            id,
            fetch(`/api/v3/discussions/${encodeURIComponent(id)}`, {
                headers: { Accept: 'application/json' },
            }).then(async (res) => {
                if (res.status === 404) {
                    return null;
                }

                if (!res.ok) {
                    throw new Error(String(res.status));
                }

                return (await res.json()).data;
            }),
        );
    }

    try {
        return await cache.get(id);
    } catch (e) {
        cache.delete(id); // 失败的不缓存，下次点击重试

        throw e;
    }
}

function renderIssue(issue, i18n) {
    if (!issue) {
        return `<div class="anno-issue-item text-muted">${escapeHtml(i18n.notFound)}</div>`;
    }

    const severity = issue.severity ?? '';
    const severityLabel = i18n.severity?.[severity] ?? '';
    const editor = issue.editor?.nickName ?? '';
    const date = issue.created_at
        ? new Date(issue.created_at).toLocaleDateString()
        : '';
    const replies = issue.reply_count
        ? i18n.replies.replace(':count', issue.reply_count)
        : '';
    // summary 是后端渲染好的纯文本（模板已展开），比原始 markdown 更适合直接显示
    const body = (issue.summary ?? issue.content ?? '').trim();
    const meta = [severityLabel, editor, date, replies]
        .filter(Boolean)
        .join(' · ');

    return `
        <div class="anno-issue-item anno-issue-${escapeHtml(severity || 'none')}">
            <div class="anno-issue-item-head">
                <i class="ti ti-alert-triangle"></i>
                <span>${escapeHtml(issue.title ?? '')}</span>
            </div>
            ${body ? `<div class="anno-issue-item-body">${escapeHtml(body)}</div>` : ''}
            <div class="anno-issue-item-meta">${escapeHtml(meta)}</div>
        </div>`;
}

async function renderList(ids, i18n) {
    const results = await Promise.allSettled(ids.map(fetchIssue));

    return results
        .map((r) =>
            r.status === 'fulfilled'
                ? renderIssue(r.value, i18n)
                : `<div class="anno-issue-item text-danger">${escapeHtml(i18n.failed)}</div>`,
        )
        .join('');
}

export function initIssueMarkers() {
    const article = document.querySelector('article.reader-body');
    const drawerEl = document.getElementById('annoIssueDrawer');

    if (!article || !article.querySelector('.anno-issue, .anno-issue-mark')) {
        return;
    }

    let i18n = {
        loading: '…',
        failed: '!',
        notFound: '',
        replies: ':count',
        severity: {},
    };

    try {
        i18n = { ...i18n, ...JSON.parse(drawerEl?.dataset.i18n ?? '{}') };
    } catch {
        // 文案解析失败就用兜底文案
    }

    const loadingHtml = `<div class="text-muted small">${escapeHtml(i18n.loading)}</div>`;

    let activeEl = null;
    let activePopover = null;
    const deactivate = () => {
        activePopover?.dispose();
        activePopover = null;
        activeEl?.classList.remove('is-active');
        activeEl = null;
    };

    const open = async (el) => {
        const ids = (el.dataset.discussionIds ?? '').split(',').filter(Boolean);

        if (ids.length === 0) {
            return;
        }

        const same = activeEl === el;
        deactivate();

        if (same && !isMobile()) {
            return; // 再点一次同一个标注：收起
        }

        activeEl = el;
        el.classList.add('is-active');

        if (isMobile() && drawerEl) {
            const body = drawerEl.querySelector('.offcanvas-body');
            body.innerHTML = loadingHtml;
            const drawer = bootstrap.Offcanvas.getOrCreateInstance(drawerEl);
            drawerEl.addEventListener('hidden.bs.offcanvas', deactivate, {
                once: true,
            });
            drawer.show();
            body.innerHTML = await renderList(ids, i18n);

            return;
        }

        const popover = new bootstrap.Popover(el, {
            trigger: 'manual',
            html: true,
            sanitize: false, // 内容全部经过 escapeHtml
            placement: 'bottom',
            fallbackPlacements: ['top', 'bottom'],
            customClass: 'anno-issue-popover',
            title: escapeHtml(i18n.title),
            content: `<div class="anno-issue-list">${loadingHtml}</div>`,
        });
        activePopover = popover;
        popover.show();
        const html = await renderList(ids, i18n);

        if (activePopover !== popover) {
            return; // 等待期间已经切到别的标注
        }

        // 不用 setContent()：它在 manual 触发下会重走一遍 show()，完成回调里因 _isHovered === false
        // 直接把 popover 收起（Bootstrap 5.3）。与 term-tooltip.js 一样直接改 body，再重新定位。
        const body = `<div class="anno-issue-list">${html}</div>`;
        popover._config.content = body;
        const tip = document.getElementById(
            el.getAttribute('aria-describedby') ?? '',
        );
        tip?.querySelector('.popover-body')?.replaceChildren(
            document.createRange().createContextualFragment(body),
        );
        popover.update();
    };

    article.addEventListener('click', (e) => {
        // 标注里可能嵌着边注（label / input / .sidenote）与术语，它们有自己的点击行为
        if (
            e.target.closest(
                'label, input, .sidenote, .marginnote, .term-ref, a',
            )
        ) {
            return;
        }

        const el = e.target.closest('.anno-issue, .anno-issue-mark');

        if (!el) {
            return;
        }

        e.preventDefault();
        open(el);
    });

    article.addEventListener('keydown', (e) => {
        const el = e.target.closest?.('.anno-issue-mark');

        if (el && (e.key === 'Enter' || e.key === ' ')) {
            e.preventDefault();
            open(el);
        }
    });

    // 点标注与弹层以外的地方收起 popover
    document.addEventListener('click', (e) => {
        if (
            activePopover &&
            !e.target.closest(
                '.anno-issue, .anno-issue-mark, .anno-issue-popover',
            )
        ) {
            deactivate();
        }
    });
}
