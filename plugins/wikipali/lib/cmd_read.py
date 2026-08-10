"""检索与阅读的子命令：forms / word / search / dist / get。

全部只读，不需要凭据。
"""

import html as html_mod
import json
import re
import sys

from client import make_client, note
from coords import fmt_coord, fmt_path, parse_coord, parse_coords, text_layer
from errors import ApiError, WpError, explain_api_error

# 巴利原文本身就是一个 channel（_System_Pali_VRI_）。取原文、取译文、取逐词解析
# 是同一个调用换 channel。
PALI_CHANNEL = '00b577c0-13b9-11ee-a05a-b7307efd9ee6'

# 靠 channel 名字判断机器译文很脆弱：库里既有名字含 "AI" 的，也有直接用模型名命名的
# （deepseek / qwen-max / grok-简体中文 / gemini / 豆包 / ChatGPT），后者不含 "ai"。
# 这个清单只用来「提醒去核实」，不作为判定依据——权威判定看 get 返回的作者是不是模型。
MACHINE_HINTS = ('ai', 'gpt', 'chatgpt', 'claude', 'deepseek', 'gemini', 'qwen', 'grok',
                 'llama', 'mistral', 'kimi', 'norbu', '豆包', '文心', 'ernie', '通义')

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
    # <code>M1.1</code> 是版本页码（M=缅甸版 V=VRI P=PTS T=泰版），标的是页在正文里
    # 的起始位置，与段落不是一一对应，所以必须留在原位。直接去标签会让它粘到前一个
    # 词上（Evaṃ M1.1 → EvaṃM1.1），看着像词形的一部分，加方括号隔开。
    text = re.sub(r'<code>([^<]*)</code>', r'[\1]', text)
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
    """展开词形。**0 个词形的候选等同于没找到**——服务端对查无此词会返回一个
    case 为空的行，若把它当成命中，后续 search 会拿到空 key 而返回整个语料库。"""
    try:
        data = client.call('GET', f'v2/case/{word}', timeout=READ_TIMEOUT)
    except ApiError as exc:
        raise explain_api_error(exc, f'展开词形 {word}')
    rows = (data or {}).get('rows') or []
    return [r for r in rows if r.get('case')]


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
        if not key:
            raise WpError(f'「{args.lemma}」展不出任何词形，无法检索。')
        note(f'⚠ 已把词根「{args.lemma}」展开为 {len(key.split(","))} 个词形：{key}')
        return key
    key = ','.join(part.strip() for item in args.forms for part in item.split(',') if part.strip())
    if not key:
        # 空 key 会被服务端当成「不限」，返回整个语料库——决不能发出去
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


# ---------------------------------------------------------------------------
# toc —— 章节目录
# ---------------------------------------------------------------------------


def cmd_toc(args):
    client = make_client(args)
    book, para = parse_coord(args.coord)
    try:
        data = client.call('GET', 'v2/palitext', query={'view': 'book-toc', 'book': book, 'para': para},
                           timeout=READ_TIMEOUT)
    except ApiError as exc:
        raise explain_api_error(exc, f'取 {book}:{para} 的章节目录')
    rows = (data or {}).get('rows') or []
    # 服务端返回的是整套丛书的目录，默认只留当前这本，避免刷屏
    shown = rows if args.all else [r for r in rows if r.get('book') == book]

    def render():
        print(f'{len(rows)} 条目录条目'
              + ('' if args.all else f'，其中 book {book} 有 {len(shown)} 条（--all 看整套丛书）'))
        for r in shown:
            level = int(r.get('level') or 1)
            if level > args.depth:
                continue
            print(f'{"  " * (level - 1)}{r.get("book")}:{r.get("paragraph")}  {r.get("toc")}')

    emit(args, shown, render)
    return 0


# ---------------------------------------------------------------------------
# chapter —— 先报体量，再取整章
# ---------------------------------------------------------------------------


def fetch_meta(client, book, para):
    try:
        return client.call('GET', f'v2/palitext/{book}-{para}', timeout=READ_TIMEOUT)
    except ApiError as exc:
        raise explain_api_error(exc, f'取 {book}:{para} 的段落元信息')


def parse_path(raw):
    """这个端点的 path 是 JSON 字符串，search 那边却是数组——两边都要能吃。"""
    if isinstance(raw, str):
        try:
            return json.loads(raw)
        except ValueError:
            return []
    return raw or []


