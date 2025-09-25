# 全文搜索项目需求

## 1. 项目目标

构建一个基于 **OpenSearch** 的佛教文献检索系统，支持 **巴利文、中文、英文** 的多语种搜索，覆盖经文、译文、字典等资源，满足以下核心需求：

- 支持 **巴利文格位归一化搜索**（ synonyms 等效单词表）
- 支持 **简繁体互查**
- 支持 **Markdown 内容** 的搜索，并对 **黑体加权**
- 支持 **页码标记搜索**
- 支持 **语义相似搜索**（向量检索）
- 支持 **跨语言搜索** （多语言 embedding 模型 + 向量检索 ）
- 支持 **模糊搜索和精确搜索**
- suggestion 支持 巴利字符英文字母化

三层设计 数据层，api 层 , 交互层 满足简单查询到 RAG 的不同需求

---

## 数据层

### 2. 数据类型

系统需要存储和检索以下资源：

1. **字典**

   - 词头（作为 title）
   - 解释（content，支持 Markdown）

2. **巴利语经文**

   - 标题（title）
   - 内容（content, markdown 格式 ， 多个颗粒度，编号 放在 related_id 字段 如 93-6）
   - 包含 embedding 化后的文本

3. **译文**

   - 标题（title）
   - 内容（与巴利文同结构）

---

### 3. 字段需求

#### 核心字段

- `id`：openSearch 唯一 ID
- `resource_id`：文档在数据库中的 ID（UUID）
- `resource_type`：文档类型（article | term | dictionary | translation | origin_text|nissaya）
- `granularity`：文档颗粒度 "book | chapter | sutta | section | paragraph | sentence"
- `title`：文档标题，可以是中文或巴利文
  - `display`:文档显示用 不搜索
  - `text`: 纯文本，模糊搜索用
  - `vector`: embedding
- `summary`：文档摘要 llm 根据 content 生成
  - `text`: 摘要文本
  - `vector`: embedding
- `content`：文档主体内容
  - `display`:文档显示用，支持 Markdown，可能包含黑体字
  - `text`: 纯文本，模糊搜索用 小写
  - `exact`: 精确搜索
  - `tokens`: 对象数组
    - `surface` : 显示拼写
    - `lemma` : 去格位拼写
    - `compound_parts` 复合词组分
    - `case` 语法信息 如：sg_acc
  - `vector`:文档内容的 embedding
- `related_id`：关联的段落 ID、句子 ID ["chapter_93-5","m.n. 38","93-5","95-7-2-10"]
- `bold_single`：单个黑体文本，用于搜索加权
- `bold_multi`：多个黑体文本，用于搜索加权
- `page_refs`：页码标记数组（如 \["V3.81","M3.58","PTS Vin II 57"]）
- `tags`：文档主题标签（数组）
- `category`：文档分类 枚举 "pali"| "commentary"|'subcommentary'
- `path`：经文路径 "/suttapitaka/dighanikaya/mahavaggo/satipatthanasutta"
- `language`：资源语言 pali, zh-Hans, zh-Hant, en-US, my 等
- `metadata`：
  - `APA`: APA 格式引文字符串
  - `MLA`: MLA 格式引文字符串
  - `author`：作者或译者
  - `channel`: wikipali channel
- `updated_at`：原始文档更新时间

```json
{
  "id": "openSearch唯一ID",
  "resource_id": "UUID",
  "resource_type": "article | term | dictionary | translation | origin_text | nissaya",
  "granularity": "book | chapter | sutta | section | paragraph | sentence"|"auto", // 新增
  "title": "文档标题（中文或巴利文）",
  "summary": {
    "text": "摘要文本",
    "vector": [
      /* embedding 向量 */
    ]
  },
  "content": {
    "display": "Markdown 格式，含黑体字",
    "text": "小写纯文本，模糊搜索用",
    "exact": "精确匹配用",
    "tokens": [
      { "surface": "bhikkhave", "lemma": "bhikkhu", "case": "voc_pl" }
    ],
    "vector": [
      /* embedding 向量 */
    ]
  },
  "related_id": ["m.n.38", "93-5"],
  "bold_single": ["某个黑体词"],
  "bold_multi": ["多个黑体词"],
  "page_refs": ["V3.81", "M3.58", "PTS Vin II 57"],
  "tags": ["禅修", "业报"],
  "category": ["pali", "atthakatha","tika"],
  "path": "/suttapitaka/dighanikaya/mahavaggo/satipatthanasutta",
  "language": "pali | zh-Hans | zh-Hant | en-US | my",
  "metadata": {
    "APA": "Smith, J. (2020). Title of the book. Publisher.",
    "MLA": "Smith, John. Title of the Book. Publisher, 2020.",
    "author": "译者或作者",
  },
  "updated_at": "2025-09-19T00:00:00Z"
}
```

