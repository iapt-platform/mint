# related_paragraphs 段落关联体系 修复设计文档

> 状态：已评审；2026-03 对当前库（visuddhinanda_20260311）全量实测复核后修订，待实施
> 背景：2025-08 数据审计发现 related_paragraphs 存在多类系统性数据错误。
> 原始生成链路（VRI html 锚点 → cs6_para.csv → Laravel 导入）中的 csv 文件已丢失，
> 决定以 wbw_templates 为唯一数据源重新导出第一版数据，再用补丁文件修正历史问题。

---

## 1. 功能描述

通过一本巴利圣典书的段落号 `<book>-<para>` 查找与之存在注释关系的段落号。

注释关系（巴利三藏传统结构）：

```
mūla（原文） ← aṭṭhakathā（义注） ← ṭīkā（复注）
部分典籍为四层：mūla ← aṭṭhakathā ← mūlaṭīkā ← anuṭīkā（如 abhidhamma）
```

核心机制：相同 `book_name + cs_para` 的段落互为注释关系。

- `book_name`：SuttaCentral 风格的书名缩写（dn1、an5、kn14...）
- `cs_para`：该书内的段落编号。颗粒度比 VRI 段落（paragraph）粗：
  一对一（一段对一个 cs）或一对多（多个 VRI 段落同属一个 cs，且构成连续区间）
- 实测镜像判据：义注/复注段的标记即被注书的段落号——镜像型书对两侧同名章的
  max(cs_para) 相等（dn1 559=559、kn6 1289=1289、kn9 524=524、abhi5 918=918）
- 义注/复注文件的段落标记直接挂在**被注书的 book_name** 下（如义注文件的 `para4_dn1`
  表示"此段注释 dn1 的第 4 段"），由此实现跨文件对齐
- 义注开头的序言部分（Ganthārambhakathā）使用 `{book}_A` 命名（如 dn1_A），cs=0，
  属正常设计而非错误——`_A` 书不对应具体原文段落

## 2. 相关表及关联关系

```
wbw_templates                （数据源头，逐词表）
  book, paragraph, wid       含 .ctl. 控制标记（原 VRI html 的 <a name="..."> 锚点）
  │   标记格式：
  │   mūla 文件:    para4 / para4_dn1（4(style=paranum) 裸数字型实测已为 0 行，
  │                 按历史形态保留备注，导出器以实测词表为准）
  │   区间标记:      para151-152 / para151-152_sn4  （1 个 vri 段 ↔ 多个 cs，展开多行）
  │   缩写区间:      para292-3  = 292-293（M 补齐 N 的前缀）
  │   义注文件:      dn1_0 / dn1_1（{mula}_{cs}，义注段挂 mūla 名下）
  │   章节标记:      kn10_2 / an1_5（mūla 文件内的章界）
  ▼  （旧链路经 cs6_para.csv ——已丢失，废弃）
related_paragraphs           （本表，631,838 行）
  book, para                 VRI 文件号 1-217 + 段落号
  book_id ──────────────────▶ book_titles.sn
  cs_para, book_name         匹配键（本表核心索引 (book_name, cs_para)）
  ▼
book_titles (sn)             （book_id 的语义来源）
  book, paragraph, sn        一个 level=1（书）= 一个 sn，(book,paragraph) 唯一
  ▼                          （paragraph 即该书在 pali_texts 中的 level=1 段落）
pali_texts                   （全文）
  book, paragraph, level     标题层级树（章界推导的依据）
  uid ──────────────────────▶ tag_maps.anchor_id (table_name='pali_texts') ▶ tags
                              tag.name: pāḷi+mūla=原文 / aṭṭhakathā=义注 /
                              ṭīkā、mūlaṭīkā、anuṭīkā、abhinavaṭīkā、purāṇaṭīkā=复注
```

要点：
- `related_paragraphs.book` 是 VRI 版文件编号；一个文件可含多本书（多个 level=1），
  因此增加 `book_id`，一个 book_id 对应一个 level=1，即 book_titles.sn
- book_id 仅用于输出书名，**不参与**注释关系匹配（匹配只用 book_name+cs_para）
- 义注分册边界与 mūla 分册不对齐时，同一 book_id 内出现多个 book_name（如义注
  manorathapūraṇī 第二册 book_id=110 内同时有 an2、an3）是正常内容分布

## 3. 当前查询逻辑

`RelatedParagraphController::index`（输入 book+para）：

1. `related_paragraphs WHERE book=? AND para=? AND cs_para>0` 取首行得到
   (book_name, cs_para) 锚点；无锚点返回空集（注释体系书内约 2.6%，
   11,078/419,300 段；全库口径 6.6%，属正常）
2. `WHERE book_name=? AND cs_para=?` 全表取所有命中段落
3. 按 book_id 分组输出：每本书一条记录（book、book_id、cs6_para、para 列表）

其他消费方：`ParaInfoController`（段落信息附带 CS6 锚点）、
`UpgradeSystemCommentary`（按 book_name|cs_para 为单元遍历生成 AI 注释）。

## 4. 问题描述

### 4.1 用户最初描述

- 正常情况：一个 book_id 内只有一个 book_name 且 cs_para 单调递增（如 book=93）。
  现发现部分书不是：一个 book_id 内出现重复的 cs_para
- related_paragraphs 数据当年从 wbw_templates（进而来自 VRI html 锚点）导入，
  具体过程已不可考，cs6_para.csv 原始文件已丢失

### 4.2 审计确认的正确性判据（修正后的业务模型）

- 同一 `(book, book_name, cs_para)` 的段落必须构成**连续区间**
- cs 小幅回退（如 25→23）是义注"回指补充"体例（`para1012-3` 型标记），
  属合法结构，多区间命中是设计内行为（含跨文件多命中）
- 真正的错误是：cs 乱码值、章回绕导致的跨章重复、书边界污染平铺、标记丢失
- 注释链条语义对齐的实测判据：镜像型书对两侧同名章的 max(cs_para) 相等
  （dn1 559=559、kn6 1289=1289、kn9 524=524、abhi5 918=918）；两侧不等的书对
  （abhi3/4/6/7 系）即跨侧错位，需走 §6.2 的补丁后缀路径，不能只靠各自加后缀

### 4.3 审计发现的问题清单