def resolve_chapter(client, book, para):
    """给任意段号，向上找到它所属的章节节点。返回 (章节 meta, 走了几层)。

    注意：正文段自己也带 chapter_len（值为 1），所以不能用「有没有这个字段」判断，
    要看它是不是 > 1。向上一层优先取 path 的末项（那就是直接所属的章节），
    没有 path 才退回 parent。
    """
    meta = fetch_meta(client, book, para)
    hops = 0
    while meta and int(meta.get('chapter_len') or 0) <= 1 and hops < 6:
        up = None
        path = parse_path(meta.get('path'))
        if path:
            last = path[-1]
            if int(last.get('paragraph', -1)) != int(meta.get('paragraph', -1)):
                up = int(last['paragraph'])
        if up is None and meta.get('parent'):
            up = int(meta['parent'])
        if up is None:
            break
        meta = fetch_meta(client, book, up)
        hops += 1
    return meta, hops


def cmd_chapter(args):
    client = make_client(args)
    book, para = parse_coord(args.coord)
    meta, hops = resolve_chapter(client, book, para)
    if not meta or not meta.get('chapter_len'):
        raise WpError(f'{book}:{para} 向上找不到章节节点，无法确定章节范围。')

    start = int(meta['paragraph'])
    length = int(meta['chapter_len'])
    strlen = int(meta.get('chapter_strlen') or 0)
    end = start + length - 1
    path = parse_path(meta.get('path'))
    title = meta.get('toc') or meta.get('title') or (path[-1].get('title') if path else '')

    print(f'章节   : {title}')
    print(f'路径   : {fmt_path(path)}')
    print(f'范围   : {book}:{start} – {book}:{end}（{length} 段）'
          + (f'，约 {strlen} 字符' if strlen else ''))
    if hops:
        print(f'（{book}:{para} 是正文段，向上 {hops} 层找到所属章节）')
    if meta.get('prev_chapter') or meta.get('next_chapter'):
        print(f'相邻   : 上一章 {book}:{meta.get("prev_chapter")}  下一章 {book}:{meta.get("next_chapter")}')

    if not args.fetch:
        print(f'\n只报体量，未取文。确认要读再加 --fetch；只要其中几段用：'
              f'wikipali get {book}:{start} {book}:{start + 1} …')
        return 0

    if strlen > args.warn_at:
        note(f'⚠ 本章约 {strlen} 字符，超过 {args.warn_at} 的提示阈值——注意上下文预算。')

    if args.via == 'chapter-content':
        return fetch_chapter_content(client, book, start, args)
    return fetch_tipitaka_content(client, book, start, args)


# tipitaka-content 返回的是一整串 HTML，每句包在 data-sid 里；sid 就是
# book-para-wordStart-wordEnd，段落号从 sid 里就能取，不必解析外层的 data-para。
SENTENCE_RE = re.compile(r"data-sid='([^']+)'\s*>(.*?)</div>", re.S)


