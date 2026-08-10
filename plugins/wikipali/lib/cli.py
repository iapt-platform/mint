"""命令行装配。

读侧（forms/word/search/dist/get）不需要凭据；写侧（ensure-model/channels/grant/write）
需要，且写入前有确认闸门。登录是独立的 wikipali-login，本入口不接触密码。
"""

import argparse
import sys

import cmd_read
import cmd_site
import cmd_write
from client import DEFAULT_BATCH
from errors import WpError


def build_parser():
    parser = argparse.ArgumentParser(
        prog='wikipali',
        description='WikiPali 客户端：检索、阅读、以 AI 模型身份写入',
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog='凭据在 ~/.wikipali/credentials.json（0600）。登录请跑 wikipali-login。',
    )
    parser.add_argument('--api', help='本次调用使用的 API 地址（序号/简称/完整 url），不写回凭据文件')
    sub = parser.add_subparsers(dest='command', required=True)

    def add(name, help_text, needs_json=True):
        p = sub.add_parser(name, help=help_text)
        if needs_json:
            p.add_argument('--json', action='store_true', help='输出原始 JSON')
        return p

    # -- 站点与状态 --------------------------------------------------------
    p = add('endpoint', '查看 / 切换 API 地址', needs_json=False)
    p.add_argument('target', nargs='?', help='序号、简称（www/www.cc/next/next.cc/staging/local）或完整 url')
    p.add_argument('-l', '--list', action='store_true',
                   help='只列出不提示选择（在终端里跑而又不想被追问时用）')
    p.set_defaults(func=cmd_site.cmd_endpoint)

    p = add('whoami', '显示当前凭据状态', needs_json=False)
    p.add_argument('--check', action='store_true', help='额外向服务端校验用户 token')
    p.set_defaults(func=cmd_site.cmd_whoami)

    # -- 读 ----------------------------------------------------------------
    p = add('forms', '展开词形——一切检索的前置步骤')
    p.add_argument('word', help='词根或任意变格形')
    p.add_argument('--limit', type=int, default=3, help='显示几个候选词根，默认 3')
    p.set_defaults(func=cmd_read.cmd_forms)

    p = add('word', '词典释义与形态分析，用来确认选对了词根')
    p.add_argument('word')
    p.add_argument('--lang', default='zh', help='释义语言，默认 zh')
    p.add_argument('--limit', type=int, default=3, help='最多显示几个词条')
    p.add_argument('--dicts', type=int, default=3, help='每个词条显示几部词典')
    p.set_defaults(func=cmd_read.cmd_word)

    p = add('search', '按词形检索段落')
    p.add_argument('forms', nargs='*', help='逗号或空格分隔的词形；或用 --lemma 自动展开')
    p.add_argument('--lemma', help='给词根，自动先展开成全部词形再检索')
    p.add_argument('--bold', action='store_true', help='只要黑体命中（注释书标出的词条）')
    p.add_argument('--book', help='限定书（用 dist 输出里的 --book 值）')
    p.add_argument('--tags', help='限定范围，如 vinaya 或 vinaya,mūla;vinaya,aṭṭhakathā')
    p.add_argument('--limit', type=int, default=50, help='本页条数，默认 50')
    p.add_argument('--offset', type=int, default=0)
    p.add_argument('--width', type=int, default=200, help='每条摘要的字符数')
    p.set_defaults(func=cmd_read.cmd_search)

    p = add('dist', '出处分布：命中散布在哪些书、各多少、什么层次')
    p.add_argument('forms', nargs='*')
    p.add_argument('--lemma')
    p.add_argument('--tags')
    p.add_argument('--limit', type=int, default=25, help='最多列几部书')
    p.set_defaults(func=cmd_read.cmd_dist)

    p = add('get', '按坐标取文，如 wikipali get 216:35 216:36')
    p.add_argument('coords', nargs='+', help='book:paragraph，可给多个')
    p.add_argument('--channel', action='append',
                   help='channel uid，可重复；缺省取巴利原文')
    p.add_argument('--limit', type=int, default=200, help='每次请求最多取几句')
    p.set_defaults(func=cmd_read.cmd_get)

    p = add('toc', '章节目录')
    p.add_argument('coord', help='book:paragraph，任意段号即可，服务端会找到所属丛书')
    p.add_argument('--depth', type=int, default=4, help='最多显示到第几层，默认 4')
    p.add_argument('--all', action='store_true', help='显示整套丛书而不只当前这本')
    p.set_defaults(func=cmd_read.cmd_toc)

    p = add('chapter', '先报体量，再按需取整章')
    p.add_argument('coord', help='book:paragraph，正文段也行，会自动向上找章节')
    p.add_argument('--fetch', action='store_true', help='确认要读全文时加这个；不加只报体量')
    p.add_argument('--channel', action='append', help='channel uid，可重复；缺省取巴利原文')
    p.add_argument('--warn-at', type=int, default=8000, help='超过多少字符就提示，默认 8000')
    p.add_argument('--text', action='store_true', help='输出纯文本而非 html（黑体转成 **）')
    p.add_argument('--via', choices=['tipitaka-content', 'chapter-content'],
                   default='tipitaka-content',
                   help='取文走哪个端点。默认 tipitaka-content；chapter-content 返回逐句的'
                        '多版本结构，写入侧需要它')
    p.add_argument('--limit', type=int, default=200)
    p.set_defaults(func=cmd_read.cmd_chapter)

    p = add('versions', '某坐标有哪些译本，以及没有哪些')
    p.add_argument('coord', help='book:paragraph')
    p.set_defaults(func=cmd_read.cmd_versions)

    p = add('count', '词频合计（词次，不是段落数）')
    p.add_argument('words', nargs='+', help='一个或多个词/词根')
    p.set_defaults(func=cmd_read.cmd_count)

    p = add('terms', '术语表：权威译名对照')
    p.add_argument('keyword', nargs='?', help='按巴利词形过滤；省略则列全表')
    p.add_argument('--lang', default='zh-Hans')
    p.add_argument('--view', default='community')
    p.add_argument('--limit', type=int, default=30)
    p.add_argument('--refresh', action='store_true', help='强制重新拉取（全表有缓存）')
    p.set_defaults(func=cmd_read.cmd_terms)

    p = add('related', '本文 ↔ 义注 ↔ 复注的段落对应')
    p.add_argument('coord', help='book:paragraph')
    p.set_defaults(func=cmd_read.cmd_related)

    p = add('articles', '列出 / 搜索文章（二手研究）')
    p.add_argument('keyword', nargs='?', help='标题关键词')
    p.add_argument('--lang', help='按语言过滤')
    p.add_argument('--view', default='public')
    p.add_argument('--limit', type=int, default=20)
    p.add_argument('--offset', type=int, default=0)
    p.set_defaults(func=cmd_read.cmd_articles)

    p = add('article', '读一篇文章的全文')
    p.add_argument('uid')
    p.add_argument('--chars', type=int, default=4000, help='最多输出多少字符，0 为不截断')
    p.set_defaults(func=cmd_read.cmd_article)

    p = add('anthology', '文集：不给 uid 列表，给 uid 看目录')
    p.add_argument('uid', nargs='?')
    p.add_argument('--view', default='public')
    p.add_argument('--limit', type=int, default=30)
    p.add_argument('--offset', type=int, default=0)
    p.set_defaults(func=cmd_read.cmd_anthology)

    p = add('books', '分类目录：按 tag 找书，如「长部的复注有哪些」')
    p.add_argument('keyword', nargs='?', help='按书名/toc 过滤')
    p.add_argument('--tags', help='按 tag 筛，逗号分隔是**且**，如 dīghanikāya,ṭīkā')
    p.add_argument('--tag-list', action='store_true', help='列出全部 tag 及各自的书数')
    p.add_argument('--show-tags', action='store_true', help='每本书都列出它的 tag')
    p.add_argument('--limit', type=int, default=40)
    p.add_argument('--refresh', action='store_true', help='强制重新拉取书目清单（有本地缓存）')
    p.set_defaults(func=cmd_read.cmd_books)

    # -- 写 ----------------------------------------------------------------
    p = add('ensure-model', '幂等地建立模型记录并取模型身份 token', needs_json=False)
    p.add_argument('--name', help='模型标识，如 claude-opus-5（会成为句子作者署名）')
    p.add_argument('--model', help='底层模型 id')
    p.add_argument('--url', dest='url', help='模型服务地址')
    p.add_argument('--description', help='描述')
    p.add_argument('--privacy', choices=['private', 'public'], default='private')
    p.set_defaults(func=cmd_write.cmd_ensure_model)

    p = add('revoke', '撤销该模型已签出的全部 token', needs_json=False)
    p.add_argument('--uid', help='模型 uid，缺省用缓存里的')
    p.add_argument('-y', '--yes', action='store_true')
    p.set_defaults(func=cmd_write.cmd_revoke)

    p = add('channels', '列出当前账号可编辑的 channel')
    p.add_argument('--search', help='按名字过滤')
    p.set_defaults(func=cmd_write.cmd_channels)

    p = add('grant', '为某个 channel 签发 access token 并缓存', needs_json=False)
    p.add_argument('channel', nargs='?', help='channel uid / 列表序号 / 名字片段；省略则交互选择')
    p.add_argument('--book', type=int, default=0, help='限定 book，0 表示不限（默认）')
    p.add_argument('--force', action='store_true', help='即使缓存未过期也重新签发')
    p.set_defaults(func=cmd_write.cmd_grant)

    p = add('write', '写入句子', needs_json=False)
    p.add_argument('file', help='句子 JSON 文件，- 表示从 stdin 读')
    p.add_argument('--channel', help='目标 channel（uid / 序号 / 名字片段）')
    p.add_argument('--book', type=int, help='access token 的 book 范围，缺省按句子推断')
    p.add_argument('--batch', type=int, default=DEFAULT_BATCH, help=f'每批条数，默认 {DEFAULT_BATCH}')
    p.add_argument('--content-type', default='markdown')
    p.add_argument('--preview', type=int, default=5, help='确认时预览几条')
    p.add_argument('--dry-run', action='store_true', help='只做校验与回显，不发请求')
    p.add_argument('-y', '--yes', action='store_true', help='跳过交互确认（非交互环境必须显式给）')
    p.set_defaults(func=cmd_write.cmd_write)

    return parser


def main(argv=None):
    args = build_parser().parse_args(argv)
    try:
        return args.func(args)
    except WpError as exc:
        print(f'错误：{exc}', file=sys.stderr)
        return 1
    except KeyboardInterrupt:
        print('\n已中断。', file=sys.stderr)
        return 130