| 编号 | 问题 | 规模 | 根因 |
|---|---|---|---|
| E1 | cs 乱码值（7,703~462,580；正常值域实测至 3,183） | 1,242 行 / 24 本 | 旧 csv 生成器解析无后缀区间标记 para7-10 产生废值（如 44022，book 98 para 573-585 实测 13 行全为 44022，wbw 标记正是 para7-10） |
| E2 | 章回绕：cs 从 1 重新计数 | 回绕点 430 个 / 70 本 | VRI 标记为章内局部编号，章界处重置，旧生成器照搬，未加区分。回绕点到最近真实标题（toc 非空）实测：±1 段内 86%、±5 段内 96.5%、±30 段内零失配。mūla 侧与义注侧回绕数不对齐（如 abhi7：mūla 64 次 = books 69-72 的 16+9+20+19，复注侧 books 174/176 各 12/13 次），跨章同名 cs 无法区分，注释串段 |
| E3 | kn10 家族命名错乱，apadāna 注释链断裂 | `book_name='kn10'` 0 行 | therā/therī（book 143/144）被赋 kn10_A/kn10_B 且归属互相矛盾；义注（book 107）cs 为乱码；wbw 中 `paraN_kn10` 标记 6,533 行（book 143 4,461 / book 144 2,072）在 related 中映射丢失（kn10 行数为 0） |
| E4 | 空 book_name | 112,965 行（17.9%）/ 60 本（60 个连续段，69 个 book_id） | book 2-68 等 nīti/语法/词典类书无 cs 标记体系，不参与注释关系，维持现状 |
| E5 | book_id=0 未赋值 | 354 行（201 本各 1-3 行）；book 189 审计时整本 1,572 行为 0，现已回填（1,570 行 book_id=230，仅书前页 2 行为 0） | 各文件 para 1-2 书前页（可接受）。sn=280/281 仍无 book_id 行：book 139 para≥925 整段归入 sn 157、book 214 para≥2162 整段归入 sn 276，随 book_id 层重跑修复。另 book_titles sn=230 title 误写 sādhuvilāsinī（与 sn=229 重名，实际应为 sīlakkhandhavaggaabhinavaṭīkā，已由 pali_texts level-1 标题证实） |
| E6 | 覆盖缺口 | 3,466 段无行 | 主体为缩写区间丢失（见 E7），其余为 wbw_templates 整段缺失 |
| E7 | **缩写区间丢失**（本次审计新发现） | 1,517 个标记段落 / 44 本：其中 1,292 段（85.2%）无 related 行，其余 225 段带行但 cs 全为乱码值（与 E1 同根） | VRI 缩写语法 `para292-3`（=292-293）旧生成器不识别：多数直接跳过，少数误解析为废值（如 `para32-3`→11,749、`para15-8`→44,058） |
| E8 | **书边界污染平铺** | kaṅkhāvitaraṇī 义注 1,499 行 cs 恒 75、ṭīkā 669 行 cs 恒 0 等 | 换书（book_id 切换）后无标记，旧生成器无限沿用上一个 cs 值 |
| E9 | 无标记书 | 16 本（book 8,15,16,25,37,39-42,44,45,47,48,56,61,129）related 有行但 wbw_templates 零标记 | VRI 源文件无锚点。其中 book 129（theragāthā-aṭṭhakathā 后半）等存在真实注释关系，需外部手段补齐 |
| E10 | 自造名体系 | NK（book 1，namakkāra 自注释对）、subo（book 10↔11）、dict（book 21↔9） | 非 SC 体系书对的自造 album 名，连接各自 mūla↔注释。架构正确（与 dn1/dn1_A 同构），保留不动；仅其中的乱码/缩写问题随全局规则修复 |

## 5. 全库书况初步分析表

判定口径（book 级信号会标注到该文件的全部 sn）：

- **合格**：无任何问题信号
- **合格（无注释体系）**：全书空 book_name（nīti/语法/词典类），不参与注释关系，维持现状
- **乱码 n 行**：存在 cs>5000 的乱码值 → 新导出器自动修复
- **缩写丢失 n 处**：缩写区间标记段落无行 → 新导出器自动修复
- **章回绕 n 次**：cs 重置计数 → 章后缀方案处理（注：含少量合法多轮注释结构
  如 NK 的 ṭīkā 两轮注释，处理时用标题层级甄别）
- **无标记污染**：cs 值种类极少但行数巨大（书边界沿用平铺）→ 导出器留空 + LLM 补丁
- **全书无标记**：wbw_templates 无任何 para 标记但 related 有行 → LLM 补丁或维持现状
- **无 book_id 行**：sn 存在但无任何 book_id 归属行（行均被归入同文件前一册）

注：表内「章回绕 n 次」为 book 级信号（标注到该文件的全部 sn），按本去重合计 366 次 / 58 本，
与 E2 的机械规则 430 个 / 70 本之差来自 12 本乱码书（9、96、107、108、109、116、172、175、
179、190、201、205）——其重置多为乱码值→1 的伪回绕，不计入表，实施时统一以 E2 口径为准。

汇总：合格 69 本；合格（无注释体系）51 本；不合格 160 本；无 book_id 行 2 本（sn 280、281）。

