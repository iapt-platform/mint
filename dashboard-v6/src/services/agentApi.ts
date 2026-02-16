import { FunctionDefinition } from "../types/chat";
import {
  SearchByQueryArgs,
  SearchByPageRefArgs,
  GetTermDefinitionArgs,
  SearchPaliArgs,
  SearchResponse,
} from "../types/search"; // 假设你的类型定义文件名为 apiTypes.ts

/**
 * 基础 API URL
 * 请替换为你的实际后端 API 地址
 */
const API_BASE_URL = "http://localhost:8000/api/v3";

export const system_prompt = `
你是一个专业的、精通巴利语和汉语的佛教文献检索助手。你的任务是分析用户的查询，并利用你拥有的工具（函数）来获取信息。

严格遵守以下原则：
1.  优先并恰当地使用提供的工具来满足用户的查询需求。
2.  你的回答必须简洁、直接，仅包含从工具中获得的信息，不添加任何额外闲聊。
3.  如果一个查询无法被任何工具处理，或者需要与用户进行澄清，请清晰地说明。
4.  当用户的查询包含**页码标记**（例如：M3.58, V3.81）时，必须使用 search_by_page_ref 函数。
5.  当用户的查询是**明确的佛教术语或词汇**（例如：四圣谛, mettā）时，必须使用 search_pali 和 get_term_definition 函数 进行两次搜索，获取巴利文原文和术语字典结果。
6.  当用户的查询是**描述性、自然语言问题**（例如：佛陀关于慈悲的教导）时，必须使用 search_by_query 函数。
7.  当用户的查询是**普通关键词**（例如：比丘, 袈裟）时，使用 search_by_query 函数。

以下是你的工具箱：
- search_by_query: 用于通用的模糊和语义搜索。
- search_by_page_ref: 专门用于处理页码搜索。
- get_term_definition: 专门用于获取术语定义。
- search_pali: 专门用于处理巴利文搜索。

请严格根据上述原则，选择最恰当的工具来处理用户的请求。
如果用户追问的问题可以使用之前的数据回答，那么无需再次调用工具。
`;

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
export const searchByQuery = async (
  args: SearchByQueryArgs
): Promise<SearchResponse> => {
  return apiClient<SearchResponse>("/search", {
    q: args.query,
  });
};

/**
 * 专门处理页码搜索的函数。
 */
export const searchByPageRef = async (
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
export const getTermDefinition = async (
  args: GetTermDefinitionArgs
): Promise<SearchResponse> => {
  return apiClient<SearchResponse>("/search", {
    q: args.term,
    resource_type: "term",
  });
};

/**
 * 专门用于巴利文精确搜索的函数。
 */
export const searchPali = async (
  args: SearchPaliArgs
): Promise<SearchResponse> => {
  return apiClient<SearchResponse>("/search", {
    q: args.query,
    resource_type: "original_text", // 仅搜索原文
    language: "pali", // 仅搜索巴利文
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

export const tools: FunctionDefinition[] = [
  {
    type: "function",
    function: {
      name: "search_by_query",
      description: "通用搜索函数，支持模糊与语义查询。",
      parameters: {
        type: "object",
        properties: {
          query: {
            type: "string",
            description: "用户输入的查询语句（如: 佛陀关于慈悲的教导）",
          },
          search_mode: {
            type: "string",
            enum: ["fuzzy", "semantic"],
            description: "搜索模式: fuzzy=模糊, semantic=语义",
          },
        },
        required: ["query", "search_mode"],
        additionalProperties: false,
      },
    },
    strict: true,
  },
  {
    type: "function",
    function: {
      name: "search_by_page_ref",
      description: "根据页码标记（如 M3.58, V3.81）搜索对应内容。",
      parameters: {
        type: "object",
        properties: {
          page_refs: {
            type: "string",
            description: "经文页码标记（如 M3.58）",
          },
        },
        required: ["page_refs"],
        additionalProperties: false,
      },
    },
    strict: true,
  },
  {
    type: "function",
    function: {
      name: "get_term_definition",
      description: "获取佛教术语的精确定义，主要从字典资源。",
      parameters: {
        type: "object",
        properties: {
          term: {
            type: "string",
            description: "佛教术语（如: 四圣谛, 五蕴, 涅槃）",
          },
        },
        required: ["term"],
        additionalProperties: false,
      },
    },
    strict: true,
  },
  {
    type: "function",
    function: {
      name: "search_pali",
      description:
        "在巴利文语料中进行关键词搜索，被搜索词可以是巴利文或者其他语言。",
      parameters: {
        type: "object",
        properties: {
          query: {
            type: "string",
            description:
              "被搜索词，巴利文词汇或短语（如: mettā, anicca）或者其他语言的佛教专有名词",
          },
        },
        required: ["query"],
        additionalProperties: false,
      },
    },
    strict: true,
  },
];

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