def fetch_tipitaka_content(client, book, para, args):
    """整章取文：走 tipitaka-content（OpenSearch 预建文档）。

    与 chapter-content 的区别：一次只取**一个** channel（参数是单数 channel），
    返回已渲染好的 HTML 串而不是嵌套 JSON。没有该 channel 的预建文档时服务端
    返回 400 且 message 里带 OpenSearch 的 found=false——那是「这一章没有该版本」，
    不是服务故障，必须区分开。
    """
    query = {}
    if args.channel:
        if len(args.channel) > 1:
            note('⚠ tipitaka-content 一次只接受一个 channel，已取第一个；'
                 '要对读多个版本请分别调用。')
        query['channel'] = args.channel[0]
    try:
        display = client.call('GET', f'v2/tipitaka-content/{book}-{para}', query=query,
                              timeout=READ_TIMEOUT)
    except ApiError as exc:
        if 'found' in str(exc) and 'false' in str(exc):
            raise WpError(
                f'{book}:{para} 这一章没有该版本的预建内容。\n'
                '这是「该 channel 在本章无文本」，不是服务故障——如实报告，'
                '不要拿别的版本顶替。\n'
                f'用 wikipali versions {book}:{para} 看这一段实际有哪些版本。'
            )
        raise explain_api_error(exc, f'取 {book}:{para} 的整章内容')

    if not isinstance(display, str):
        raise WpError('整章内容的返回不是字符串，服务端返回形状可能变了。')

    grouped = {}
    order = []
    for sid, body in SENTENCE_RE.findall(display):
        text = strip_markup(body) if args.text else re.sub(r'\s+', ' ', body).strip()
        if not text:
            continue
        try:
            para_no = int(sid.split('-')[1])
        except (IndexError, ValueError):
            continue
        if para_no not in grouped:
            grouped[para_no] = []
            order.append(para_no)
        grouped[para_no].append({'id': sid, 'text': text})
    out = [{'para': n, 'sentences': grouped[n]} for n in order]

    def render():
        total = sum(len(x['sentences']) for x in out)
        src = f'channel {args.channel[0]}' if args.channel else '巴利原文'
        if not total:
            print(f'\n该版本在本章**没有句子内容**（服务端返回了文档但其中没有句子）。')
            print(f'用 wikipali versions {book}:{para} 看这一段实际有哪些版本。')
            return
        print(f'\n{len(out)} 段 / {total} 句（{src}）')
        for item in out:
            print(f'\n## {book}:{item["para"]}')
            for sent in item['sentences']:
                print(f'  {sent["id"]}  {sent["text"]}')
        print('\n句子 id 就是引用坐标（book-para-wordStart-wordEnd）。')

    emit(args, out, render)
    return 0


def fetch_chapter_content(client, book, para, args):
    """整章取文：走 chapter-content，一次拿回全章并按句对齐。

    服务端返回的结构极厚（每句都带 channel / studio / editor / 各类计数，
    24–36 KB），直接丢给模型是浪费。这里只留每句的 id 与 html——id 本身就是
    可引用的坐标（book-para-wordStart-wordEnd），html 保留了 <strong> 黑体，
    那是判断「这句是不是词条解释」的依据，不能丢。
    """
    query = {}
    if args.channel:
        query['channels'] = ','.join(args.channel)
    try:
        data = client.call('GET', f'v2/chapter-content/{book}-{para}', query=query,
                           timeout=READ_TIMEOUT)
    except ApiError as exc:
        raise explain_api_error(exc, f'取 {book}:{para} 的整章内容')

    raw = (data or {}).get('content') or '[]'
    try:
        paragraphs = json.loads(raw) if isinstance(raw, str) else raw
    except ValueError:
        raise WpError('整章内容不是合法 JSON，服务端返回形状可能变了。')

    wanted = set(args.channel or [])
    out = []
    placeholders = 0
    for item in paragraphs:
        sentences = []
        for child in item.get('children') or []:
            body = pick_body(child, wanted)
            if body is None:
                continue
            if not body.strip():
                # 请求的 channel 在这一句没有内容时，服务端仍返回等量的空占位条目。
                # 照直输出会让人以为「有译文只是没显示」，必须滤掉并单独报数。
                placeholders += 1
                continue
            sentences.append({
                'id': child.get('id'),
                'text': strip_markup(body) if args.text else body,
            })
        if sentences:
            out.append({'para': int(item.get('para')), 'sentences': sentences})

    def render():
        total = sum(len(x['sentences']) for x in out)
        src = f'channel {",".join(args.channel)}' if args.channel else '巴利原文'
        if not total:
            print(f'\n该 channel 在本章**没有任何内容**'
                  + (f'（服务端返回了 {placeholders} 条空占位）' if placeholders else '') + '。')
            print('如实报告「该译本在本章无文本」，不要拿别的版本或相邻章节顶替。')
            print('用 wikipali versions <坐标> 看这一段实际有哪些译本。')
            return
        print(f'\n{len(out)} 段 / {total} 句（{src}）'
              + (f'　⚠ 另有 {placeholders} 句该 channel 无内容，已略去' if placeholders else ''))
        for item in out:
            print(f'\n## {book}:{item["para"]}')
            for sent in item['sentences']:
                print(f'  {sent["id"]}  {sent["text"]}')
        print('\n句子 id 就是引用坐标（book-para-wordStart-wordEnd）。')

    emit(args, out, render)
    return 0


