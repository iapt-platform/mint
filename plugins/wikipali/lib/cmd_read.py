"""检索与阅读的子命令：forms / word / search / dist / get。

全部只读，不需要凭据。
"""

import html as html_mod
import json
import re
import sys

from client import make_client, note
from coords import fmt_coord, fmt_path, parse_coords, text_layer
from errors import ApiError, WpError, explain_api_error

# 巴利原文本身就是一个 channel（_System_Pali_VRI_）。取原文、取译文、取逐词解析
# 是同一个调用换 channel。
PALI_CHANNEL = '00b577c0-13b9-11ee-a05a-b7307efd9ee6'

# 服务端的 sentence?view=paragraph 不带 channels 会 500，所以永远要给一个默认值。
READ_TIMEOUT = 60


def strip_markup(raw, hl='【】', bold='**'):
    """把服务端返回的 HTML 压成纯文本，保留命中高亮与黑体两种信息。

    命中词用 <span class='hl'> 包，黑体是原文的 <span class="bld">——后者是注释书
    标出词条的地方，对判断「这段是不是定义」很关键，不能丢。
    """
    if not raw:
        return ''
    text = raw
    text = re.sub(r"<span class='hl'>(.*?)</span>", hl[0] + r'\1' + hl[1], text, flags=re.S)
    text = re.sub(r'<span class="bld">(.*?)</span>', bold + r'\1' + bold, text, flags=re.S)
    text = re.sub(r"<MdTpl[^>]*></MdTpl>", '', text)
    text = re.sub(r'<[^>]+>', '', text)
    text = html_mod.unescape(text)
    return re.sub(r'\s+', ' ', text).strip()


def snippet(text, width, around=None):
    """截断文本；给了 around 就尽量把它所在的位置露出来。"""
    if len(text) <= width:
        return text
    if around:
        pos = text.find(around)
        if pos > width // 2:
            start = pos - width // 3
            return '…' + text[start:start + width] + '…'
    return text[:width] + '…'


def emit(args, payload, render):
    if getattr(args, 'json', False):
        print(json.dumps(payload, ensure_ascii=False, indent=2))
    else:
        render()


# ---------------------------------------------------------------------------
# forms —— 词形展开，一切检索的前置
# ---------------------------------------------------------------------------


def fetch_forms(client, word):
    try:
        data = client.call('GET', f'v2/case/{word}', timeout=READ_TIMEOUT)
    except ApiError as exc:
        raise explain_api_error(exc, f'展开词形 {word}')
    return (data or {}).get('rows') or []


def cmd_forms(args):
    client = make_client(args)
    rows = fetch_forms(client, args.word)
    if not rows:
        raise WpError(
            f'「{args.word}」在语料里找不到任何词形。检查拼写（变音符号是否正确），'
            '或换一个可能的词根再试。'
        )

    def render():
        for idx, row in enumerate(rows[: args.limit], 1):
            forms = row.get('case') or []
            total = sum(int(f.get('count') or 0) for f in forms)
            bold = sum(int(f.get('bold') or 0) for f in forms)
            mark = '  ← 可能性最高' if idx == 1 else ''
            print(f'[{idx}] {row.get("word")}  {len(forms)} 形 / 共 {total} 次（黑体 {bold}）{mark}')
            for f in sorted(forms, key=lambda x: -int(x.get('count') or 0)):
                print(f'      {f.get("word"):<20} {f.get("count"):>5} 次   黑体 {f.get("bold")}')
        print()
        print('检索用（第一候选的全部词形）：')
        print('  ' + forms_arg(rows[0]))
        if len(rows) > 1:
            print('注意：还有其他候选词根。若目标概念同时有名词与动词两条线，两条都要展开。')

    emit(args, rows, render)
    return 0


def forms_arg(row):
    """把一个候选的全部词形拼成 search 要的逗号串。"""
    return ','.join(f.get('word') for f in (row.get('case') or []) if f.get('word'))


# ---------------------------------------------------------------------------
# word —— 词典释义与形态分析，用来确认选对了词根
# ---------------------------------------------------------------------------


def cmd_word(args):
    client = make_client(args)
    try:
        data = client.call('GET', 'v2/dict', query={'word': args.word, 'lang': args.lang},
                           timeout=READ_TIMEOUT)
    except ApiError as exc:
        raise explain_api_error(exc, f'查词典 {args.word}')
    groups = (data or {}).get('words') or []
    if not groups:
        raise WpError(f'词典里没有「{args.word}」。')

    def render():
        for grp in groups:
            for w in (grp.get('words') or [])[: args.limit]:
                print(f'■ {w.get("word")}')
                for g in (w.get('grammar') or [])[:6]:
                    print(f'    ← {g.get("parent")}  {g.get("type")} {g.get("grammar")}'
                          f'  ({g.get("factors")})')
                for d in (w.get('dict') or [])[: args.dicts]:
                    # 释义在 note；description 是词典本身的介绍，不是词条内容
                    meaning = strip_markup(d.get('note') or '')
                    if not meaning:
                        continue
                    print(f'    〔{d.get("shortname")}·{d.get("lang")}〕{snippet(meaning, 220)}')
                print()

    emit(args, groups, render)
    return 0


# ---------------------------------------------------------------------------
# search —— 按词形检索段落
# ---------------------------------------------------------------------------


