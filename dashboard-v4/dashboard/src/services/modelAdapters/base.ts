// dashboard-v4/dashboard/src/services/modelAdapters/base.ts

import { IAiModel } from "../../components/api/ai";
import {
  ModelAdapter,
  OpenAIMessage,
  SendOptions,
  ParsedChunk,
  ToolCall,
} from "../../types/chat";
import {
  getArgs,
  GetTermDefinitionArgs,
  SearchByPageRefArgs,
  SearchByQueryArgs,
  SearchPaliArgs,
  SearchResponse,
} from "../../types/search";
import {
  getTermDefinition,
  searchByPageRef,
  searchByQuery,
  searchPali,
  tools,
} from "../agentApi";

export abstract class BaseModelAdapter implements ModelAdapter {
  abstract name: string;
  abstract supportsFunctionCall: boolean;
  abstract model: IAiModel | undefined;

  abstract sendMessage(
    messages: OpenAIMessage[],
    options: SendOptions
  ): Promise<AsyncIterable<string>>;
  abstract parseStreamChunk(chunk: string): ParsedChunk | null;

  public setModel(model: IAiModel) {
    this.model = model;
  }

  protected createStreamController() {
    return {
      addToken: (token: string) => {
        // 流式输出控制逻辑
      },
      complete: () => {
        // 完成处理逻辑
      },
    };
  }

  protected buildRequestPayload(
    messages: OpenAIMessage[],
    options: SendOptions
  ): SendOptions {
    return {
      model: this.model?.name,
      messages,
      stream: true,
      temperature: options.temperature || 0.7,
      max_tokens: options.max_tokens || 2048,
      top_p: options.top_p || 1,
      tools: tools,
      tool_choice: "auto",
    };
  }
  // ---------------------------------------------------------------- //
  //               核心 Function Calling 处理函数                     //
  // ---------------------------------------------------------------- //

  /**
   * 核心函数：根据 AI 助手返回的函数调用对象，执行相应的操作。
   *
   * @param functionCall AI 助手返回的函数调用对象。
   * @returns 返回一个 Promise，包含搜索结果。
   */
  async handleFunctionCall(functionCall: ToolCall): Promise<SearchResponse> {
    console.info("ai chat function call", functionCall);
    switch (functionCall.function.name) {
      case "search_by_query":
        return searchByQuery(
          getArgs<SearchByQueryArgs>(functionCall.function.arguments)
        );

      case "search_by_page_ref":
        return searchByPageRef(
          getArgs<SearchByPageRefArgs>(functionCall.function.arguments)
        );

      case "get_term_definition":
        return getTermDefinition(
          getArgs<GetTermDefinitionArgs>(functionCall.function.arguments)
        );

      case "search_pali":
        return searchPali(
          getArgs<SearchPaliArgs>(functionCall.function.arguments)
        );

      default:
        throw new Error(`Unknown function call: ${functionCall.function.name}`);
    }
  }
}