def pick_body(child, wanted_channels):
    """取这一句要展示的正文：指定了 channel 就取该 channel 的译文，否则取原文。

    **优先 content，为空才回退 html**——两者按 channel 类型互补：
    - original（巴利原文）的 content 是空的，正文在 html 里（带 <strong> 黑体）；
    - nissaya 的 content 是 markdown 源码「巴利词= 缅文释义。」，既紧凑又保住了
      「哪部分是巴利、哪部分是释义」这个区分；其 html 是同样内容的渲染结果，
      体积十几倍且把两者拼在了一起。
    """
    sources = []
    if wanted_channels:
        for tran in child.get('translation') or []:
            if ((tran.get('channel') or {}).get('id')) in wanted_channels:
                sources.append(tran)
        if not sources:
            return None
    else:
        sources = child.get('origin') or []

    for src in sources:
        body = (src.get('content') or '').strip()
        if body:
            return body
        html = (src.get('html') or '').strip()
        if html:
            return html
    return ''  


# ---------------------------------------------------------------------------
# versions —— 某坐标有哪些译本，以及没有哪些
# ---------------------------------------------------------------------------


def cmd_versions(args):
    client = make_client(args)
    book, para = parse_coord(args.coord)
    try:
        data = client.call('GET', 'v2/channel',
                           query={'view': 'paragraphs', 'book_id': book, 'para': para},
                           timeout=READ_TIMEOUT)
    except ApiError as exc:
        if exc.status and exc.status >= 500:
            raise WpError(
                f'查 {book}:{para} 的可用译本失败（HTTP {exc.status}）。\n'
                '稳定版站点上 channel?view=paragraphs 有已知缺陷，修复只在最新版代码上。\n'
                '请切到最新版再试：wikipali endpoint next，或本次调用加 --api next。'
            )
        raise explain_api_error(exc, f'查 {book}:{para} 的可用译本')
    rows = (data or {}).get('rows') or []

    def render():
        if not rows:
            print(f'{book}:{para} 在任何 channel 下都没有内容。')
            return
        print(f'{book}:{para} 有 {len(rows)} 个 channel 存有内容：\n')
        by_type = {}
        for r in rows:
            by_type.setdefault(r.get('type') or '?', []).append(r)
        for typ in sorted(by_type):
            print(f'  [{typ}]')
            for r in sorted(by_type[typ], key=lambda x: str(x.get('lang'))):
                name_l = (r.get('name') or '').lower()
                ai = ' ⚠疑似机器译' if any(h in name_l for h in MACHINE_HINTS) else ''
                print(f'    {str(r.get("lang")):<8} {str(r.get("name"))[:36]:<38} {r.get("uid")}{ai}')
        langs = {str(r.get('lang')) for r in rows}
        missing = [l for l in ('pali', 'my', 'zh-Hans', 'zh', 'en', 'th') if l not in langs]
        if missing:
            print(f'\n该段**没有**这些语言的内容：{", ".join(missing)}')
            print('如实报告「无」，不要拿相邻段落或别的译本凑。')
        print('\n标 ⚠疑似机器译 的按机器译文标注引用。**没标的不等于是人译**——'
              '名字判断很脆弱，权威做法是 wikipali get 看作者是不是模型，见 conventions.md。')

    emit(args, rows, render)
    return 0


# ---------------------------------------------------------------------------
# count —— 词频合计
# ---------------------------------------------------------------------------


def cmd_count(args):
    client = make_client(args)
    out = []
    for word in args.words:
        rows = fetch_forms(client, word)
        if not rows:
            out.append({'word': word, 'found': False})
            continue
        top = rows[0]
        forms = top.get('case') or []
        out.append({
            'word': word, 'found': True, 'lemma': top.get('word'),
            'forms': len(forms),
            'total': sum(int(f.get('count') or 0) for f in forms),
            'bold': sum(int(f.get('bold') or 0) for f in forms),
        })

    def render():
        print(f'{"词":<28}{"词根":<24}{"词形":>5}{"词次":>8}{"黑体":>7}')
        for r in out:
            if not r['found']:
                print(f'{r["word"]:<28}{"（语料中未见）":<24}')
                continue
            print(f'{r["word"]:<28}{r["lemma"]:<24}{r["forms"]:>5}{r["total"]:>8}{r["bold"]:>7}')
        print('\n这里数的是**词次**，不是段落数。段落数用 search 的 count。')

    emit(args, out, render)
    return 0


