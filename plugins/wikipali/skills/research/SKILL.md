---
name: research
description: "Use this skill to research Pali Buddhist texts with WikiPali's corpus — finding where a term occurs across the Tipiṭaka and its commentaries, reading the Pali source, comparing translations, and producing cited scholarly writing. Trigger whenever the user asks to look up a Pali word or concept, find passages about a topic in the canon, analyse how a term is used, compare mūla / aṭṭhakathā / ṭīkā, check a translation against the Pali, or write a paper or summary grounded in Pali sources. Do not use for writing data into WikiPali (that is the write skill)."
---

# WikiPali 研究

用 WikiPali 的语料做巴利文献研究：定位 → 取证 → 展开 → 交叉验证 → 成文。

命令是 `wikipali <子命令>`。检索与阅读全部只读，**不需要登录**。

⚠ **刚装好或刚更新插件时，`wikipali` 可能还不在 PATH 上**——PATH 注入在会话启动时完成，装完要重启会话。若 `command -v wikipali` 为空，改用绝对路径 `${CLAUDE_PLUGIN_ROOT}/bin/wikipali`，并提醒用户重启会话。

**坐标、引用格式、文献层次、译文来源判定见 `references/conventions.md`——那是所有
skill 共用的规矩，必须遵守。** 端点细节见 `references/api-read.md`。

## 铁律

1. **每一条写进正文的引用，必须带得回坐标。** 手里没有坐标的内容，一个字都不许写进
   产出。宁可说"未找到相关段落"，也不要凭印象转述。
2. **检索前必须先展开词形**（`wikipali forms`，或给 `search --lemma`）。直接拿词典形
   去搜会**返回 0 条且不报错**。这是本工具最容易犯的错，因为它看起来像"搜过了，没有"。
3. **0 条结果不等于"没有材料"。** 依次怀疑：词形没展开 → 词根选错 → 范围限太窄 →
   才是真的没有。把怀疑过程说给用户听。
4. **本文、义注、复注不能混。** 引用时必须标明层次（见 conventions.md）。
5. **判断依据是巴利原文，译文只作佐证。** 机器生成的译文必须显式标注。
6. **不要把整章往上下文里灌。** 先看体量再决定取多少。

## 流程

### 0a. 需要浏览语料结构时：先找书，再看章节

```bash
wikipali books --tag-list                      # 有哪些分类 tag
wikipali books --tags dīghanikāya,ṭīkā         # 长部的复注有哪些（多个 tag 是「且」）
wikipali toc 185:3                             # 那本书的章节目录
```

`books` 按 tag 筛（`mūla` / `aṭṭhakathā` / `ṭīkā` / 各部尼柯耶 / 各种论书），
输出直接接 `toc` 看章节。适合「我想知道某类文献有哪些」这种起步阶段，
而不是已经有明确关键词的检索。

⚠ 该功能需要服务端较新版本；旧版会明确报「分类目录功能尚未上线」。

### 0b. 先看有没有人写过

```bash
wikipali articles 别住          # 平台上的二手研究
wikipali anthology              # 文集（成体系的系列文章）
wikipali article <uid>          # 读全文
```

**几秒钟的事，能省掉重复劳动，也能发现你要处理的分歧点。** 但记住文章是二手研究：
引用它的观点要标明作者，**不能把它的说法当成经律本身的说法**。

### 1. 展开词形（永远的第一步）

```bash
wikipali forms parivāsa
```

输出候选词根，每个带该词根在语料中出现过的全部词形及频次、黑体数。取可能性最高的
那个，但**要看一眼其余候选**：若目标概念同时有名词与动词两条线（`parivāsa` /
`parivāseti`），两条都要展开。

拿不准选哪个候选时：

```bash
wikipali word parivāsa          # 释义 + 形态分析，确认词根选对了
wikipali terms parivāsa         # 术语表：该词的权威中文译名
```

**产出里的译名应与术语表一致**，不一致要说明理由。别自己从构词法编译名——实测
`samodhānaparivāsa` 的权威译名是「合并别住」，凭字面容易写成别的。

### 2. 看分布，再决定范围

```bash
wikipali dist --lemma parivāsa
```

输出每部书的命中数、`--book` 值和 tags，并按 `mūla` / `aṭṭhakathā` / `ṭīkā` 汇总。
这一步决定后面所有工作的范围：

- 命中集中在律藏 → 后续加 `--tags vinaya` 收窄；
- 本文命中少而义注命中多 → 这是个**注释书概念**，论文结构要相应调整；
- 总量太大 → 先收窄再取证，不要硬取。

