import { ModelAdapter } from "../../types/chat";
import { MockOpenAIAdapter } from "./mockOpenAI";
import { OpenAIAdapter } from "./openai";

const adapters = new Map<string, ModelAdapter>();

// 注册适配器
adapters.set("gpt-4", new OpenAIAdapter());
adapters.set("gpt-3.5-turbo", new OpenAIAdapter());
adapters.set("deepseek-v3", new MockOpenAIAdapter());

export function getModelAdapter(modelId: string): ModelAdapter {
  const adapter = adapters.get(modelId);
  if (!adapter) {
    throw new Error(`未找到模型适配器: ${modelId}`);
  }
  return adapter;
}

export function registerAdapter(modelId: string, adapter: ModelAdapter) {
  adapters.set(modelId, adapter);
}