# ---------------------------------------------------------------------------
# terms —— 术语表（权威译名对照）
# ---------------------------------------------------------------------------


def cache_path(client, name):
    """缓存文件按**站点分桶**存放。

    线上四站共享同一个库，可以共用；但开发机（local）与任何自定义地址是**另一个
    数据库**。不分桶的话，用过一次 --api local 之后，之后打线上会静默拿到开发机的
    数据——看起来一切正常，数据却是错的。凭据早就是按桶存的，缓存同理。
    """
    import os
    import re as _re
    from creds import CREDS_DIR
    bucket = _re.sub(r'[^A-Za-z0-9_.-]', '_', client.bucket_name)
    return os.path.join(CREDS_DIR, 'cache', bucket, name)


def cmd_terms(args):
    import os
    client = make_client(args)
    path = cache_path(client, f'terms-{args.view}-{args.lang}.json')
    rows = None
    if os.path.exists(path) and not args.refresh:
        try:
            with open(path, encoding='utf-8') as fh:
                rows = json.load(fh)
        except (OSError, ValueError):
            rows = None
    if rows is None:
        note('正在拉取术语表全表（服务端不支持按词查询，只能整表拉后本地过滤）…')
        try:
            data = client.call('GET', 'v2/term-vocabulary',
                               query={'view': args.view, 'lang': args.lang}, timeout=120)
        except ApiError as exc:
            raise explain_api_error(exc, '取术语表')
        rows = (data or {}).get('rows') or []
        os.makedirs(os.path.dirname(path), exist_ok=True)
        with open(path, 'w', encoding='utf-8') as fh:
            json.dump(rows, fh, ensure_ascii=False)
        note(f'已缓存 {len(rows)} 条到 {path}（--refresh 可强制更新）')

    kw = (args.keyword or '').lower()
    hits = [r for r in rows if kw in (r.get('word') or '').lower()] if kw else rows

    def render():
        if not hits:
            print(f'术语表（{args.view} / {args.lang}，共 {len(rows)} 条）里没有含「{args.keyword}」的词条。')
            print('注意：这只说明术语表没收录，不代表语料里没有这个词。')
            return
        print(f'{len(hits)} 条（全表 {len(rows)}）：\n')
        for r in hits[: args.limit]:
            tag = f'  [{r["tag"]}]' if r.get('tag') else ''
            other = f'  / {r["other_meaning"]}' if r.get('other_meaning') else ''
            print(f'  {r.get("word"):<32} {r.get("meaning")}{other}{tag}')
        if len(hits) > args.limit:
            print(f'  …… 其余 {len(hits) - args.limit} 条（--limit 调整）')
        print('\n术语表是**权威译名对照**，写译文或论文时的用词应与它一致；'
              '与它不一致时要说明理由。')

    emit(args, hits, render)
    return 0


# ---------------------------------------------------------------------------
# related —— 本文 ↔ 义注 ↔ 复注的段落对应
# ---------------------------------------------------------------------------


def cmd_related(args):
    client = make_client(args)
    book, para = parse_coord(args.coord)
    try:
        data = client.call('GET', 'v2/related-paragraph', query={'book': book, 'para': para},
                           timeout=READ_TIMEOUT)
    except ApiError as exc:
        if exc.status and exc.status >= 500:
            # 服务端在「查无关联」时会抛异常（修复已合并，未部署到稳定版站点）。
            # 对使用者来说这多半就是「没有关联段落」，但不能替服务端断言，如实说明两种可能。
            raise WpError(
                f'查 {book}:{para} 的关联段落失败（HTTP {exc.status}）。\n'
                '最可能的原因是**该段没有关联段落**——稳定版站点在这种情况下会报 500，\n'
                '服务端修复已合并但尚未部署。也可能是服务本身有问题。\n'
                '两者无法从这里区分，**不要据此断言「该段有/没有注释」**；\n'
                '可以换最新版试试：wikipali --api next related {0}:{1}'.format(book, para)
            )
        raise explain_api_error(exc, f'查 {book}:{para} 的关联段落')
    rows = (data or {}).get('rows') or []

    def render():
        if not rows:
            print(f'{book}:{para} 没有关联段落。')
            print('约 2% 的段落没有 CST 锚点，这是正常结果，不是查询失败——'
                  '如实报告，不要转而去注释书里搜关键词充数。')
            return
        print(f'{book}:{para} 关联到 {len(rows)} 部书：\n')
        order = {'mūla': 0, 'aṭṭhakathā': 1, 'ṭīkā': 2}
        rows.sort(key=lambda r: order.get(text_layer(r.get('tags')), 9))
        for r in rows:
            layer = text_layer(r.get('tags')) or '未标层次'
            paras = r.get('para') or []
            coords = ' '.join(f'{r.get("book")}:{p}' for p in paras[:8])
            more = f' …共 {len(paras)} 段' if len(paras) > 8 else ''
            here = '  ← 当前' if int(r.get('book', -1)) == book and para in paras else ''
            print(f'  [{layer:<11}] {str(r.get("book_title_pali"))[:26]:<28}{here}')
            print(f'      {coords}{more}')
        first = rows[0]
        print(f'\n取文：wikipali get {first.get("book")}:{(first.get("para") or [0])[0]}')
        print('引用时必须标明层次——把义注的解释当成本文的说法是学术错误。')

    emit(args, rows, render)
    return 0


