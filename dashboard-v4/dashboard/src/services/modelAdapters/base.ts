import {
  ModelAdapter,
  OpenAIMessage,
  SendOptions,
  ParsedChunk,
  ToolCall,
} from "../../types/chat";

export abstract class BaseModelAdapter implements ModelAdapter {
  abstract name: string;
  abstract supportsFunctionCall: boolean;

  abstract sendMessage(
    messages: OpenAIMessage[],
    options: SendOptions
  ): Promise<AsyncIterable<string>>;
  abstract parseStreamChunk(chunk: string): ParsedChunk | null;
  abstract handleFunctionCall(functionCall: ToolCall): Promise<any>;

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
  ) {
    return {
      model: this.name,
      messages,
      stream: true,
      temperature: options.temperature || 0.7,
      max_tokens: options.max_tokens || 2048,
      top_p: options.top_p || 1,
      functions: options.functions,
      function_call: options.function_call || "auto",
    };
  }
}