| sn | book | 书名 | 行数 | 状况 |
|---|---|---|---|---|
| 1 | 1 | namakkārapāḷi | 157 | 章回绕3次 |
| 2 | 1 | namakkāraṭīkā | 828 | 章回绕3次 |
| 3 | 2 | mahāpaṇāmapāṭha | 426 | 合格（无注释体系，维持现状） |
| 4 | 2 | tigumbacetiya thomanā | 51 | 合格（无注释体系，维持现状） |
| 5 | 2 | vāsamālinīkya | 367 | 合格（无注释体系，维持现状） |
| 6 | 3 | lakkhaṇāto | 315 | 合格（无注释体系，维持现状） |
| 7 | 4 | suttavandanā | 596 | 合格（无注释体系，维持现状） |
| 8 | 5 | jinālaṅkāra | 1024 | 合格（无注释体系，维持现状） |
| 9 | 6 | kamalāñjali | 301 | 合格（无注释体系，维持现状） |
| 10 | 7 | pajjamadhu | 525 | 合格（无注释体系，维持现状） |
| 11 | 8 | buddhaguṇagāthāvalī | 2457 | 全书无标记 |
| 12 | 9 | abhidhānappadīpikāṭīkā | 3108 | 乱码31行 |
| 13 | 10 | subodhālaṅkāro | 1319 | 合格 |
| 14 | 11 | subodhālaṅkāraṭīkā | 2937 | 乱码96行; 缩写丢失2处 |
| 15 | 12 | bālāvatāra | 846 | 合格（无注释体系，维持现状） |
| 16 | 13 | moggallānasuttapāṭho | 1103 | 合格（无注释体系，维持现状） |
| 17 | 13 | moggallānabyākaraṇaṃ | 2158 | 合格（无注释体系，维持现状） |
| 18 | 14 | kaccāyanabyākaraṇaṃ | 713 | 合格（无注释体系，维持现状） |
| 19 | 14 | mahākaccāyanasaddāpāṭha | 2772 | 合格（无注释体系，维持现状） |
| 20 | 15 | saddanītippakaraṇaṃ (padamālā) | 2979 | 全书无标记 |
| 21 | 16 | saddanītippakaraṇaṃ (dhātumālā) | 3400 | 全书无标记 |
| 22 | 17 | padarūpasiddhi | 4054 | 合格（无注释体系，维持现状） |
| 23 | 18 | moggallāna-pañcikā-ṭīkā | 1578 | 合格（无注释体系，维持现状） |
| 24 | 19 | payogasiddhipāḷi | 2492 | 合格（无注释体系，维持现状） |
| 25 | 20 | vuttodayaṃ | 317 | 合格（无注释体系，维持现状） |
| 26 | 21 | abhidhānappadīpikā | 3962 | 章回绕1次 |
| 27 | 22 | niruttidīpanīpāṭha | 6143 | 合格（无注释体系，维持现状） |
| 28 | 23 | paramatthadīpanī | 1497 | 合格 |
| 29 | 24 | anudīpanīpāṭha | 441 | 合格 |
| 30 | 25 | paṭṭhānuddesa dīpanīpāṭha | 248 | 全书无标记 |
| 31 | 26 | caturārakkhadīpanī | 1959 | 合格（无注释体系，维持现状） |
| 32 | 27 | kavidappaṇanīti | 1346 | 合格（无注释体系，维持现状） |
| 33 | 28 | nītimañjarī | 242 | 合格（无注释体系，维持现状） |
| 34 | 29 | dhammanīti | 1332 | 合格（无注释体系，维持现状） |
| 35 | 30 | mahārahanīti | 1282 | 合格（无注释体系，维持现状） |
| 36 | 31 | lokanīti | 875 | 合格（无注释体系，维持现状） |
| 37 | 32 | suttantanīti | 132 | 合格（无注释体系，维持现状） |
| 38 | 32 | vasalasutta | 133 | 合格（无注释体系，维持现状） |
| 39 | 33 | sūrassatīnīti | 544 | 合格（无注释体系，维持现状） |
| 40 | 34 | cāṇakyanītipāḷi | 551 | 合格（无注释体系，维持现状） |
| 41 | 35 | naradakkhadīpanī | 1846 | 合格（无注释体系，维持现状） |
| 42 | 36 | rasavāhinī | 2456 | 合格（无注释体系，维持现状） |
| 43 | 37 | sīmavisodhanī | 432 | 全书无标记 |
| 44 | 38 | vessantarāgīti | 2612 | 合格（无注释体系，维持现状） |
| 45 | 39 | (saṅgayana-puccha vissajjanā) dī | 347 | 全书无标记 |
| 46 | 40 | (saṅgayana-puccha vissajjanā) ma | 686 | 全书无标记 |
| 47 | 41 | (saṅgayana-puccha vissajjanā) sa | 1254 | 全书无标记 |
| 48 | 42 | (saṅgayana-puccha vissajjanā) aṅ | 905 | 全书无标记 |
| 49 | 43 | (saṅgayana-puccha vissajjanā) vi | 1553 | 合格（无注释体系，维持现状） |
| 50 | 44 | (saṅgayana-puccha vissajjanā) ab | 295 | 全书无标记 |
| 51 | 45 | (saṅgayana-puccha vissajjanā) aṭ | 502 | 全书无标记 |
| 52 | 46 | milidaṭīkā | 1240 | 无标记污染(cs值仅20个/1240行) |
| 53 | 47 | padamañjarī | 1640 | 全书无标记 |
| 54 | 48 | padasādhanaṃ | 1095 | 全书无标记 |
| 55 | 49 | saddabindu pakaraṇaṃ | 83 | 合格（无注释体系，维持现状） |
| 56 | 50 | kaccāyana  dhātu mañjūsā | 494 | 合格（无注释体系，维持现状） |
| 57 | 51 | samantakūṭavaṇṇanā | 3057 | 合格（无注释体系，维持现状） |
| 58 | 52 | moggallāna vuttivivaraṇapañcikā. | 1703 | 合格（无注释体系，维持现状） |
| 59 | 53 | thupavaṃsa | 709 | 合格（无注释体系，维持现状） |
| 60 | 54 | dāṭhāvaṃsa | 1642 | 合格（无注释体系，维持现状） |
| 61 | 55 | dhātupāṭha  vilāsiniyā | 118 | 合格（无注释体系，维持现状） |
| 62 | 56 | dhātuvaṃsa | 491 | 全书无标记 |
| 63 | 57 | hatthavanagallavihāra  vaṃso | 364 | 合格（无注释体系，维持现状） |
| 64 | 58 | jinacaritaya | 1649 | 合格（无注释体系，维持现状） |
| 65 | 59 | jinavaṃsadīpaṃ | 9907 | 合格（无注释体系，维持现状） |
| 66 | 60 | telakaṭāhagāthā | 501 | 合格（无注释体系，维持现状） |
| 67 | 61 | cūḷaganthavaṃsapāḷi | 151 | 全书无标记 |
| 68 | 62 | sāsanavaṃsappadīpikā | 970 | 合格（无注释体系，维持现状） |
| 69 | 63 | mahāvaṃsapāḷi | 15943 | 合格（无注释体系，维持现状） |
| 70 | 64 | visuddhimagga | 2239 | 合格 |
| 71 | 65 | visuddhimagga | 2191 | 合格 |
| 72 | 66 | visuddhimagga-mahāṭīkā | 1612 | 合格 |
| 73 | 67 | visuddhimagga-mahāṭīkā | 1850 | 合格 |
| 74 | 68 | visuddhimagga nidānakathā | 2700 | 合格（无注释体系，维持现状） |
| 75 | 69 | paṭṭhānapāḷi | 4239 | 章回绕16次 |
| 76 | 70 | paṭṭhānapāḷi | 6002 | 章回绕9次 |
| 77 | 71 | paṭṭhānapāḷi | 8044 | 章回绕20次 |
| 78 | 72 | paṭṭhānapāḷi | 5167 | 章回绕19次 |
| 79 | 73 | dhammasaṅgaṇīpāḷi | 2434 | 章回绕2次 |
| 80 | 74 | vibhaṅgapāḷi | 3425 | 合格 |
| 81 | 75 | dhātukathāpāḷi | 671 | 合格 |
| 82 | 76 | puggalapaññattipāḷi | 559 | 章回绕1次 |
| 83 | 77 | kathāvatthupāḷi | 3458 | 合格 |
| 84 | 78 | yamakapāḷi | 4772 | 章回绕4次 |
| 85 | 79 | yamakapāḷi | 3857 | 章回绕2次 |
| 86 | 80 | yamakapāḷi | 5584 | 章回绕1次 |
| 87 | 81 | paṭṭhānapāḷi | 4299 | 章回绕4次 |
| 88 | 82 | dasakanipātapāḷi | 3139 | 合格 |
| 89 | 83 | ekādasakanipātapāḷi | 1887 | 合格 |
| 90 | 84 | ekakanipātapāḷi | 955 | 合格 |
| 91 | 85 | dukanipātapāḷi | 483 | 合格 |
| 92 | 86 | tikanipātapāḷi | 1936 | 合格 |
| 93 | 87 | catukkanipātapāḷi | 3437 | 合格 |
| 94 | 88 | pañcakanipātapāḷi | 16264 | 合格 |
| 95 | 89 | chakkanipātapāḷi | 2375 | 合格 |
| 96 | 90 | sattakanipātapāḷi | 14829 | 合格 |
| 97 | 91 | aṭṭhakanipātapāḷi | 2602 | 合格 |
| 98 | 92 | navakanipātapāḷi | 1502 | 合格 |
| 99 | 93 | dīghanikāyapāḷi | 1072 | 合格 |
| 100 | 94 | dīghanikāyapāḷi | 1557 | 合格 |
| 101 | 95 | dīghanikāyapāḷi | 1908 | 合格 |
| 102 | 96 | dhammasaṅgaṇī-aṭṭhakathā | 3548 | 乱码3行; 缩写丢失2处 |
| 103 | 97 | vibhaṅga-aṭṭhakathā | 2420 | 缩写丢失2处 |
| 104 | 98 | dhātukathā-aṭṭhakathā | 169 | 章回绕17次 |
| 105 | 98 | puggalapaññatti-aṭṭhakathā | 310 | 章回绕17次 |
| 106 | 98 | kathāvatthu-aṭṭhakathā | 2713 | 乱码13行 |
| 107 | 98 | yamakappakaraṇa-aṭṭhakathā | 9000 | 乱码26行 |
| 108 | 98 | paṭṭhānappakaraṇa-aṭṭhakathā | 5394 | 乱码1行 |
| 109 | 99 | manorathapūraṇī | 2076 | 合格 |
| 110 | 100 | manorathapūraṇī | 518 | 缩写丢失2处 |
| 111 | 100 | manorathapūraṇī | 816 | 缩写丢失2处 |
| 112 | 100 | manorathapūraṇī | 2402 | 缩写丢失2处 |
| 113 | 101 | manorathapūraṇī | 601 | 章回绕1次 |
| 114 | 101 | manorathapūraṇī | 343 | 章回绕1次 |
| 115 | 101 | manorathapūraṇī | 216 | 章回绕1次 |
| 116 | 102 | manorathapūraṇī | 306 | 章回绕1次 |
| 117 | 102 | manorathapūraṇī | 174 | 章回绕1次 |
| 118 | 102 | manorathapūraṇī | 363 | 章回绕1次 |
| 119 | 102 | manorathapūraṇī | 105 | 章回绕1次 |
| 120 | 103 | sumaṅgalavilāsinī | 1974 | 合格 |
| 121 | 104 | sumaṅgalavilāsinī | 1729 | 缩写丢失1处 |
| 122 | 105 | sumaṅgalavilāsinī | 1129 | 合格 |
| 123 | 106 | therīgāthā-aṭṭhakathā | 4546 | 合格 |
| 124 | 107 | apadāna-aṭṭhakathā | 4094 | 乱码47行; 缩写丢失22处 |
| 125 | 108 | buddhavaṃsa-aṭṭhakathā | 4192 | 乱码80行 |
| 126 | 109 | cariyāpiṭaka-aṭṭhakathā | 2134 | 乱码80行; 缩写丢失6处 |
| 127 | 110 | jātaka-aṭṭhakathā | 3114 | 合格 |
| 128 | 111 | jātaka-aṭṭhakathā | 2995 | 章回绕1次 |
| 129 | 112 | jātaka-aṭṭhakathā | 4889 | 章回绕5次 |
| 130 | 113 | jātaka-aṭṭhakathā | 6020 | 乱码21行 |
| 131 | 113 | jātaka-aṭṭhakathā | 1691 | 章回绕5次 |
| 132 | 114 | jātaka-aṭṭhakathā | 5079 | 章回绕4次 |
| 133 | 115 | jātaka-aṭṭhakathā | 3686 | 合格 |
| 134 | 116 | khuddakapāṭha-aṭṭhakathā | 1233 | 乱码26行 |
| 135 | 117 | jātaka-aṭṭhakathā | 6461 | 合格 |
| 136 | 118 | mahāniddesa-aṭṭhakathā | 1831 | 合格 |
| 137 | 119 | cūḷaniddesa-aṭṭhakathā | 763 | 乱码18行; 缩写丢失1处 |
| 138 | 120 | paṭisambhidāmagga-aṭṭhakathā | 2716 | 章回绕3次; 缩写丢失1处 |
| 139 | 121 | nettippakaraṇa-aṭṭhakathā | 1094 | 章回绕1次 |
| 140 | 122 | dhammapada-aṭṭhakathā | 5229 | 章回绕1次 |
| 141 | 123 | udāna-aṭṭhakathā | 1686 | 章回绕1次 |
| 142 | 124 | itivuttaka-aṭṭhakathā | 1764 | 乱码7行 |
| 143 | 125 | suttanipāta-aṭṭhakathā | 2503 | 乱码1行; 缩写丢失114处 |
| 144 | 126 | vimānavatthu-aṭṭhakathā | 3971 | 章回绕1次; 缩写丢失36处 |
| 145 | 127 | petavatthu-aṭṭhakathā | 3279 | 章回绕1次; 缩写丢失46处 |
| 146 | 128 | theragāthā-aṭṭhakathā | 5388 | 缩写丢失2处 |
| 147 | 129 | theragāthā-aṭṭhakathā | 6603 | 全书无标记 |
| 148 | 130 | papañcasūdanī | 2904 | 章回绕1次; 缩写丢失2处 |
| 149 | 131 | papañcasūdanī | 1367 | 合格 |
| 150 | 132 | papañcasūdanī | 1103 | 缩写丢失1处 |
| 151 | 133 | sāratthappakāsinī | 1478 | 合格 |
| 152 | 134 | sāratthappakāsinī | 1226 | 合格 |
| 153 | 135 | sāratthappakāsinī | 2848 | 合格 |
| 154 | 136 | sāratthappakāsinī | 1860 | 合格 |
| 155 | 137 | sāratthappakāsinī | 2838 | 章回绕5次; 缩写丢失9处 |
| 156 | 138 | samantapāsādikā | 2803 | 缩写丢失26处 |
| 157 | 139 | samantapāsādikā | 1347 | 缩写丢失32处 |
| 158 | 140 | samantapāsādikā | 942 | 乱码6行; 缩写丢失6处 |
| 159 | 141 | samantapāsādikā | 668 | 合格 |
| 160 | 142 | samantapāsādikā | 715 | 缩写丢失1处 |
| 161 | 143 | therāpadānapāḷi | 14644 | 章回绕41次 |
| 162 | 144 | therāpadānapāḷi | 6531 | 章回绕17次 |
| 163 | 144 | therīapadānapāḷi | 4140 | 章回绕17次 |
| 164 | 145 | buddhavaṃsapāḷi | 3280 | 章回绕28次 |
| 165 | 146 | cariyāpiṭakapāḷi | 1197 | 章回绕2次 |
| 166 | 147 | jātakapāḷi | 11526 | 章回绕5次 |
| 167 | 148 | jātakapāḷi | 10504 | 章回绕15次 |
| 168 | 149 | mahāniddesapāḷi | 2925 | 合格 |
| 169 | 150 | cūḷaniddesapāḷi | 2769 | 章回绕1次 |
| 170 | 151 | paṭisambhidāmaggapāḷi | 2042 | 章回绕3次 |
| 171 | 152 | milindapañhapāḷi | 2491 | 章回绕22次 |
| 172 | 153 | nettippakaraṇapāḷi | 1622 | 章回绕1次 |
| 173 | 154 | khuddakapāṭhapāḷi | 274 | 无标记污染(cs值仅19个/274行); 章回绕6次 |
| 174 | 155 | peṭakopadesapāḷi | 1215 | 合格 |
| 175 | 156 | dhammapadapāḷi | 1392 | 合格 |
| 176 | 157 | udānapāḷi | 963 | 合格 |
| 177 | 158 | itivuttakapāḷi | 1192 | 合格 |
| 178 | 159 | suttanipātapāḷi | 4049 | 合格 |
| 179 | 160 | vimānavatthupāḷi | 3277 | 合格 |
| 180 | 161 | petavatthupāḷi | 2653 | 合格 |
| 181 | 162 | theragāthāpāḷi | 4667 | 合格 |
| 182 | 163 | therīgāthāpāḷi | 1783 | 合格 |
| 183 | 164 | majjhimanikāyapāḷi | 1795 | 合格 |
| 184 | 165 | majjhimanikāyapāḷi | 1688 | 合格 |
| 185 | 166 | majjhimanikāyapāḷi | 1461 | 合格 |
| 186 | 167 | saṃyuttanikāyapāḷi | 3496 | 合格 |
| 187 | 168 | saṃyuttanikāyapāḷi | 1425 | 合格 |
| 188 | 169 | saṃyuttanikāyapāḷi | 3163 | 合格 |
| 189 | 170 | saṃyuttanikāyapāḷi | 2223 | 合格 |
| 190 | 171 | saṃyuttanikāyapāḷi | 5898 | 合格 |
| 191 | 172 | mūlaṭīkā | 1364 | 乱码7行; 缩写丢失1处 |
| 192 | 173 | mūlaṭīkā | 1210 | 章回绕1次; 缩写丢失7处 |
| 193 | 173 | anuṭīkā | 1103 | 章回绕1次; 缩写丢失7处 |
| 194 | 174 | mūlaṭīkā | 96 | 章回绕12次; 缩写丢失2处 |
| 195 | 174 | mūlaṭīkā | 101 | 乱码1行; 缩写丢失2处 |
| 196 | 174 | mūlaṭīkā | 1309 | 乱码8行; 缩写丢失2处 |
| 197 | 174 | mūlaṭīkā | 6204 | 乱码29行; 缩写丢失2处 |
| 198 | 174 | mūlaṭīkā | 3192 | 乱码1行; 缩写丢失2处 |
| 199 | 175 | anuṭīkā | 1273 | 乱码9行 |
| 200 | 176 | anuṭīkā | 113 | 章回绕13次 |
| 201 | 176 | anuṭīkā | 96 | 乱码1行 |
| 202 | 176 | anuṭīkā | 1338 | 乱码8行 |
| 203 | 176 | anuṭīkā | 6905 | 乱码33行 |
| 204 | 176 | anuṭīkā | 3446 | 乱码1行 |
| 205 | 177 | abhidhammāvatāro | 4803 | 合格 |
| 206 | 177 | nāmarūpaparicchedo | 5822 | 合格（无注释体系，维持现状） |
| 207 | 177 | paramatthavinicchayo | 3645 | 合格（无注释体系，维持现状） |
| 208 | 177 | saccasaṅkhepo | 1228 | 合格（无注释体系，维持现状） |
| 209 | 178 | abhidhammatthasaṅgaho | 873 | 章回绕17次 |
| 210 | 178 | abhidhammatthavibhāvinīṭīkā | 964 | 章回绕17次 |
| 211 | 179 | abhidhammāvatāra-purāṇaṭīkā | 879 | 乱码9行; 缩写丢失418处 |
| 212 | 179 | abhidhammāvatāra-abhinavaṭīkā | 1523 | 乱码46行; 缩写丢失418处 |
| 213 | 180 | abhidhammamātikāpāḷi | 1219 | 合格（无注释体系，维持现状） |
| 214 | 180 | mohavicchedanī | 1606 | 合格（无注释体系，维持现状） |
| 215 | 181 | ekakanipāta-ṭīkā | 1449 | 合格 |
| 216 | 182 | dukanipāta-ṭīkā | 1028 | 章回绕3次; 缩写丢失10处 |
| 217 | 182 | tikanipāta-ṭīkā | 999 | 章回绕3次; 缩写丢失10处 |
| 218 | 182 | catukkanipāta-ṭīkā | 1299 | 章回绕3次; 缩写丢失10处 |
| 219 | 183 | pañcakanipāta-ṭīkā | 1108 | 章回绕4次; 缩写丢失10处 |
| 220 | 183 | chakkanipāta-ṭīkā | 583 | 章回绕4次; 缩写丢失10处 |
| 221 | 183 | sattakanipāta-ṭīkā | 401 | 章回绕4次; 缩写丢失10处 |
| 222 | 184 | aṭṭhakanipāta-ṭīkā | 2130 | 章回绕6次; 缩写丢失1处 |
| 223 | 184 | navakanipāta-ṭīkā | 272 | 章回绕6次; 缩写丢失1处 |
| 224 | 184 | dasakanipāta-ṭīkā | 3805 | 章回绕6次; 缩写丢失1处 |
| 225 | 184 | ekādasakanipāta-ṭīkā | 147 | 章回绕6次; 缩写丢失1处 |
| 226 | 185 | līnatthappakāsanā | 1586 | 缩写丢失17处 |
| 227 | 186 | līnatthappakāsanā | 1507 | 缩写丢失1处 |
| 228 | 187 | līnatthappakāsanā | 1130 | 合格 |
| 229 | 188 | sādhuvilāsinī | 1779 | 合格 |
| 230 | 189 | sādhuvilāsinī | 1570 | 缩写丢失10处 |
| 231 | 190 | nettippakaraṇa-ṭīkā | 762 | 乱码1行 |
| 232 | 191 | nettivibhāvinī | 1668 | 章回绕1次 |
| 233 | 192 | līnatthappakāsanā | 2395 | 章回绕2次; 缩写丢失3处 |
| 234 | 193 | līnatthappakāsanā | 934 | 缩写丢失1处 |
| 235 | 194 | līnatthappakāsanā | 854 | 缩写丢失1处 |
| 236 | 195 | līnatthappakāsanā | 1707 | 章回绕1次 |
| 237 | 196 | līnatthappakāsanā | 1326 | 合格 |
| 238 | 197 | līnatthappakāsanā | 3274 | 合格 |
| 239 | 198 | līnatthappakāsanā | 1926 | 合格 |
| 240 | 199 | līnatthappakāsanā | 2835 | 章回绕7次; 缩写丢失7处 |
| 241 | 200 | vinayavinicchayo | 10174 | 章回绕1次 |
| 242 | 200 | uttaravinicchayo | 3106 | 章回绕1次 |
| 243 | 201 | vinayavinicchaya-ṭīkā | 3812 | 乱码450行; 缩写丢失256处 |
| 244 | 201 | uttaravinicchaya-ṭīkā | 771 | 乱码2行; 缩写丢失256处 |
| 245 | 202 | pācityādiyojanā | 2987 | 章回绕3次; 缩写丢失10处 |
| 246 | 203 | khuddasikkhā | 1617 | 章回绕1次; 缩写丢失84处 |
| 247 | 203 | khuddasikkhā | 630 | 乱码33行; 缩写丢失84处 |
| 248 | 203 | khuddasikkhā | 631 | 乱码35行; 缩写丢失84处 |
| 249 | 203 | mūlasikkhā | 376 | 章回绕1次; 缩写丢失84处 |
| 250 | 203 | mūlasikkhā | 148 | 乱码13行; 缩写丢失84处 |
| 251 | 204 | sāratthadīpanī-ṭīkā | 1657 | 合格 |
| 252 | 205 | sāratthadīpanī-ṭīkā | 1924 | 乱码5行; 缩写丢失3处 |
| 253 | 206 | sāratthadīpanī-ṭīkā | 1008 | 章回绕3次; 缩写丢失4处 |
| 254 | 206 | sāratthadīpanī-ṭīkā | 1148 | 章回绕3次; 缩写丢失4处 |
| 255 | 206 | sāratthadīpanī-ṭīkā | 447 | 章回绕3次; 缩写丢失4处 |
| 256 | 206 | sāratthadīpanī-ṭīkā | 257 | 章回绕3次; 缩写丢失4处 |
| 257 | 207 | pātimokkhapāḷi | 521 | 章回绕12次 |
| 258 | 207 | pātimokkhapāḷi | 691 | 章回绕12次 |
| 259 | 207 | kaṅkhāvitaraṇī | 1499 | 无标记污染(cs值仅1个/1499行); 章回绕12次 |
| 260 | 208 | vinayasaṅgaha-aṭṭhakathā | 1671 | 章回绕1次 |
| 261 | 209 | vajirabuddhi-ṭīkā | 163 | 章回绕1次; 缩写丢失85处 |
| 262 | 209 | vajirabuddhi-ṭīkā | 779 | 乱码53行; 缩写丢失85处 |
| 263 | 209 | vajirabuddhi-ṭīkā | 719 | 乱码9行; 缩写丢失85处 |
| 264 | 209 | vajirabuddhi-ṭīkā | 486 | 乱码5行; 缩写丢失85处 |
| 265 | 209 | vajirabuddhi-ṭīkā | 189 | 章回绕1次; 缩写丢失85处 |
| 266 | 209 | vajirabuddhi-ṭīkā | 224 | 乱码3行; 缩写丢失85处 |
| 267 | 210 | vimativinodanī-ṭīkā | 1709 | 乱码6行; 缩写丢失46处 |
| 268 | 210 | vimativinodanī-ṭīkā | 707 | 章回绕4次; 缩写丢失46处 |
| 269 | 210 | vimativinodanī-ṭīkā | 759 | 乱码18行; 缩写丢失46处 |
| 270 | 210 | vimativinodanī-ṭīkā | 338 | 章回绕4次; 缩写丢失46处 |
| 271 | 210 | vimativinodanī-ṭīkā | 386 | 章回绕4次; 缩写丢失46处 |
| 272 | 211 | vinayālaṅkāra-ṭīkā | 2411 | 章回绕2次; 缩写丢失1处 |
| 273 | 212 | kaṅkhāvitaraṇī | 669 | 无标记污染(cs值仅1个/669行) |
| 274 | 212 | kaṅkhāvitaraṇī | 1847 | 无标记污染(cs值仅19个/1847行) |
| 275 | 213 | vinayapitaka | 2475 | 合格 |
| 276 | 214 | vinayapitaka | 3688 | 合格 |
| 277 | 215 | vinayapitaka | 2757 | 合格 |
| 278 | 216 | vinayapitaka | 2819 | 合格 |
| 279 | 217 | vinayapitaka | 3643 | 合格 |
| 280 | 139 | samantapāsādikā | 0 | 缩写丢失32处 |
| 281 | 214 | (VN)Bhikkhunīvibhaṅgo | 0 | 无 book_id 行 |