# ---------------------------------------------------------------------------
# articles / article / anthology —— 文章与文集
# ---------------------------------------------------------------------------


def cmd_articles(args):
    client = make_client(args)
    query = {'view': args.view, 'limit': args.limit, 'offset': args.offset}
    if args.keyword:
        query['search'] = args.keyword
    if args.lang:
        query['lang'] = args.lang
    try:
        data = client.call('GET', 'v2/article', query=query, timeout=READ_TIMEOUT)
    except ApiError as exc:
        raise explain_api_error(exc, '列出文章')
    rows = (data or {}).get('rows') or []

    def render():
        print(f'共 {(data or {}).get("count")} 篇，本页 {len(rows)}'
              + (f'（关键词「{args.keyword}」）' if args.keyword else ''))
        if not rows:
            return
        print()
        for r in rows:
            who = (r.get('editor') or {}).get('nickName') or ''
            sub = f'  —— {r["subtitle"]}' if r.get('subtitle') else ''
            print(f'  {str(r.get("lang")):<8} {str(r.get("title"))[:40]:<42}{sub}')
            print(f'      {r.get("uid")}   {who}   {str(r.get("updated_at"))[:10]}')
        print(f'\n读全文：wikipali article {rows[0].get("uid")}')

    emit(args, rows, render)
    return 0


def cmd_article(args):
    client = make_client(args)
    try:
        art = client.call('GET', f'v2/article/{args.uid}', timeout=READ_TIMEOUT)
    except ApiError as exc:
        raise explain_api_error(exc, f'读文章 {args.uid}')
    if not art:
        raise WpError(f'读不到文章 {args.uid}。')

    def render():
        who = (art.get('editor') or {}).get('nickName') or ''
        studio = (art.get('studio') or {}).get('nickName') or ''
        print(f'# {art.get("title")}')
        if art.get('subtitle'):
            print(f'  {art["subtitle"]}')
        print(f'  {art.get("lang")}  作者 {who}  studio {studio}  更新 {str(art.get("updated_at"))[:10]}')
        print(f'  uid {art.get("uid")}\n')
        body = art.get('content') or ''
        if args.chars and len(body) > args.chars:
            print(body[: args.chars])
            print(f'\n……全文 {len(body)} 字符，此处截断（--chars 0 取全文）')
        else:
            print(body)
        print('\n⚠ 文章是**二手研究**，不是原典。引用它的观点要标明作者，'
              '不要把它的说法当成经律本身的说法。')

    emit(args, art, render)
    return 0


