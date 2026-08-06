"""坐标与引用。

WikiPali 的最小可引用单位是 (book, paragraph)，句子再细分到 word_start/word_end。
一切给用户看的内容都必须带得回坐标——这是研究型产出可信度的地基。

坐标的书写形式统一为 `book:paragraph`，例如 `216:35`。
"""

import re

from errors import WpError

COORD_RE = re.compile(r'^\s*(\d+)\s*[:\-_]\s*(\d+)\s*$')


def parse_coord(text):
    """把 '216:35' 解析成 (216, 35)。也接受 216-35 / 216_35。"""
    m = COORD_RE.match(str(text))
    if not m:
        raise WpError(f"坐标格式不对：{text}（应为 book:paragraph，如 216:35）")
    return int(m.group(1)), int(m.group(2))


def parse_coords(items):
    """解析一串坐标，按 book 分组，返回 {book: [paragraph, ...]}（去重、保序）。"""
    grouped = {}
    for item in items:
        for part in str(item).split(','):
            if not part.strip():
                continue
            book, para = parse_coord(part)
            paras = grouped.setdefault(book, [])
            if para not in paras:
                paras.append(para)
    return grouped


def fmt_coord(book, paragraph):
    return f"{book}:{paragraph}"


def fmt_path(path, sep=' › ', max_items=4):
    """把检索结果的 path 数组压成一行章节路径。"""
    if not path:
        return ''
    titles = [p.get('title', '') for p in path if isinstance(p, dict) and p.get('title')]
    if len(titles) > max_items:
        titles = [titles[0], '…'] + titles[-(max_items - 2):]
    return sep.join(titles)


def text_layer(tags):
    """按 tags 判断文献层次：本文 / 义注 / 复注。引用时必须标明，混用是学术错误。"""
    names = {t.get('name') for t in (tags or []) if isinstance(t, dict)}
    if 'ṭīkā' in names:
        return 'ṭīkā'
    if 'aṭṭhakathā' in names:
        return 'aṭṭhakathā'
    if 'mūla' in names:
        return 'mūla'
    return ''