## 6. 解决方案

### 6.1 总体架构

```
wbw_templates（唯一数据源，含全部 .ctl. 标记）
   │
   ▼  ①导出器（Artisan command，确定性重放，宁空勿错）
第一版 related_paragraphs + issues.json（补丁工作清单）
   │
   ▼  ②补丁文件（人工/LLM 生成的修正数据，进代码仓库评审）
   │
   ▼  ③更新 command（幂等，--dry-run，按补丁执行 UPDATE）
修复后的 related_paragraphs
```

关键决策记录：
- **cs6_para.csv 废弃**，不再寻找；一切从 wbw_templates 重放
- **维持 VRI 编号方案**（章内局部编号），SC 数据不采用（SC 层级树与加后缀方案无本质
  区别，且 SC 为 Mahāsaṅgīti 独立校勘本，文本与 VRI 不对齐，无法直接落到 VRI 段落）
- **回绕处理（分两类）**：
  - 镜像型书对（义注/复注 cs 与 mūla 同空间，实测判据见 §4.2）：章界处 book_name 加后缀
    （优先用 VRI 标题自带编号如 `abhi7_1-1`，无编号用章序号 `kn14_18`）；章界来源为
    pali_texts 真实标题（toc 非空；level=100 是正文节点不算标题）。实测回绕点 ±1 段内
    命中 86%、±5 段内 96.5%、±30 段内零失配；两侧按同一套后缀对齐。
  - abhidhamma 系不对齐书对（abhi3/4/6/7 等，义注/复注自有编号，跨侧回绕数不一致，
    如 abhi7 mūla 64 次 vs 复注 12/13 次）：自动后缀无法保证注释链条对齐，改为
    **补丁 rename_book 显式加后缀**（§6.5），以 mūla←义注←复注链条对齐为约束；
    对齐信息优先取 VRI 标题自带编号，不足时由 LLM 辅助判定（§6.4），人工复核。