def cmd_anthology(args):
    client = make_client(args)
    if args.uid:
        try:
            data = client.call('GET', f'v2/anthology/{args.uid}', timeout=READ_TIMEOUT)
        except ApiError as exc:
            raise explain_api_error(exc, f'读文集 {args.uid}')
        arts = (data or {}).get('article_list') or []

        def render():
            print(f'# {data.get("title")}   {data.get("lang")}')
            if data.get('summary'):
                print(f'  {data["summary"]}')
            print(f'  {len(arts)} 篇文章\n')
            for a in arts[: args.limit]:
                if isinstance(a, dict):
                    print(f'  {str(a.get("title"))[:44]:<46} {a.get("uid")}')
                else:
                    print(f'  {a}')
        emit(args, data, render)
        return 0

    try:
        data = client.call('GET', 'v2/anthology',
                           query={'view': args.view, 'limit': args.limit, 'offset': args.offset},
                           timeout=READ_TIMEOUT)
    except ApiError as exc:
        raise explain_api_error(exc, '列出文集')
    rows = (data or {}).get('rows') or []

    def render():
        print(f'共 {(data or {}).get("count")} 个文集，本页 {len(rows)}\n')
        for r in rows:
            print(f'  {str(r.get("lang")):<8} {str(r.get("title"))[:40]:<42} '
                  f'{r.get("childrenNumber")} 篇')
            print(f'      {r.get("uid")}')
        if rows:
            print(f'\n看目录：wikipali anthology {rows[0].get("uid")}')

    emit(args, rows, render)
    return 0


# ---------------------------------------------------------------------------
# books —— 分类目录：按 tag 找书
# ---------------------------------------------------------------------------


def fetch_books(client, refresh=False):
    """书目清单整表拉一次缓存在本地。服务端也缓存 24 小时，这里再缓存一层是为了
    让按 tag 筛选变成本地操作——281 条全量在手，筛什么都不用再请求。"""
    import os
    path = cache_path(client, 'book-titles.json')
    if os.path.exists(path) and not refresh:
        try:
            with open(path, encoding='utf-8') as fh:
                return json.load(fh)
        except (OSError, ValueError):
            pass
    try:
        data = client.call('GET', 'v2/book-title', timeout=READ_TIMEOUT)
    except ApiError as exc:
        raise explain_api_error(exc, '取书目清单')
    rows = (data or {}).get('rows') or []
    if rows and 'tags' not in rows[0]:
        raise WpError(
            '该站点返回的书目清单里没有 tags/toc 字段——服务端版本较旧，'
            '分类目录功能尚未上线。\n'
            '可以换最新版试试：wikipali --api next books …'
        )
    os.makedirs(os.path.dirname(path), exist_ok=True)
    with open(path, 'w', encoding='utf-8') as fh:
        json.dump(rows, fh, ensure_ascii=False)
    return rows


def cmd_books(args):
    client = make_client(args)
    rows = fetch_books(client, refresh=args.refresh)

    if args.tag_list:
        counter = {}
        for r in rows:
            for t in r.get('tags') or []:
                counter[t] = counter.get(t, 0) + 1

        def render_tags():
            print(f'{len(counter)} 个 tag（后面是有该 tag 的书数）：\n')
            for name, n in sorted(counter.items(), key=lambda kv: (-kv[1], kv[0]))[: args.limit]:
                print(f'  {n:>4}  {name}')
            print('\n多个 tag 用逗号连接是**且**的关系：'
                  'wikipali books --tags dīghanikāya,ṭīkā')
        emit(args, counter, render_tags)
        return 0

    hits = rows
    if args.tags:
        want = [t.strip() for t in args.tags.split(',') if t.strip()]
        hits = [r for r in hits if all(t in (r.get('tags') or []) for t in want)]
    if args.keyword:
        kw = args.keyword.lower()
        hits = [r for r in hits
                if kw in str(r.get('title', '')).lower() or kw in str(r.get('toc', '')).lower()]

    def render():
        scope = []
        if args.tags:
            scope.append(f'tags={args.tags}')
        if args.keyword:
            scope.append(f'关键词={args.keyword}')
        print(f'{len(hits)} 部书（全部 {len(rows)} 部）'
              + (f'  [{" ".join(scope)}]' if scope else ''))
        if not hits:
            print('\n没有匹配的书。用 --tag-list 看有哪些 tag；多个 tag 之间是「且」。')
            return
        print()
        for r in hits[: args.limit]:
            cs = f'  {r["related_name"]}' if r.get('related_name') else ''
            print(f'  {r.get("book")}:{r.get("paragraph"):<6} {str(r.get("toc"))[:38]:<40}{cs}')
            if args.show_tags:
                print(f'      {" ".join(r.get("tags") or [])}')
        if len(hits) > args.limit:
            print(f'  …… 其余 {len(hits) - args.limit} 部（--limit 调整）')
        if hits:
            first = hits[0]
            print(f'\n看某本书的章节：wikipali toc {first.get("book")}:{first.get("paragraph")}')

    emit(args, hits, render)
    return 0