⚠ `dist` 数的是**词次**，`search` 数的是**段落数**，两个数不相等。方法论里别写混。

### 3. 取定义：前 50 条，靠排序

```bash
wikipali search --lemma parivāsa --limit 50
```

结果按黑体加权排序，**注释书里作为词条解释的段落会自然排在前面**。从前 50 条里挑出
讲定义和执行流程的，用来写定义部分。

**前几名全是 aṭṭhakathā / ṭīkā 是正常的，不是检索出了问题。** 大部分名词解释在义注
（aṭṭhakathā）与复注（ṭīkā）里，律藏的根本（pāḷi / mūla）中也有部分解释。

所以：不必因为看不到本文命中就怀疑检索出了错；但也**不要断定本文没有解释**——本文
里的那部分同样要引，按铁律 4 标明层次。

命中总量特别大、前 50 条噪声明显时，可以加 `--bold` 只看黑体命中来收窄——那是收窄
手段，不是默认做法，因为不加黑体的定义段落会被它漏掉。

### 3b. 留意注疏有没有枚举子类

注疏解释术语时常会枚举它的子类（`catubbidho X`、`duvidho X` 这类体例很常见，但**不是
每个词都有**）。读定义段落时**留意有没有**这种枚举——有，它就是现成的分类框架，比自己
归纳可靠；没有就跳过，**不要硬造分类**。

**发现子类后，每一个都要单独再查一遍，不得凭名字推测含义。** 巴利复合词看起来像是
可以拆开理解的（`paṭicchanna-parivāsa` 像"覆藏＋别住"），而这种推测正是编造释义的
入口——构词法给的是字面拼合，不是该词在律学里的实际所指。

每个子类走一遍完整链路：

```bash
wikipali count samodhānaparivāsa paṭicchannaparivāsa …  # 一次看清各子类的词次
wikipali terms samodhānaparivāsa                        # 权威译名
wikipali search --lemma samodhānaparivāsa --limit 20    # 找它自己的解释段落
wikipali get <坐标>                                      # 取原文
```

**拿到注疏对该子类的解释原文之前，不要写出它的含义。** 查不到解释就如实说"语料中
未见对该子类的解释"，不要用构词法去补——这是铁律 1 在子类上的具体化。

**两个实测踩到的陷阱：**

- **不同注疏的枚举可能不一样。** 实测 `parivāsa`：Pācityādiyojanā（202:1882）与
  Kaṅkhāvitaraṇī-abhinavaṭīkā（212:1134）都说"四种"，但列出的**不是同一组**——后者
  含 `suddhantaparivāsa`（语料中 86 次），前者没有。所以**不要把某一处的列表当成
  「标准分类」**：按出处分别记录，各家不一致就如实说明不一致。这本身往往是论文里
  值得写的一笔。
- **子类可能还有子类。** `samodhānaparivāsa` 自己又分三种（odhāna / aggha /
  missaka，见 141:120）。要挖多深由研究问题决定，不必无限递归，但要说明你停在了
  哪一层。

频次**只报数字，不从中下判断**。例如 `parivāsa` 的四个子类：samodhāna 260 次、
paṭicchanna 29、titthiya 18、appaṭicchanna 14。把这组数字连同出处交给用户即可——高频
可能只是某部注疏反复提及，不等于该子类更重要，这个判断该由研究者做。

### 4. 取案例：全量检索

```bash
wikipali search --lemma parivāsa --tags vinaya --limit 200
```

每条给出坐标、章节路径和高亮片段。**先用片段做初筛**，判断该段落属不属于目标案例
类型，不要一上来就把每段全文取回来。

**这一步要有意识地回到本文（mūla）。** 与定义相反，案例——谁、在什么情况下、如何
执行、判定结果如何——在律藏本文里，注疏是对这些案例的解释。用 `dist` 输出里本文那
几部书的 `--book` 值收窄，别让注疏的高命中密度把本文案例挤出视野。

### 5. 取原文

```bash
wikipali get 216:35 216:36 216:41       # 按坐标精确取，缺省是巴利原文
wikipali toc 216:512                    # 看这本书的章节结构
wikipali chapter 216:512                # 只报体量：章节范围、段数、字符数
wikipali chapter 216:512 --fetch        # 确认要读全章时才加 --fetch
wikipali chapter 216:512 --fetch --channel <uid>   # 读某一个译本（一次一个）
wikipali chapter 216:512 --fetch --text            # 纯文本，更省
```

