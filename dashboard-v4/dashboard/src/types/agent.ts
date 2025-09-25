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

// ---------------------------------------------------------------- //
//              AI 助手返回的 Function Call 类型                    //
// ---------------------------------------------------------------- //

/**
 * AI 助手返回的函数调用对象。
 */

export interface SearchByQueryArgs {
  query: string;
  search_mode: SearchMode;
  resource_type?: ResourceType[];
  language?: Language[];
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

export type AICallbackFunction = {
  name:
    | "search_by_query"
    | "search_by_page_ref"
    | "get_term_definition"
    | "search_pali";
  arguments:
    | SearchByQueryArgs
    | SearchByPageRefArgs
    | GetTermDefinitionArgs
    | SearchPaliArgs;
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
    search_type: SearchMode | "term_definition";
  };
}