- **页码标记**：P/M/V/T/O 各 0-5 系列（实测 26 家族 165,907 行）一律忽略，本轮不处理。
- **无标记书**：LLM 为主体做内部段落归属（95% 工作量在章节内部的段落归属划分，
  程序无法完成——需要读懂巴利文），标题对齐与校验为辅助
- **查不到优于查到错的**：导出器在无法确定处留空（不输出行）并记 issue，
  由补丁填补；不沿用污染值

### 6.2 导出器处理策略矩阵

**标记语法层**

| 输入 | 处理 |
|---|---|
| `paraN` / `paraN_book` | 输出 cs=N，更新书上下文 |
| `paraN-M`（M 位数≥N） | 完整区间，展开 N..M 多行 |
| `paraN-M`（M 位数<N，补齐后>N） | 缩写区间，前缀补齐展开（para292-3→292,293） |
| `paraN-M_book`（缩写+后缀组合，实测 989 行，如 `para1008-9_sn5`） | 先按缩写规则展开 N..M，再以后缀定 book_name |
| `paraN-M-K...` | 链式首尾法展开 N..tail（para292-3-6→292..296）；实测仅 4 行，旧数据已正确展开（book 189 para 827 → 292-296） |
| `paraN-`（M 空，全库仅 1 处：book 11 para 1577） | 留空 + issue(`truncated_marker`)，人工定 |
| 解析值超值域保护阈值 | 拒绝输出 + issue(`cs_out_of_range`)，防乱码 |
| 页码/卷标 `P/M/V/T/O` × 0-5（实测 26 家族 165,907 行） | 忽略，本轮不处理 |
| 纯书名标记 `dn1`、`kn10` | 书上下文事件，不输出行 |

