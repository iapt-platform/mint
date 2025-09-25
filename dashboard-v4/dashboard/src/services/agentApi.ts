import {
  SearchByQueryArgs,
  SearchByPageRefArgs,
  GetTermDefinitionArgs,
  SearchPaliArgs,
  SearchResponse,
  AICallbackFunction,
} from "../types/agent"; // 假设你的类型定义文件名为 apiTypes.ts

/**
 * 基础 API URL
 * 请替换为你的实际后端 API 地址
 */
const API_BASE_URL = "http://localhost:8000/api/v3";

// ---------------------------------------------------------------- //
//                  低层 API 客户端（使用 fetch）                  //
// ---------------------------------------------------------------- //

const apiClient = async <T>(
  endpoint: string,
  params: Record<string, any>
): Promise<T> => {
  const searchParams = new URLSearchParams();
  for (const key in params) {
    if (params[key] !== undefined && params[key] !== null) {
      if (Array.isArray(params[key])) {
        searchParams.append(key, params[key].join(","));
      } else {
        searchParams.append(key, String(params[key]));
      }
    }
  }

  const url = `${API_BASE_URL}${endpoint}?${searchParams.toString()}`;

  try {
    const response = await fetch(url);
    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }

    const data: SearchResponse = await response.json();
    if (data.success) {
      return data.data as T;
    } else {
      throw new Error("API request was not successful.");
    }
  } catch (error) {
    console.error("API call failed:", error);
    throw new Error("An unexpected error occurred.");
  }
};

// ---------------------------------------------------------------- //
//                  封装的搜索函数（每个函数对应一个意图）          //
// ---------------------------------------------------------------- //

/**
 * 通用搜索函数，处理模糊和语义查询。
 */
const searchByQuery = async (
  args: SearchByQueryArgs
): Promise<SearchResponse> => {
  return apiClient<SearchResponse>("/search", {
    q: args.query,
    search_mode: args.search_mode,
    resource_type: args.resource_type,
    language: args.language,
  });
};

/**
 * 专门处理页码搜索的函数。
 */
const searchByPageRef = async (
  args: SearchByPageRefArgs
): Promise<SearchResponse> => {
  return apiClient<SearchResponse>("/search", {
    q: args.page_refs, // query参数使用页码
    search_mode: "page_search", // 固定搜索模式为页码搜索
    page_refs: args.page_refs,
  });
};

/**
 * 专门用于获取术语定义的函数。
 */
const getTermDefinition = async (
  args: GetTermDefinitionArgs
): Promise<SearchResponse> => {
  return apiClient<SearchResponse>("/search", {
    q: args.term,
    search_mode: "exact", // 固定为精确搜索
    resource_type: ["dictionary"], // 仅搜索字典类型
  });
};

/**
 * 专门用于巴利文精确搜索的函数。
 */
const searchPali = async (args: SearchPaliArgs): Promise<SearchResponse> => {
  return apiClient<SearchResponse>("/search", {
    q: args.query,
    search_mode: "exact", // 巴利文搜索通常是精确的
    language: ["pali"], // 仅搜索巴利文
  });
};

// ---------------------------------------------------------------- //
//               核心 Function Calling 处理函数                     //
// ---------------------------------------------------------------- //

/**
 * 核心函数：根据 AI 助手返回的函数调用对象，执行相应的操作。
 *
 * @param functionCall AI 助手返回的函数调用对象。
 * @returns 返回一个 Promise，包含搜索结果。
 */
export const handleFunctionCall = async (
  functionCall: AICallbackFunction
): Promise<SearchResponse> => {
  switch (functionCall.name) {
    case "search_by_query":
      return searchByQuery(functionCall.arguments as SearchByQueryArgs);

    case "search_by_page_ref":
      return searchByPageRef(functionCall.arguments as SearchByPageRefArgs);

    case "get_term_definition":
      return getTermDefinition(functionCall.arguments as GetTermDefinitionArgs);

    case "search_pali":
      return searchPali(functionCall.arguments as SearchPaliArgs);

    default:
      throw new Error(`Unknown function call: ${functionCall.name}`);
  }
};

// ---------------------------------------------------------------- //
//                  使用示例                                       //
// ---------------------------------------------------------------- //
/**
 * 
 * 

const main = async () => {
  // 模拟从 AI 助手获得的函数调用对象
  const mockCalls: AICallbackFunction[] = [
    {
      name: "search_by_query",
      arguments: { query: "佛陀关于慈悲的教导", search_mode: "semantic" },
    },
    { name: "search_by_page_ref", arguments: { page_refs: "M3.58" } },
    { name: "get_term_definition", arguments: { term: "四圣谛" } },
    { name: "search_pali", arguments: { query: "mettā" } },
  ];

  for (const call of mockCalls) {
    try {
      console.log(`\n正在处理函数调用：${call.name}`);
      const result = await handleFunctionCall(call);
      console.log("搜索成功，找到结果数量：", result.data.hits.total.value);
      // 根据你的需求，你可以在这里处理并展示结果
    } catch (error) {
      console.error(`处理函数调用失败：${call.name}`, error);
    }
  }
};
 */
// main();