---

### 4. 技术实现

1. **全文搜索**

   - 标题、内容、段落、句子、巴利相似句
   - 支持中文多种颗粒度分词，避免分词错误导致搜索不到结果
   - 支持巴利文 lemma 搜索
   - 支持 `page_refs` 页码搜素

1. **巴利文格位归一化搜索**

   - 使用 synonyms.txt 实现存储和查询去掉格位
   - 保留一个原始字段供精确查询
   - synonyms.txt 中包括中文术语，能够用中文查询巴利文
   - 文件放在 opensearch/config/analysis/synonyms.txt
   - 输入 亚卡 能搜索到 yakkha yakkho

在配置里定义一个查询时同义词分析器：

```json
"analyzer": {
  "pali_query_analyzer": {
    "tokenizer": "standard",
    "filter": ["lowercase", "pali_synonyms"]
  },
},
"filter": {
  "pali_synonyms": {
    "type": "synonym_graph",
    "synonyms_path": "analysis/pali_synonyms.txt"
  },
}

```

查询时指定 analyzer：

```json
{
  "query": {
    "match": {
      "content.text": {
        "query": "夜叉",
        "analyzer": "pali_query_analyzer"
      }
    }
  }
}
```

1. **过滤/精确查询**

   - 按 `tags` 过滤主题
   - 按 `category` 过滤文献分类

2. **黑体字搜索加权**

   - 若匹配到 `bold_single` `bold_multi`（Markdown 黑体），排名靠前

3. **语义搜索**

   - 基于 `vector` 检索相似句子
   - 基于 `vector` 检索不同语言相似句子
   -

4. **巴利相似句**

   - 忽略格位变化
   - 只搜索 sentence

5. **页码搜索**

   - 只搜索 `page_refs`

6. **简繁体互查**

   - 使用 OpenSearch ICU plugin 的 icu_transform 做简繁映射

7. **处理变音符号**

   - analysis-icu

8. **模糊搜索+精确匹配**

```json
{
  "mappings": {
    "properties": {
      "content": {
        "type": "text",
        "analyzer": "icu_analyzer",
        "fields": {
          "raw": { "type": "keyword" }
          // 精确匹配整段内容或句子
        }
      }
    }
  },
  "settings": {
    "analysis": {
      "analyzer": {
        "icu_analyzer": {
          "tokenizer": "icu_tokenizer",
          "filter": ["icu_folding", "lowercase"]
        }
      }
    }
  }
}
```

模糊搜索（支持巴利 diacritics 和英文转写）

```json
{
  "query": {
    "match": {
      "content": "mettā"
    }
  }
}
```

精确匹配（只要完全等于）

```json
{
  "query": {
    "term": {
      "title.raw": "mettā"
    }
  }
}
```

混合搜索 hybrid

模糊搜索+语义搜索 权重为 7:3

### 5. 插件依赖

- `k-NN` 插件（已支持向量搜索，无需额外安装）
- 中文分词 `ik`
- 巴利文转英文字母 `analysis-icu`
- 简繁体转换 `icu_transform`

### 多语言 embedding 模型（托管型 调用 API 即可）

适合快速上线，效果好，但依赖外部服务。

1. **OpenAI - `text-embedding-3-small`**