**段落层**

| 输入 | 处理 |
|---|---|
| 延续段（同书内有前一标记） | 沿用；区间沿用则整区间展开（含巨区间 844 行场景） |
| 书边界（book_id 切换）后的无标记段 | **留空 + issue(`no_marker_after_book_switch`)** ⭐ 防污染核心规则 |
| 文件开头段（para 1-2） | cs=0、book_id=0（维持现状语义） |
| wbw_templates 整段无行 | 自然无输出 + issue(`missing_wbw`) |

**书上下文层**

| 输入 | 处理 |
|---|---|
| `paraN_book` 后缀 | book_name = 后缀 |
| 无后缀标记 | 继承当前 book_name；文件开头继承不到 → 留空 + issue |
| `{mula}_{0}`（义注文件） | 进入 `{mula}_A` 序言模式（重现 dn1_A 正确先例） |
| `{mula}_{N>0}`（义注文件） | 退出序言模式，book_name=mūla 名 |
| `{book}_{N}`（mūla 文件） | 章切换事件，触发章后缀上下文 |
| NK/subo/dict 等自造名 | 由书级配置表提供（保兼容，不重新发明）；books 107/143/144 的 kn10 家族导出名同样由书级配置表决定（补丁 old 值必须与之匹配，见 §6.5） |