def resolve_key(client, args):
    """确定检索用的词形串。--lemma 会先跑一次 forms，并把展开结果打出来。"""
    if args.lemma:
        rows = fetch_forms(client, args.lemma)
        if not rows:
            raise WpError(f'「{args.lemma}」展不出任何词形。')
        key = forms_arg(rows[0])
        note(f'⚠ 已把词根「{args.lemma}」展开为 {len(key.split(","))} 个词形：{key}')
        return key
    key = ','.join(part.strip() for item in args.forms for part in item.split(',') if part.strip())
    if not key:
        raise WpError('没有给出词形。用 --lemma <词根> 自动展开，或直接给逗号分隔的词形。')
    return key


def cmd_search(args):
    client = make_client(args)
    key = resolve_key(client, args)
    query = {'key': key, 'limit': args.limit, 'offset': args.offset}
    if args.bold:
        query['bold'] = 'on'
    if args.book:
        query['book'] = args.book
    if args.tags:
        query['tags'] = args.tags
    try:
        data = client.call('GET', 'v2/search-pali-wbw', query=query, timeout=READ_TIMEOUT)
    except ApiError as exc:
        raise explain_api_error(exc, '检索')
    rows = (data or {}).get('rows') or []
    total = (data or {}).get('count', 0)

    def render():
        scope = []
        if args.bold:
            scope.append('仅黑体')
        if args.book:
            scope.append(f'book={args.book}')
        if args.tags:
            scope.append(f'tags={args.tags}')
        print(f'命中 {total} 段，本页 {len(rows)}（offset {args.offset}）'
              + (f'  [{" ".join(scope)}]' if scope else ''))
        if not rows:
            print('\n0 条。依次怀疑：词形没展开（用 --lemma）→ 词根选错 → 范围限太窄。')
            return
        print()
        for idx, r in enumerate(rows, 1 + args.offset):
            coord = fmt_coord(r.get('book'), r.get('paragraph'))
            print(f'[{idx}] {coord}  {fmt_path(r.get("path"))}   rank {r.get("rank")}')
            print(f'     {snippet(strip_markup(r.get("highlight")), args.width, "【")}')
        print(f'\n引用时用坐标 book:paragraph，取原文用：wikipali get {rows[0].get("book")}:'
              f'{rows[0].get("paragraph")}')

    emit(args, {'count': total, 'rows': rows}, render)
    return 0


# ---------------------------------------------------------------------------
# dist —— 出处分布
# ---------------------------------------------------------------------------


def cmd_dist(args):
    client = make_client(args)
    key = resolve_key(client, args)
    query = {'key': key}
    if args.tags:
        query['tags'] = args.tags
    try:
        data = client.call('GET', 'v2/search-pali-wbw-books', query=query, timeout=READ_TIMEOUT)
    except ApiError as exc:
        raise explain_api_error(exc, '统计出处分布')
    rows = (data or {}).get('rows') or []

    def render():
        total = sum(int(r.get('count') or 0) for r in rows)
        print(f'{len(rows)} 部书，共 {total} 次词命中\n'
              '（注意：这里数的是词次，不是段落数。段落数用 search 的 count，'
              '两者不相等——同一段里出现多次只算一段。）\n')
        by_layer = {}
        for r in sorted(rows, key=lambda x: -int(x.get('count') or 0))[: args.limit]:
            layer = text_layer(r.get('tags'))
            by_layer[layer] = by_layer.get(layer, 0) + int(r.get('count') or 0)
            tags = ' '.join(t.get('name') for t in (r.get('tags') or []) if t.get('name'))
            print(f'{r.get("count"):>5}  {str(r.get("paliTitle"))[:38]:<40} '
                  f'--book {r.get("pcdBookId")}   [{tags}]')
        print('\n按文献层次：', end='')
        for layer in ('mūla', 'aṭṭhakathā', 'ṭīkā', ''):
            if layer in by_layer:
                print(f'  {layer or "未标层次"} {by_layer[layer]}', end='')
        print('\n引用时必须标明层次——把义注的解释当成本文的说法是学术错误。')

    emit(args, {'rows': rows}, render)
    return 0


# ---------------------------------------------------------------------------
# get —— 按坐标取原文/译文
# ---------------------------------------------------------------------------


def cmd_get(args):
    client = make_client(args)
    grouped = parse_coords(args.coords)
    channels = ','.join(args.channel) if args.channel else PALI_CHANNEL

    collected = []
    for book, paras in grouped.items():
        # 服务端不带 channels 会 500，所以 channels 永远要给
        query = {'view': 'paragraph', 'book': book, 'para': ','.join(str(p) for p in paras),
                 'channels': channels, 'limit': args.limit}
        try:
            data = client.call('GET', 'v2/sentence', query=query, timeout=READ_TIMEOUT)
        except ApiError as exc:
            raise explain_api_error(exc, f'取 {book} 的段落')
        collected.extend((data or {}).get('rows') or [])

    def render():
        if not collected:
            print('这些坐标在指定 channel 下没有内容。')
            print('注意：这是「该 channel 在此处没有文本」，不是「查询失败」——'
                  '如实报告，不要拿相邻段落或别的译本凑。')
            return
        current = None
        for r in collected:
            ch = (r.get('channel') or {})
            head = (r.get('book'), r.get('paragraph'), ch.get('uid'))
            if head != current:
                current = head
                editor = (r.get('editor') or {})
                who = editor.get('nickName') or editor.get('name') or ''
                print(f'\n=== {fmt_coord(r.get("book"), r.get("paragraph"))}  '
                      f'{ch.get("name")}（{ch.get("lang")}）'
                      + (f'  作者：{who}' if who else '') + ' ===')
            text = strip_markup(r.get('content'))
            print(f'  [{r.get("word_start")}-{r.get("word_end")}] {text}')
        print(f'\n共 {len(collected)} 句。')

    emit(args, collected, render)
    return 0