- [text-embedding-3-large](https://platform.openai.com/docs/models/text-embedding-3-large)
- [text-embedding-3-small](https://platform.openai.com/docs/models/text-embedding-3-small)

- 支持 100+ 语言（含中文、英文、缅文）。
- 1536 维向量。
- 专门为跨语言搜索优化。
- 部署成本：只需 API 调用。
- 场景：最稳妥，适合你的「用户用中文 → 检索缅文/英文/巴利文」需求。

```bash
curl https://api.openai.com/v1/embeddings \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $OPENAI_API_KEY" \
  -d '{
    "model": "text-embedding-3-small",
    "input": "佛陀在祇园精舍说法"
  }'

15 token
```

Prices per 1M tokens.

| Model                  | Cost  | Batch cost |
| ---------------------- | ----- | ---------- |
| text-embedding-3-small | $0.02 | $0.01      |
| text-embedding-3-large | $0.13 | $0.065     |

## API 层设计方案

laravel API.实现简单和复杂两个查询接口

### 搜索接口

`GET /api/v3/search`

查询参数：

- `q` (string): 搜索关键词
- `resource_type` (string): article|term|dictionary|translation|origin_text|nissaya
- `granularity` (string): book|chapter|sutta|section|paragraph|sentence
- `language` (string): pali|zh-Hans|zh-Hant|en-US|my
- `category` (array): 文献分类过滤 ["pali", "commentary", "subcommentary"]
- `tags` (array): 主题标签过滤
- `page_refs` (string): 页码搜索，如 "M3.58"
- `page` (int): 页码，默认 1
- `page_size` (int): 每页数量，默认 20，最大 100
- `search_mode` (string): fuzzy|exact|semantic|hybrid，默认 fuzzy

#### 业务逻辑示例

**关键词搜索**

```bash
GET /api/v3/search?q=世尊&search_mode=fuzzy
```

**语义搜索：**

```bash
GET /api/v3/search?q=佛陀关于慈悲的教导&search_mode=semantic
```

**巴利相似句搜索：**

```bash
GET /api/v3/search?q=sabbe dhammā anattā&resource_type=origin_text&language=pali&granularity=sentence&search_mode=fuzzy
```

**页码搜索：**

```bash
GET /api/v3/search?page_refs=M3.58
```

响应格式：

```json
{
  "success": true,
  "data": {
    "total": 156,
    "page": 1,
    "page_size": 20,
    "took": "120ms",
    "results": [
      {
        "id": "doc_123",
        "resource_id": "uuid-456",
        "resource_type": "origin_text",
        "granularity": "sentence",
        "title": "法印经",
        "content": {
          "display": "sabbe dhammā anattāti",
          "tokens": [
            { "surface": "sabbe", "lemma": "sabba", "case": "nom_pl" },
            { "surface": "dhammā", "lemma": "dhamma", "case": "nom_pl" },
            { "surface": "anattāti", "lemma": "anatta", "case": "nom_pl" }
          ]
        },
        "related_id": ["93-15"],
        "page_refs": ["M3.58"],
        "language": "pali",
        "path": "/suttapitaka/majjhimanikaya",
        "score": 0.95,
        "similarity": 0.92 // 巴利相似句特有字段
      }
    ],
    "aggregations": {
      "resource_type": {
        "buckets": [{ "key": "origin_text", "doc_count": 89 }]
      },
      "language": {
        "buckets": [{ "key": "pali", "doc_count": 89 }]
      }
    }
  },
  "query_info": {
    "original_query": "sabbe dhammā anattā",
    "search_type": "pali_similar_sentence" // 后端识别的搜索类型
  }
}
```

### 自动建议接口

输入英文字母，能查到带有特殊符号的巴利文

`GET /api/v3/search-suggest`

查询参数：

- `q` (string): 输入的部分文本
- `type` (enum): term|pali_romanized|page_ref
- `language` (string): 建议的目标语言
- `limit` (int): 返回数量，默认 10

```json
{
  "success": true,
  "data": {
    "suggestions": [
      {
        "text": "四念处",
        "pali": "satipaṭṭhāna",
        "romanized": "satipatthana",
        "type": "term",
        "frequency": 156
      }
    ]
  }
}
```

## 交互层

REACT + antd 实现聊天 Agent 调用不同的查询函数实现复杂的多轮对话查询

### 查询类型

- **单词解析**
  - **词义解释** 例子：什么是四圣谛 查询 summary, content 找到与`四圣谛`相关的资源
  - **佛教术语** 例子：请写一个竹林精舍的百科词条。 同上
  - **词义比较** 例子：禅那与禅那缘有什么关系。查询`禅那` `禅那缘`
- **经文解读**
  - **根据资料解释经文** 例子：经文解读:{{chapter|95-4}}. 查询经文和相关参考资料`related_id=95-4`。用于经文阅读界面。
- **经文查询**
  - **给一个大概的意思，查询相关的经文** 如：佛陀让阿难尊者制作如田地的袈裟出自哪里，给出巴利文和译文。

### 设计思路 --多功能代理设计思路：小而精的函数集

我们的全文搜索项目将采用**“多个函数、较少参数”**的设计理念。这一策略旨在通过创建一组专精、单一职责的工具函数，来构建一个高效、可扩展且易于维护的搜索代理（Agent）。

---

#### 1. 设计核心

传统的代理设计常将所有功能打包进一个庞大的函数（例如 `searchDocument`），并用大量参数来区分不同意图。这种做法虽然直观，但会给大型语言模型（LLM）带来认知负担，导致参数混淆和调用错误，并使得代码难以扩展。

我们的设计为：**每个函数只负责一种明确的搜索意图**，并只接受其完成任务所必需的最少参数。这就像为代理配备了一套专用的工具箱，而不是一把万能钥匙。

---

#### 2. 实现方式

我们为不同的搜索需求定义了以下几个功能独立的函数：

- **`search_by_query(query, search_mode, ...)`**: 处理通用的、描述性的模糊或语义搜索。
- **`search_by_page_ref(page_refs)`**: 专门用于精确的页码搜索。
- **`get_term_definition(term)`**: 仅用于获取佛教术语的定义。
- **`search_pali(query)`**: 专门用于巴利文的精确匹配搜索。

代理将通过解析用户查询，准确地调用这四个函数中的一个。例如，当用户输入“M3.58”时，代理会立即识别为页码搜索意图，并调用 `search_by_page_ref` 函数，而不会在其他不相关的参数上浪费算力。

---

#### 3. 核心优势

- **高准确率**：每个函数都有清晰、明确的用途，这极大地简化了 LLM 的判断过程，显著提高了函数调用的准确率。
- **卓越的灵活性**：如果未来需要新增功能，例如“按作者搜索”，我们只需定义一个新的 `search_by_author(author)` 函数即可，无需改动任何现有代码。
- **更易于维护**：由于每个函数都小而精，代码逻辑更清晰，方便开发和测试，降低了维护成本。

### 提示词

```text
你是一个专业的、精通巴利语和汉语的佛教文献检索助手。你的任务是分析用户的查询，并利用你拥有的工具（函数）来获取信息。

严格遵守以下原则：
1.  优先并恰当地使用提供的工具来满足用户的查询需求。
2.  你的回答必须简洁、直接，仅包含从工具中获得的信息，不添加任何额外闲聊。
3.  如果一个查询无法被任何工具处理，或者需要与用户进行澄清，请清晰地说明。
4.  当用户的查询包含**页码标记**（例如：M3.58, V3.81）时，必须使用 `search_by_page_ref` 函数。
5.  当用户的查询是**明确的佛教术语或词汇**（例如：四圣谛, mettā）时，必须使用 `get_term_definition` 或 `search_pali` 函数。
6.  当用户的查询是**描述性、自然语言问题**（例如：佛陀关于慈悲的教导）时，必须使用 `search_by_query` 函数并指定 `search_mode='semantic'`。
7.  当用户的查询是**普通关键词**（例如：比丘, 袈裟）时，使用 `search_by_query` 函数并指定 `search_mode='fuzzy'`。

以下是你的工具箱：
- `search_by_query`: 用于通用的模糊和语义搜索。
- `search_by_page_ref`: 专门用于处理页码搜索。
- `get_term_definition`: 专门用于获取术语定义。
- `search_pali`: 专门用于处理巴利文精确搜索。

请严格根据上述原则，选择最恰当的工具来处理用户的请求。
```

### function call

```json
[
  {
    "name": "search_by_query",
    "description": "根据关键词或自然语言描述在佛教文献库中进行通用搜索，支持模糊和语义匹配。适用于“佛陀关于慈悲的教导”或“什么是八正道”这类查询。",
    "parameters": {
      "type": "object",
      "properties": {
        "query": {
          "type": "string",
          "description": "用户的搜索关键词或句子。"
        },
        "search_mode": {
          "type": "string",
          "enum": ["fuzzy", "semantic"],
          "description": "指定搜索模式：'fuzzy' 用于模糊搜索, 'semantic' 用于语义相似度搜索。"
        }
      },
      "required": ["query", "search_mode"]
    }
  },
  {
    "name": "search_by_page_ref",
    "description": "根据页码标记（如 PTS VinIII 24, M3.58）在文献库中进行精确搜索，快速定位特定经文。",
    "parameters": {
      "type": "object",
      "properties": {
        "page_refs": {
          "type": "string",
          "description": "页码标记，例如 'PTS VinIII 24', 'M3.58', 'P3.81'。"
        }
      },
      "required": ["page_refs"]
    }
  },
  {
    "name": "get_term_definition",
    "description": "获取佛教术语或词汇的精确定义和解释，用于回答“什么是四圣谛”或“禅那是什么意思”这类问题。",
    "parameters": {
      "type": "object",
      "properties": {
        "term": {
          "type": "string",
          "description": "需要查询的佛教术语或词汇。"
        }
      },
      "required": ["term"]
    }
  },
  {
    "name": "search_pali",
    "description": "在巴利文经文中搜索精确匹配的词汇或短语，支持巴利文格位归一化。适用于“sabbe dhammā anattā”这类查询。",
    "parameters": {
      "type": "object",
      "properties": {
        "query": {
          "type": "string",
          "description": "需要精确匹配的巴利文词汇或短语。"
        }
      },
      "required": ["query"]
    }
  }
]
```

TypeScript 类型定义

```TypeScript
// ---------------------------------------------------------------- //
//             核心搜索函数参数类型：search_documents               //
// ---------------------------------------------------------------- //

/**
 * 搜索模式的枚举，用于指定不同类型的搜索。
 * - 'fuzzy': 模糊搜索，支持巴利文变音符号、简繁体等。
 * - 'exact': 精确匹配，用于专有名词或特定短语。
 * - 'semantic': 语义搜索，基于向量检索。
 * - 'hybrid': 混合搜索 fuzzy+semantic。
 */
export type SearchMode = 'fuzzy' | 'exact' | 'semantic' | 'hybrid';

/**
 * 文档类型的枚举，用于筛选不同来源的文档。
 */
export type ResourceType = 'article' | 'term' | 'dictionary' | 'translation' | 'original_text' | 'nissaya';

/**
 * 语言代码的枚举。
 */
export type Language = 'pali' | 'zh-Hans' | 'zh-Hant' | 'en-US' | 'my';

/**
 * search_documents 函数的参数类型。
 * 这将作为 Function Calling 的 `arguments` 参数传递。
 */
export interface SearchDocumentsArgs {
  /**
   * 用户的搜索关键词或句子。
   */
  query: string;

  /**
   * 指定搜索模式，由 AI 助手根据用户意图判断。
   */
  search_mode: SearchMode;

  /**
   * 文档类型数组，用于过滤搜索结果。
   */
  resource_type?: ResourceType[];

  /**
   * 语言数组，用于过滤搜索结果。
   */
  language?: Language[];

  /**
   * 页码标记，仅在 search_mode 为 'page_search' 时使用。
   */
  page_refs?: string;

  /**
   * 主题标签数组，用于进一步过滤。
   */
  tags?: string[];
}


// ---------------------------------------------------------------- //
//             术语定义函数参数类型：get_term_definition            //
// ---------------------------------------------------------------- //

/**
 * get_term_definition 函数的参数类型。
 */
export interface GetTermDefinitionArgs {
  /**
   * 需要查询的佛教术语或词汇。
   */
  term: string;
}


// ---------------------------------------------------------------- //
//              AI 助手返回的 Function Call 类型                    //
// ---------------------------------------------------------------- //

/**
 * AI 助手返回的函数调用对象。
 */
export type AICallbackFunction = {
  /**
   * 要调用的函数名。
   */
  name: 'search_documents' | 'get_term_definition';

  /**
   * 传递给函数的参数。
   * 这里使用泛型来确保参数类型与函数名匹配。
   */
  arguments: SearchDocumentsArgs | GetTermDefinitionArgs;
};

// ---------------------------------------------------------------- //
//              后端 API 响应类型（示例）                           //
// ---------------------------------------------------------------- //

/**
 * 核心文档卡片的数据结构。
 */
export interface DocumentResult {
  id: string;
  resource_id: string;
  resource_type: ResourceType;
  title: string;
  content: {
    display: string;
    text: string;
    vector?: number[];
  };
  related_id: string[];
  page_refs?: string[];
  language: Language;
  score: number;
  similarity?: number;
}

/**
 * API 搜索响应的完整结构。
 */
export interface SearchResponse {
  success: boolean;
  data: {
    total: number;
    page: number;
    page_size: number;
    took: string;
    results: DocumentResult[];
  };
  query_info: {
    original_query: string;
    search_type: SearchMode | 'term_definition';
  };
}
```
