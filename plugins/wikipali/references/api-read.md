# WikiPali API 参考（检索与阅读）

读端**全部不需要凭据**。响应统一是 `{ ok, data, message }`。
坐标、引用、层次等通用规矩见 `conventions.md`。

## 1. 词形展开 —— `GET /v2/case/{词}`

输入任意变格形或词根，返回候选词典原型，按可能性排序。

```
data: { rows: [ { word, count, case: [ {word, count, bold}, ... ] } ], count }
```

`rows[0]` 是可能性最高的候选；`case` 是**该词根在语料中实际出现过的全部词形**，
每项带出现次数与其中的黑体次数。

**这是一切检索的前置**：`wbw_templates` 索引的是变格形，拿词典形直接查会返回 0 条
且不报错。

## 2. 词典 —— `GET /v2/dict?word={词}&lang={zh|en|jp|…}`

```
data.words[].words[] = {
  word, anchor, factors, parents,
  grammar: [ {word, type, grammar, parent, factors, confidence} ],   ← 形态分析：形 → 根
  dict:    [ {shortname, dictname, lang, note, dict_id} ]            ← 释义在 note
}
```

⚠ **释义在 `note` 字段**，`description` 是词典本身的介绍（"词数 7735"之类），不是词条
内容。`note` 里可能夹着 `<MdTpl …></MdTpl>` 模板标记，要清掉。

`case`/`grammar` 的方向是 **形 → 根**，用来确认"我选对了词根"；**根 → 全部形**是
`/v2/case` 的活。

## 3. 检索 —— `GET /v2/search-pali-wbw`

| 参数 | 说明 |
|---|---|
| `key` | **逗号分隔的词形列表**（不是词根）。多个词形之间是 OR |
| `bold` | `on` 只要黑体命中、`off` 只要非黑体。黑体是注释书标出词条的地方 |
| `book` | 限定书，值用 `search-pali-wbw-books` 返回的 `pcdBookId` |
| `tags` | 范围限定，`tag1,tag2;tag3` —— 组间 OR、组内 AND |
| `limit` / `offset` | 分页。`limit=200` 实测正常 |
| ~~`view`~~ / ~~`type`~~ | **实测无影响**，源码也没读，可省略 |

```
data: { count: 命中段落数, rows: [ { book, paragraph, rank, path, paliTitle, highlight } ] }
```

- `rank` = `sum(weight)`，黑体权重更高；
- `path` 是章节路径数组，每项 `{book, paragraph, title, level}`；
- `highlight` 里命中词包在 `<span class='hl'>`，**并保留原文的 `<span class="bld">`**——
  后者是黑体，判断"这段是不是定义"要靠它，清洗 HTML 时不能一并丢掉。

## 4. 出处分布 —— `GET /v2/search-pali-wbw-books?key={词形,…}`

```
data.rows[] = { pcdBookId, count, book, paragraph, paliTitle, tags: [{name}] }
```

⚠ **`count` 数的是词次，不是段落数**。同一段里出现多次算多次。段落数要看
`search-pali-wbw` 的 `count`。实测 `parivāsa`：词次 449、段落 281。方法论陈述里
写错这两个数是硬伤。

`tags` 含 `mūla` / `aṭṭhakathā` / `ṭīkā`，用来区分本文、义注、复注。

## 5. 取文 —— `GET /v2/sentence`

```
view=paragraph&book={book}&para={p1,p2,…}&channels={uid,…}   按段落取
view=chapter&book={book}&para={章节 para}&channels={uid,…}    取整章
```

⚠ **`channels` 参数是必需的**。不带 `channels` 会 **500**（实测；`lang` / `channel_type`
单独用同样 500，它们只能与 `channels` 并用）。所以客户端必须永远给一个默认 channel。

巴利原文的 channel：`_System_Pali_VRI_`，uid `00b577c0-13b9-11ee-a05a-b7307efd9ee6`。

返回的每行是一个句子（不是整段）：`{id, content, content_type, html, book, paragraph,
word_start, word_end, editor, channel, updated_at}`。按 `word_start` 排序拼起来才是整段。

⚠ 返回里字段名是 `book`（不是 `book_id`）。

## 6. 目录 —— `GET /v2/palitext`

`view=book-toc` / `chapter` / `chapter_children` / `children` / `paragraph`，
参数 `book` / `para` / `series`。

## 已知故障

| 端点 | 现象 |
|---|---|
| `GET /v2/search?view=pali` | **500**。走 gRPC 的 `tulip` 服务，线上不可达 |
| `GET /v2/search-book-list` | **500**，同上 |

这两个是**词组/短语**检索（多词按全文匹配）。单词检索走 `search-pali-wbw`，不受影响。
遇到需要短语检索时，把短语拆成词分别展开词形再检索。

`GET /v2/search?view=title`（按标题）与 `view=page`（按页码）不走 gRPC，可用。

⚠ `search` 的 `key` 以 `para` 开头、或首字母是 `M/P/T/V/O` 时会被劫持到页码检索分支。
