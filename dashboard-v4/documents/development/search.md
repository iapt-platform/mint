# 全文搜索项目需求

## 1. 项目目标

构建一个基于 **OpenSearch** 的佛教文献检索系统，支持 **巴利文、中文、英文** 的多语种搜索，覆盖经文、译文、字典等资源，满足以下核心需求：

- 支持 **巴利文格位归一化搜索**（lemma 化）
- 支持 **中文多粒度分词**（词组 / 单字）
- 支持 **简繁体互查**
- 支持 **Markdown 内容** 的搜索，并对 **黑体加权**
- 支持 **页码标记搜索**
- 支持 **语义相似搜索**（向量检索）
- 支持 **跨语言搜索** （多语言 embedding 模型 + 向量检索 ）

---

## 2. 数据类型

系统需要存储和检索以下资源：

1. **字典**

   - 词头（作为 title）
   - 解释（content，支持 Markdown）

2. **巴利语经文**

   - 标题（title）
   - 内容（content, markdown 格式 ， sutta,paragraph sentence 三个级别，编号 放在 related_id 字段 如 93-6）
   - 包含 lemma 化后的文本

3. **译文**

   - 标题（title）
   - 内容（与巴利文同结构，但是没有 sentence 级别）

---

## 3. 字段需求

### 核心字段

| 字段             | 类型         | 说明                                                   |
| ---------------- | ------------ | ------------------------------------------------------ |
| `id`             | string       | 文档唯一 ID                                            |
| `resource_id`    | uuid         | 文档在数据库中的 id                                    |
| `resource_type`  | string       | 文档类型，例如 dictionary / translation / pali_text    |
| `title`          | string       | 文档标题，可以是中文或巴利文                           |
| `summary`        | string       | 文档摘要 纯文本                                        |
| `content`        | string       | 文档主体内容，支持 Markdown，可能包含黑体字            |
| `content_vector` | dense_vector | 文档主体内容的 embedding，                             |
| `related_id`     | string       | 用于关联的 ID 段落 id 句子 id                          |
| `bold_single`    | string       | 单个黑体文本，用于搜索加权                             |
| `bold_multi`     | string       | 多个黑体文本，用于搜索加权                             |
| `page_refs`      | array        | 页码标记数组，例如 \["V3.81","M3.58"，“PTS Vin II 57”] |
| `tags`           | array        | 文档主题标签                                           |
| `category`       | array        | 文档分类，例如 ["sutta", "vinaya"]                     |
| `author`         | string       | 作者或译者                                             |
| `language`       | string       | 资源语言 pali,zh-Hans,zh-Hant,en-US,pali 等            |
| `created_at`     | string       | 原始文档创建时间                                       |
| `updated_at`     | string       | 原始文档更新时间                                       |

---

## 4. 检索需求

1. **全文搜索**

   - 标题、内容、段落、句子、巴利相似句
   - 支持中文多种颗粒度分词，避免分词错误导致搜索不到结果
   - 支持巴利文 lemma 搜索
   - 支持 `page_refs` 页码搜素

2. **过滤/精确查询**

   - 按 `tags` 过滤主题
   - 按 `category` 过滤文献分类

3. **搜索加权**

   - 若匹配到 `bold_single` `bold_multi`（Markdown 黑体），排名靠前

4. **语义搜索**

   - 基于 `vector` 检索相似句子
   - 忽略格位变化

---

## 5. 插件依赖

- **必需**

  - k-NN 插件（已支持向量搜索，无需额外安装）
  - 中文分词
  - 巴利文转英文字母

- **可选**

  - 简繁体转换

## 多语言 embedding 模型（托管型 调用 API 即可）

适合快速上线，效果好，但依赖外部服务。

1. **OpenAI - `text-embedding-multilingual-002`**

- https://platform.openai.com/docs/models/text-embedding-3-large
- https://platform.openai.com/docs/models/text-embedding-3-small

- 支持 100+ 语言（含中文、英文、缅文）。
- 1536 维向量。
- 专门为跨语言搜索优化。
- 部署成本：只需 API 调用。
- 场景：最稳妥，适合你的「用户用中文 → 检索缅文/英文/巴利文」需求。

2. **Cohere - `embed-multilingual-v3.0`**

   - 支持 100+ 语言，1024 维。
   - 在跨语言语义检索任务中表现接近 OpenAI。
   - 优势：提供 API，延迟较低。
