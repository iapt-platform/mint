import { BaseModelAdapter } from "./base";
import {
  OpenAIMessage,
  SendOptions,
  ParsedChunk,
  ToolCall,
} from "../../types/chat";

export class OpenAIAdapter extends BaseModelAdapter {
  name = "gpt-4";
  supportsFunctionCall = true;

  // 修改这个方法
  async sendMessage(
    messages: OpenAIMessage[],
    options: SendOptions
  ): Promise<AsyncIterable<string>> {
    const payload = this.buildRequestPayload(messages, options);

    return this.createStreamIterable(payload);
  }

  // 新增这个私有方法
  private async *createStreamIterable(payload: any): AsyncIterable<string> {
    const response = await fetch(process.env.REACT_APP_OPENAI_PROXY!, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${process.env.REACT_APP_OPENAI_KEY}`,
      },
      body: JSON.stringify({
        model_id: "gpt-4",
        payload,
      }),
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const reader = response.body?.getReader();
    if (!reader) {
      throw new Error("无法获取响应流");
    }

    const decoder = new TextDecoder();
    let buffer = "";

    try {
      while (true) {
        const { done, value } = await reader.read();
        if (done) break;

        buffer += decoder.decode(value, { stream: true });
        const lines = buffer.split("\n");
        buffer = lines.pop() || "";

        for (const line of lines) {
          if (!line.trim() || !line.startsWith("data: ")) continue;
          const data = line.slice(6);
          if (data === "[DONE]") return;

          yield data;
        }
      }
    } finally {
      reader.releaseLock();
    }
  }

  // 其他方法保持不变
  parseStreamChunk(chunk: string): ParsedChunk | null {
    try {
      const parsed = JSON.parse(chunk);
      const delta = parsed.choices?.[0]?.delta;
      const finishReason = parsed.choices?.[0]?.finish_reason;

      return {
        content: delta?.content,
        function_call: delta?.function_call,
        finish_reason: finishReason,
      };
    } catch {
      return null;
    }
  }

  async handleFunctionCall(functionCall: ToolCall): Promise<any> {
    switch (functionCall.function) {
      case "searchTerm":
        return await this.searchTerm(functionCall.arguments.term);
      case "getWeather":
        return await this.getWeather(functionCall.arguments.city);
      default:
        throw new Error(`未知函数: ${functionCall.function}`);
    }
  }

  private async searchTerm(term: string) {
    const response = await fetch(
      `/v2/search-pali-wbw?view=pali&key=${term}&limit=20&offset=0`
    );
    const result = await response.json();
    return result.ok ? result.data.rows : { error: "搜索失败" };
  }

  private async getWeather(city: string) {
    return {
      city,
      temperature: "25°C",
      condition: "晴朗",
      humidity: "60%",
    };
  }
}