文件类型（义注 vs mūla）用 tag（aṭṭhakathā/ṭīkā vs mūla/pāḷi）消解
`{name}_{N}` 的同形歧义。

**章回绕层**

| 输入 | 处理 |
|---|---|
| cs 回 1 且附近有 para1 标记 | 章回绕：以回绕点为原点在 ±N 段窗口内找最近真实标题（toc 非空；level=100 的正文节点不算标题）定章界，输出加后缀的 book_name。实测 ±1 段内命中 86%、±5 段内 96.5%、±30 段内零失配；窗口大小、层级阈值、多候选取法在导出器内定死 |
| 镜像型书对 | 两侧用同一套后缀（§6.1），出口断言：同名章两侧 max(cs_para) 一致 |
| abhidhamma 系不对齐书对 | 自动后缀不保证链条对齐，改为补丁 rename_book 显式加后缀，链条对齐优先 |
| 回绕点偏移超窗口（实测仅 5 处，偏移 8-30 段） | 留空 + issue(`wrap_unmatched_title`)，补丁人工定 |
| cs 小幅回退（不回到 1） | 原样输出（回指补充，合法），低优先级 issue 仅统计 |

**book_id 层**：按 book_titles 现行逻辑匹配（book+paragraph ≥ level=1 段落）重跑；
sn 280/281 的归错（book 139 para≥925、book 214 para≥2162 整段归入前册）随重跑修复；
book 189 已完成回填（仅 2 行书前页为 0）。book_titles sn=230 的 title 错误另开小补丁。

### 6.3 导出器输出物

1. 第一版 related_paragraphs 数据（含章后缀、区间展开、留空处无行）
2. issues.json：`{ type, book, para_from, para_to, detail, suggested_action }`，
   按类型汇总即为补丁工作清单
3. 导出报告：验收断言结果 + 与现库 diff 摘要

验收断言（任一失败则导出失败）：

- 区间标记展开行数 == m_end − n + 1（全量）
- 无 cs 超值域行（乱码绝迹）
- 缩写区间零丢失
- 每个 (book_id, book_name) 内 para 连续性断开处（排除 cs=0 书前页）恰好与 issue 一一对应
- 跨侧对齐：镜像型书对同名章两侧 max(cs_para) 一致（实测 dn1 559=559、kn6 1289=1289、
  kn9 524=524、abhi5 918=918）；abhidhamma 系书对在补丁后缀后人工抽检链条

### 6.4 LLM 修复无标记书（kaṅkhāvitaraṇī 系等）

适用：E8 污染区 + E9 无标记书（kaṅkhāvitaraṇī 义注及两本 ṭīkā 约 3,200 段、
abhi ṭīkā、vin yojanā 系等，合计约 15,000-20,000 段），以及 abhidhamma 系
补丁后缀的对齐辅助判定（§6.1）。

LLM 使用原则（规则优先，合理使用）：LLM 只承担需要读懂巴利文的语义判定——
E8/E9 的段落归属与 abhi 系跨侧章界对齐；凡可由标记、标题层级、引用锚、单调性
规则程序判定的，一律不进 LLM。规模估算：40-90 段/次调用 → 全量约 250-500 次
调用，双模型独立跑翻倍；成本与时长按此量级排期，试点先行。

流程（LLM 为主体，承担 95% 的章节内部段落归属；规则只做校验）：

1. 输入构造（程序）：每个条款区间一次调用——区间全文（40-90 段）、mūla 对应
   条款文本与相邻条款开头、已判定的前一区间尾部、内引用锚点
   （ṭīkā 自带 `(pārā. aṭṭha. 1.52)` 型引用可程序化提取，作交叉验证）
