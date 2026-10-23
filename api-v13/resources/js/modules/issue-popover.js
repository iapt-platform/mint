// resources/js/modules/issue-popover.js
//
// 阅读页 issue 与译文评估标注的点击查看。正文里的标注由后端注入
// （PaliContentService::injectAnnotationNotes → AnnotationLayout）：
//   span.anno-issue            有位置的 issue，包住被指出的原文（交叠的已合并成一个 span）
//   span.anno-issue-mark       没有位置的 issue，句尾的三角叹号图标
//   span.anno-assessment-mark  译文评估，句尾的对勾图标，颜色按分数档位（high / medium / low）
// 都带 data-discussion-ids="id1,id2"。点击后逐条取 GET /api/v3/discussions/{id}
// （issue 与评估对匿名公开），桌面端用 Bootstrap Popover，手机端用 #annoIssueDrawer 抽屉。

import * as bootstrap from 'bootstrap';

const isMobile = () => window.innerWidth < 768;
const MARKERS = '.anno-issue, .anno-issue-mark, .anno-assessment-mark';
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

// level 取自标注的 data-level（后端 AnnotationLayout::assessmentLevel 定档），前端不重复分档阈值
function renderAssessment(assessment, i18n, level) {
    const score = Number(assessment.score ?? 0).toString();
    const editor = assessment.editor?.nickName ?? '';
    const coEditor = assessment.co_editor?.nickName ?? '';
    const date = assessment.updated_at
        ? new Date(assessment.updated_at).toLocaleDateString()
        : '';
    const replies = assessment.reply_count
        ? i18n.replies.replace(':count', assessment.reply_count)
        : '';
    const body = (assessment.summary ?? assessment.content ?? '').trim();
    const meta = [
        i18n.method.replace(':method', assessment.severity ?? ''),
        coEditor ? `${editor} + ${coEditor}` : editor,
        date,
        replies,
    ]
        .filter(Boolean)
        .join(' · ');

    return `
        <div class="anno-issue-item anno-assessment-item anno-assessment-${level}">
            <div class="anno-issue-item-head">
                <i class="ti ti-circle-check"></i>
                <span class="anno-assessment-score">${escapeHtml(score)}</span>
                <span>${escapeHtml(assessment.title ?? '')}</span>
            </div>
            ${body ? `<div class="anno-issue-item-body">${escapeHtml(body)}</div>` : ''}
            <div class="anno-issue-item-meta">${escapeHtml(meta)}</div>
        </div>`;
}

function renderIssue(issue, i18n, level) {
    if (!issue) {
        return `<div class="anno-issue-item text-muted">${escapeHtml(i18n.notFound)}</div>`;
    }

    if (issue.type === 'assessment') {
        return renderAssessment(issue, i18n, level ?? 'none');
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

async function renderList(ids, i18n, level) {
    const results = await Promise.allSettled(ids.map(fetchIssue));

    return results
        .map((r) =>
            r.status === 'fulfilled'
                ? renderIssue(r.value, i18n, level)
                : `<div class="anno-issue-item text-danger">${escapeHtml(i18n.failed)}</div>`,
        )
        .join('');
}

export function initIssueMarkers() {
    const article = document.querySelector('article.reader-body');
    const drawerEl = document.getElementById('annoIssueDrawer');

    if (!article || !article.querySelector(MARKERS)) {
        return;
    }

    let i18n = {
        loading: '…',
        failed: '!',
        notFound: '',
        replies: ':count',
        severity: {},
        assessmentTitle: '',
        method: ':method',
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
        const title = el.classList.contains('anno-assessment-mark')
            ? i18n.assessmentTitle
            : i18n.title;

        if (isMobile() && drawerEl) {
            const heading = drawerEl.querySelector('.offcanvas-title');

            if (heading) {
                heading.textContent = title;
            }

            const body = drawerEl.querySelector('.offcanvas-body');
            body.innerHTML = loadingHtml;
            const drawer = bootstrap.Offcanvas.getOrCreateInstance(drawerEl);
            drawerEl.addEventListener('hidden.bs.offcanvas', deactivate, {
                once: true,
            });
            drawer.show();
            body.innerHTML = await renderList(ids, i18n, el.dataset.level);

            return;
        }

        const popover = new bootstrap.Popover(el, {
            trigger: 'manual',
            html: true,
            sanitize: false, // 内容全部经过 escapeHtml
            placement: 'bottom',
            fallbackPlacements: ['top', 'bottom'],
            customClass: 'anno-issue-popover',
            title: escapeHtml(title),
            content: `<div class="anno-issue-list">${loadingHtml}</div>`,
        });
        activePopover = popover;
        popover.show();
        const html = await renderList(ids, i18n, el.dataset.level);

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

        const el = e.target.closest(MARKERS);

        if (!el) {
            return;
        }

        e.preventDefault();
        open(el);
    });

    article.addEventListener('keydown', (e) => {
        const el = e.target.closest?.(
            '.anno-issue-mark, .anno-assessment-mark',
        );

        if (el && (e.key === 'Enter' || e.key === ' ')) {
            e.preventDefault();
            open(el);
        }
    });

    // 点标注与弹层以外的地方收起 popover
    document.addEventListener('click', (e) => {
        if (
            activePopover &&
            !e.target.closest(`${MARKERS}, .anno-issue-popover`)
        ) {
            deactivate();
        }
    });
}