`chapter` 给正文段也行，会自动向上找到所属章节。**不加 `--fetch` 就只报体量**——
这是上下文预算的闸门，先看清多大再决定读不读。

取文时每句只保留 **id + 正文**，服务端原始返回的十分之一左右。**句子 id 就是引用坐标**
（`139-861-9-12` = book-para-wordStart-wordEnd），读到什么就能直接引用什么。

若指定的 channel 在本章没有内容，命令会明确报「该译本在本章无文本」而不是显示一堆
空行——服务端在这种情况下会返回等量的空占位条目。

### 5b. 从本文跳到义注与复注

```bash
wikipali related 216:512        # 该段在义注、复注里的对应段落
wikipali get 141:65 141:66      # 读义注怎么解释这一段
```

**这是找注释的正确方式，不要回头去注释书里搜关键词。** 关键词搜到的未必是在解释这一段，
而注释书解释某段时也未必重复原词——两头都会错。`related` 走的是 CST 锚点的段落对应关系，
是文献学上正确的对齐。

输出按 mūla → aṭṭhakathā → ṭīkā 排序并标好层次，直接可用于引用。

约 2% 的段落没有锚点，那时会明确报「没有关联段落」——**如实说，不要转而搜关键词充数**。

### 6. 交叉验证

```bash
wikipali versions 216:512               # 该坐标有哪些译本，以及没有哪些
wikipali get 216:512 --channel <uid>    # 取指定译本
```

`versions` 会把该段**没有**的语言明确列出来——某语言无译文时如实说"无"，**不要拿
相邻段落或别的译本凑**。

⚠ `versions` 依赖的端点在稳定版站点上有缺陷，会提示你切到 `next`（`wikipali endpoint next`）。

⚠ 用户要切换站点却**没指定目标**时，把站点列表**作为选择题呈现给他**再执行——不要替他挑，
也不要指望命令行弹选单（agent 没有 tty，永远等不到）。见 `references/conventions.md`。

⚠ 输出里标 **⚠疑似机器译** 的按机器译文标注引用；但**没标的不等于人译**——很多 channel
直接用模型名命名（`deepseek`、`qwen-max`、`grok-简体中文`），判定要看 `get` 返回的作者，
见 `references/conventions.md`。

## 上下文预算

| 操作 | 默认上限 | 超了怎么办 |
|---|---|---|
| `search` 摘要 | 一次不超过 50 条进上下文 | 用 `--tags` / `--book` 收窄，或分页逐批归纳 |
| `get` 取段落 | 一次不超过 20 段 | 分批，每批处理完先记下结论再取下一批 |

原则：**上下文里应该留下结论和坐标，而不是原文**。取回一批材料 → 归纳出结论并记下
支撑坐标 → 再取下一批。不要把所有原文堆着等最后一起分析。

`--width` 控制每条摘要的长度，`--json` 输出原始数据（需要自己处理时用）。

## 按任务类型分档

同一套检索，产出的详略要看用户要什么：

| 用户要的 | 产出形态 |
|---|---|
| 快速查询（"parivāsa 什么意思"、"哪几处提到 X"） | 结论 + 坐标。**不报告检索方法**，别把查词变成论文 |
| 综述 / 分析（"X 在律藏里怎么用"） | 结论 + 坐标 + 一句话交代范围 |
| 论文 / 研究报告（用户明确说要写论文、要发表、要引用） | 完整方法论：展开了哪些词形（连同频次）、检索范围、总命中词次与段落数、实读段数、黑体与非黑体分布 |

判断不了属于哪一档时按中间档走，并问用户要不要完整的检索方法说明。

论文档的方法论不是修辞——"检索 parivāsa 的 13 个词形，得 281 段（449 词次），分布
于 43 部书，其中律藏本文 159 词次、义注 28、复注 102"这样一句，是读者判断你的检索
是否穷尽的唯一依据。

## 常见错误

| 现象 | 真正的原因 |
|---|---|
| 检索 0 条 | 多半是没展开词形，用了词典形 |
| 查定义时结果全是义注、复注 | 正常，大部分名词解释在注疏里。但本文中也有部分解释，别据此断定本文没有 |
| 查案例时找不到本文 | 这才是问题。用 `dist` 的 `--book` 值收窄到 mūla 再搜 |
| 某段落取不到译文 | 该 channel 在该段落没有内容。如实报告 |
| 想按短语检索 | 平台的词组检索目前不可用（服务端 500）。把短语拆成词，分别展开词形再检索 |
| `get` 报 500 | 忘了 channel。`get` 缺省会带巴利原文的 channel，若你手动传了参数要确保 channel 在内 |