2. LLM 逐段输出归属 + 依据类型（自指语句/要件语义/引文匹配/引用锚/区间闭合）
   + 置信度；显式标出属于前后条款的边界段
3. 校验（规则）：cs 单调性、引用锚一致性、条款覆盖率；双模型独立跑，
   分歧段与低置信段进人工清单
4. 产出写入补丁 `manual_map` 区

实测依据（已用真实数据验证可行性）：
义注条款 1 尾部的自指语句（"...terasa nāmāni labhanti paṭhamapārājika"）、
制戒因缘（"idaṃ vesāliyaṃ sudinnattheraṃ ārabbha...paññattaṃ"）、
ṭīkā 的要件分析段落，均可由 LLM 可靠归属；标题结构三层同名对齐已实测复核：
`1. Paṭhamapārājikavaṇṇanā`～`4. Catutthapārājikavaṇṇanā` 在义注
（book 207 para 1299+）与两本 ṭīkā（book 212 para 41+ / 833+）字符串级一致。

注意：pātimokkha（vin6）自身也是规则组内局部编号（pārājika 1-4 后
saṅghādisesa 从 1 重计），修复时按组加后缀（vin6_pr1 型），与全局章后缀方案统一。

### 6.5 补丁文件格式

```jsonc
{
  "rename_book": [            // 章后缀 + kn10 家族改名；old 必须与导出器输出一致
    { "book": 143, "para_from": 2052, "para_to": 2510,
      "old": "kn10", "new": "kn10_2" },              // books 107/143/144 的导出名由书级配置表决定
    { "book": 71, "para_from": 415, "para_to": 1723, // abhidhamma 系：链条对齐约束下显式加后缀
      "old": "abhi7", "new": "abhi7_1",
      "source": "llm", "confidence": 0.93 }          // 对齐信息不足时由 LLM 辅助判定，人工复核
  ],
  "fix_cs": [                 // 乱码/区间修正（导出器已自动修的不再进补丁）
    { "book": 98, "para_from": 573, "para_to": 576,
      "old_cs": 44022, "new_cs": [7,8,9,10], "reason": "para7-10" }
      // new_cs 与 [para_from..para_to] 逐段一一对应，长度必须等于段数
  ],
  "manual_map": [             // LLM/人工区：区间整体赋值
    { "book": 207, "para_from": 1215, "para_to": 1240,
      "book_name": "vin6_pr1", "cs_para": 1, "source": "llm", "confidence": 0.97 }
  ]
  // book_id 不进补丁，更新 command 内置按 book_titles.sn 回填（顺带修复 sn 280/281 归错）
}
```

### 6.6 实施顺序

1. 导出器 command + 书级配置表 → 跑 book=136（区间正常）、book=98（缩写丢失+乱码）、
   book=207（书边界污染）三本样例验货
2. 全量导出 + issues 清单 + diff 报告（含行数净变化，区间展开会显著增行）
3. 跨侧对齐清单（程序自动）：镜像型书对做 max(cs_para) 交叉比对，不等者即 abhidhamma
   系错位书对，生成补丁后缀工作清单
4. 规则性补丁（镜像型章后缀、abhidhamma 系对齐后缀、kn10 改名、book_titles 修正）评审
5. LLM 流水线：先跑 abhidhamma 系对齐辅助判定 + kaṅkhāvitaraṇī 三本试点 →
   人工抽检 30 区间（验收 >98%）→ 推广到 E8/E9 全量
6. 更新 command 执行补丁（--dry-run 先行）
7. 消费方适配：主查询 (book, para) 锚点对后缀透明（无需改）；BookTitleController 输出需
   剥后缀/映射书名；UpgradeSystemCommentary 以 book_name|cs_para 为游标键，需制定既有
   AI 注释键的迁移或重跑方案；无锚点段落从约 2.6% 扩展为"留空区"（原为错误值）

### 6.7 明确不修 / 维持现状

- 空 book_name 的 51 本无注释体系书（E4）
- 回指补充型 cs 回退（合法结构）
- NK/subo/dict 自造名不改名（仅修其中乱码/缩写）
- book 1-2 每本前 1-3 段的 book_id=0（书前页语义正确）
- 页码/卷标标记体系（P/M/V/T/O 各系列，本轮不处理，导出时一律忽略）
- 裸数字型 `4(style=paranum)` 标记（当前库 0 行，无处理对象）

---

## 8. 实测复核记录（2026-03，库 visuddhinanda_20260311）

复核方式：对 §4.3 每个问题与 §6 每个关键假设直接 SQL 实测；与原数字有出入的均已回写
正文，此处只列要点。

- 精确复核：E1 1,242 行 / 24 本；E4 112,965 行；E5 book_id=0 共 354 行 / 201 本；
  E6 缺口 3,466 段；E8 按 sn 粒度精确（sn259 恒 cs=75 ×1,499、sn273 恒 cs=0 ×669、
  sn274 恰 19 个值）；E9 的 16 本无标记书清单逐本一致；E10 自造名逐一确认。
- E2 回绕点 430 个与机械规则一致（本数 70；§5 表内口径为 58 本 / 366 次，见 §5 注）。
- E7 缩写标记实测 1,517 段 / 44 本（原写 1,358 / 42），其中 1,292 段无行、225 段
  带行但 cs 全为乱码——与 E1 同根（`para32-3`→11,749、`para15-8`→44,058）。
- book 189 的 book_id 已回填（1,570 行 =230，仅 2 行书前页为 0），E5 描述已按现状更新。
- 章界假设：430 个回绕点到最近真实标题（toc 非空）±1 段 86%、±5 段 96.5%、
  ±30 段内零失配；41% 回绕点落在 level=100 空 toc 正文节点上，标题在邻段。
- 镜像机制：dn1 559=559、kn6 1289=1289、kn9 524=524、abhi5 918=918（两侧 max cs 相等），
  证明义注段标记即被注书段落号；abhi3/4/6/7 系两侧不等（含跨书污染 456/918 沿用），
  走补丁后缀路径。
- LLM 锚点：`1. Paṭhamapārājikavaṇṇanā`～`4. Catutthapārājikavaṇṇanā` 三层同名
  字符串级一致（book 207 para 1299+、book 212 para 41+/833+）。
- 标记词表：缩写+后缀组合 `para1008-9_sn5` 型 989 行；链式标记 4 行（旧数据已正确
  展开）；截断 `para179-` 仅 book 11 para 1577 一处；页码标记 26 家族 165,907 行
  （本轮忽略）；纯书名标记 93 行（书上下文事件）；巨区间 para308-1151 现为 844 行。
- 基础环境：cs6_para.csv 确认已不存在（storage/resources/pali_title 目录缺失）；
  281 个 sn 与 book_titles 一致；tags 的 8 个类型名齐备，文件类型消歧可用；
  新导出器 command 尚未实现，本文档仍处于待实施状态。
