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
export type SearchMode = "fuzzy" | "exact" | "semantic" | "hybrid";

/**
 * 文档类型的枚举，用于筛选不同来源的文档。
 */
export type ResourceType =
  | "article"
  | "term"
  | "dictionary"
  | "translation"
  | "original_text"
  | "nissaya";

/**
 * 语言代码的枚举。
 */
export type Language = "pali" | "zh-Hans" | "zh-Hant" | "en-US" | "my";

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

export function getArgs<T>(input: string): T {
  try {
    const parsed = JSON.parse(input);
    return parsed as T;
  } catch (err) {
    throw new Error(
      `Invalid arguments JSON: ${
        err instanceof Error ? err.message : String(err)
      }`
    );
  }
}
// ---------------------------------------------------------------- //
//              AI 助手返回的 Function Call 类型                    //
// ---------------------------------------------------------------- //

/**
 * AI 助手返回的函数调用对象。
 */

export interface SearchByQueryArgs {
  query: string;
  search_mode: SearchMode;
}

export interface SearchByPageRefArgs {
  page_refs: string;
}

export interface GetTermDefinitionArgs {
  term: string;
}

export interface SearchPaliArgs {
  query: string;
}

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
    search_type: SearchMode | "term_definition";
  };
}

/**
 * openSearch response
 */

// 基础响应接口
export interface ElasticsearchResponse<T> {
  took: number;
  timed_out: boolean;
  _shards: ShardsInfo;
  hits: Hits<T>;
  aggregations?: Aggregations;
}

// 分片信息接口
export interface ShardsInfo {
  total: number;
  successful: number;
  skipped: number;
  failed: number;
}

// 命中结果接口
export interface Hits<T> {
  total: TotalHits;
  max_score: number;
  hits: Hit<T>[];
}

// 命中总数接口
export interface TotalHits {
  value: number;
  relation: "eq" | "gte" | "lte";
}

// 单个命中结果接口
interface Hit<T> {
  _index: string;
  _id: string;
  _score: number;
  _source: T;
}

// 聚合结果接口
interface Aggregations {
  granularity?: TermsAggregation;
  resource_type?: TermsAggregation;
  language?: TermsAggregation;
  category?: TermsAggregation;
}

// 词条聚合接口
interface TermsAggregation {
  doc_count_error_upper_bound: number;
  sum_other_doc_count: number;
  buckets: Bucket[];
}

// 聚合桶接口
interface Bucket {
  key: string;
  doc_count: number;
}

// 文档数据接口 - 对应 _source 字段
export interface WikipaliDocument {
  id: string;
  resource_id: string;
  resource_type: string;
  title: Title;
  summary: Summary;
  content: Content;
  bold_single: string;
  bold_multi: string;
  related_id: number;
  category: string;
  language: string;
  updated_at: string;
  granularity: string;
  path?: string;
}

// 标题接口
interface Title {
  text: string;
}

// 摘要接口
interface Summary {
  text: string;
}

// 内容接口
interface Content {
  display: string;
  text: string;
  exact: string;
}

// 特定于搜索结果的类型别名，便于使用
export type WikipaliSearchResponse = ElasticsearchResponse<WikipaliDocument>;

// 示例使用方式：
// const response: WikipaliSearchResponse = await fetchSearchResults();
// const documents = response.hits.hits.map(hit => hit._source);

export interface Suggestion {
  text: string;
  source: string;
  score: number;
  resource_type: string;
  language: string;
  doc_id: string;
  category: string;
  granularity: string;
}

export interface SuggestionsData {
  query: string;
  suggestions: Suggestion[];
  total: number;
}

export interface SuggestionsResponse {
  success: boolean;
  data: SuggestionsData;
}
