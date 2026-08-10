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

## 7. 章节目录 —— `GET /v2/palitext?view=book-toc&book={book}&para={任意段号}`

`para` 给该书内任意段号即可，服务端自己往上找顶级目录。返回的是**整套丛书**的目录
（如给 book 216 会连 213/214/… 一起返回，实测 951 条），要按 `book` 自行过滤。

行字段：`book` / `paragraph` / `toc`（巴利标题）/ `level`（1–7，1 为顶层）。

## 8. 段落与章节元信息 —— `GET /v2/palitext/{book}-{paragraph}`

⚠ 是 **path 参数**（`/palitext/216-481`），不是 query。`data` 是**单个对象**，不是 rows。

| 字段 | 说明 |
|---|---|
| `class` | `chapter` / `subhead` / `bodytext` … |
| `chapter_len` | **章节段数**。⚠ 正文段自己也有这个字段且值为 1，所以判断「是不是章节」要看它 **> 1**，不能看有没有 |
| `chapter_strlen` | 章节字符数——**取整章前报体量就靠它** |
| `next_chapter` / `prev_chapter` / `parent` | 导航 |
| `path` | 面包屑。⚠ **这个端点返回的是 JSON 字符串，而 search 返回的是数组**，客户端两种都要能吃 |
| `pcd_book_id` | 另一套书号，**等于 `search-pali-wbw` 的 `book` 参数值**（实测 216 → 278，与 dist 输出的 `--book 278` 一致）|

从正文段找所属章节：取 `path` 的**末项**，那就是直接父章节；没有 path 才退回 `parent`。

## 9. 某段有哪些译本 —— `GET /v2/channel?view=paragraphs&book_id={book}&para={para}`

返回该坐标下有内容的全部 channel（`uid` / `name` / `type` / `lang` / `status`）。

⚠ **稳定版站点上这个分支会 500**，修复只在最新版代码里（`c392d33c3 :bug: view=paragraphs
只显示一行`）。客户端遇到 5xx 要提示切到 `next`，不要当成「该段没有译本」。

## 10. 术语表 —— `GET /v2/term-vocabulary?view=community&lang=zh-Hans`

权威译名对照。行字段：`guid` / `word`（巴利词形）/ `tag` / `meaning` / `other_meaning`。

⚠ **不支持按词查询**（带 `word=` 参数返回的不是 JSON），只能拉全表——实测 zh-Hans
共 **17074 条**，客户端要缓存后本地过滤。

## 其他 channel 视图

`view` 取值：`public`（status=30）/ `studio` / `studio-all` / `user-edit` / `user-in-chapter`
/ `system` / `paragraphs` / `id`。

`status` 不止 10/30：实测 5（610 个，basic 用户新建的）、30（559，公开）、10（495）、0、1 都在用。

## 11. 整章内容 —— `GET /v2/chapter-content/{book}-{para}`

一次调用返回整章，带 `?channels={uid,…}` 时把译文一并返回，**服务端已按
`wordStart/wordEnd` 与原文对齐**。比「`palitext` 报体量 + `sentence` 逐段取」少一次
往返，也省了客户端自己配对。

`data.content` 是**双重编码的 JSON 字符串**（`content_type: "json"`），要二次解析：

```
data.content → [ {book, para, channels, sentences: [[139,861,2,8], …], mode, children: [
    { id: "139-861-2-8", book, para, wordStart, wordEnd,
      origin: [...], translation: [...], commentaries: [...],
      tranNum, nissayaNum, commNum, originNum, simNum }
  ]} ]
```

`children[].id` 就是 `book-para-wordStart-wordEnd`，与平台文章里的引用格式
`{{141-120-17-40}}` 一致——读到什么就能直接引用什么。

不带 `channels` 时也返回 `tranNum` / `nissayaNum` / `commNum` / `simNum`，等于**免费给出
章节级的「有哪些资源」**（`versions` 只能按段落查，这里补上了章节粒度）。

### ⚠ content 与 html 按 channel 类型互补，不能只取一个

| channel 类型 | `content` | `html` | 该用哪个 |
|---|---|---|---|
| `original`（巴利原文） | **空** | 正文在这里，带 `<strong>` 黑体 | `html` |
| `nissaya`（缅文逐词） | markdown 源码 `巴利词= 缅文释义。` | 同内容的渲染，**体积十几倍** | **`content`** |

所以取值规则是「**优先 `content`，为空才回退 `html`**」。

nissaya 的 `html` 里每条 gloss 包在 `<MdTpl props="<base64>">` 里，base64 解出来是
`{"pali": "…", "meaning": ["…"], "lang": "my"}`——**它把巴利词与释义分开标注**，而渲染
出的 span 只是把两者拼接。这个区分是逐词解析的核心，**不要以为 props 是冗余而删掉**
（本项目曾犯过这个错）。用 `content` 就天然保留了这个区分，`=` 左右分别是巴利与释义。

