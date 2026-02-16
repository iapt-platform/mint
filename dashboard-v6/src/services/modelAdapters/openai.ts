// dashboard-v4/dashboard/src/services/modelAdapters/openai.ts

import { BaseModelAdapter } from "./base";
import {
  OpenAIMessage,
  SendOptions,
  ParsedChunk,
  ChatCompletionChunk,
} from "../../types/chat";
import { IAiModel } from "../../components/api/ai";
import { tools } from "../agentApi";

export class OpenAIAdapter extends BaseModelAdapter {
  model: IAiModel | undefined;
  name = "gpt-4";
  supportsFunctionCall = true;

  protected buildRequestPayload(
    messages: OpenAIMessage[],
    options: SendOptions
  ) {
    return {
      model: this.model?.name,
      messages,
      stream: true,
      temperature: options.temperature || 0.7,
      max_completion_tokens: options.max_tokens || 2048,
      top_p: options.top_p || 1,
      tools: tools,
      tool_choice: "auto",
    };
  }

  // 修改这个方法
  async sendMessage(
    messages: OpenAIMessage[],
    options: SendOptions
  ): Promise<AsyncIterable<string>> {
    const payload = this.buildRequestPayload(messages, options);

    return this.createStreamIterable(payload);
  }

  private async *createStreamIterable(payload: any): AsyncIterable<string> {
    console.log("ai chat send message", payload);
    const response = await fetch(process.env.REACT_APP_OPENAI_PROXY!, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${process.env.REACT_APP_OPENAI_KEY}`,
      },
      body: JSON.stringify({
        model_id: this.model?.uid,
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
      const parsed: ChatCompletionChunk = JSON.parse(chunk);
      const delta = parsed.choices?.[0]?.delta;
      const finishReason = parsed.choices?.[0]?.finish_reason;

      return {
        content: delta?.content,
        tool_calls: delta?.tool_calls,
        finish_reason: finishReason,
      };
    } catch {
      return null;
    }
  }
}