### ⚠ 请求的 channel 无内容时会返回等量空占位

指定 `channels=X` 而 X 在本章没有内容时，服务端**仍为每一句返回一条 `content` 与
`html` 都是空字符串的条目**。照直显示会让人以为「有译文只是没渲染出来」。客户端必须
滤掉空条目，并明确报告「该译本在本章无文本」——实测同一坐标下 `sentence` 端点返回
`count: 0`，两处口径一致。

### 体积

整章原始返回 24 KB（仅原文）到 86 KB（带 nissaya）。只保留每句的 `id` 与正文后分别是
3.3 KB 与 9.0 KB（**14% 与 10%**）。整章直接喂给模型是浪费，务必先过滤。

## 11b. 整章内容（首选）—— `GET /v2/tipitaka-content/{book}-{para}`

**取整章内容用这个，不用 `chapter-content`。** 走 OpenSearch 的预建文档
（`tipitaka_chapter_{book}-{para}_{channelId}`），返回的 `data` 是一整串渲染好的 HTML。

| | `tipitaka-content` | `chapter-content` |
|---|---|---|
| channel 参数 | `channel=<uuid>`（**单数，一次一个**） | `channels=<uuid,…>`（可多个）|
| 缺省 | 巴利原文 `_System_Pali_VRI_` | 同 |
| 返回 | 一整串 HTML | 嵌套 JSON，逐句带 origin/translation/各类计数 |
| 用途 | 读——给人或模型看 | **写入侧要用**（需要逐句的多版本结构）|

HTML 里每句包在 `data-sid='93-6-31-46'` 中，**sid 就是引用坐标**
`book-para-wordStart-wordEnd`，段落号从 sid 里就能取，不必解析外层的 `data-para`。

### 三种响应都要分开处理

| 情况 | 表现 | 该怎么说 |
|---|---|---|
| 正常 | 200，HTML 里有 `data-sid` | — |
| 有文档但没句子 | 200，但 `data-sid` 数为 0 | 「该版本在本章没有句子内容」 |
| 没有该版本的预建文档 | **400**，`message` 里带 OpenSearch 的 `found:false` | 「该 channel 在本章无文本」，**不是服务故障** |

第三种要靠 message 里的 `found` + `false` 判断。并非所有 channel 都有预建文档——
实测 `93-5`：巴利原文、庄春江、北大-法胜、Punnacari 有；wbw 与 deepseek 没有。

### ⚠ `<code>` 是版本页码，必须留在原位

正文里夹着 `<code>M1.1</code><code>V1.1</code><code>P1.1</code><code>T1.1</code>：
**M=缅甸版、V=VRI、P=PTS、T=泰版**。它标的是页在正文中的**起始位置**，与段落不是
一一对应，所以**不能抽到单独的字段里**——抽走就丢了位置信息。

去标签时也要留意：直接删会让页码粘到前一个词上（`Evaṃ M1.1` → `EvaṃM1.1`），看着
像巴利词形的一部分。本项目转成 `[M1.1]` 保持可分辨。

PTS 页码是西方巴利学界的标准引用依据，别丢。

### 体积

`93-5` 一章（7 段 37 句）原始返回 9.7 KB。这个端点的 `display` **本来就精简**，
只有句子和最小包装，过滤后省不下多少（纯文本 8.7 KB）——与 `chapter-content` 完全
不同，那边 24 KB 里绝大部分是每句重复的 channel/studio/editor 元数据。

## 12. 章节元信息的两个等价端点

`GET /v2/chapter/{book}-{para}` 与 `GET /v2/palitext/{book}-{para}` **返回完全一致**
（实测字段与取值逐一相同，两个版本的站点上都是 200）。本项目用 `palitext`，没有偏好上的
理由，换用 `chapter` 亦可。

## 已知故障

| 端点 | 现象 |
|---|---|
| `GET /v2/search?view=pali` | **500**。走 gRPC 的 `tulip` 服务，线上不可达 |
| `GET /v2/search-book-list` | **500**，同上 |

这两个是**词组/短语**检索（多词按全文匹配）。单词检索走 `search-pali-wbw`，不受影响。
遇到需要短语检索时，把短语拆成词分别展开词形再检索。

`GET /v2/search?view=title`（按标题）与 `view=page`（按页码）不走 gRPC，可用。

⚠ `search` 的 `key` 以 `para` 开头、或首字母是 `M/P/T/V/O` 时会被劫持到页码检索分支。
