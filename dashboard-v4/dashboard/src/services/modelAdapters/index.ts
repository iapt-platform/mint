import { IAiModel } from "../../components/api/ai";
import { ModelAdapter } from "../../types/chat";
import { MockOpenAIAdapter } from "./mockOpenAI";
import { OpenAIAdapter } from "./openai";

const adapters = new Map<string, ModelAdapter>();

// 注册适配器
adapters.set("gpt-4.1", new OpenAIAdapter());
adapters.set("gpt-4.1-mini", new OpenAIAdapter());
adapters.set("deepseek-v3", new MockOpenAIAdapter());

export function getModelAdapter(model: IAiModel): ModelAdapter {
  const adapter = adapters.get(model.name);

  if (!adapter) {
    throw new Error(`未找到模型适配器: ${model.name}`);
  }
  adapter.setModel(model);
  return adapter;
}

export function registerAdapter(modelId: string, adapter: ModelAdapter) {
  adapters.set(modelId, adapter);
}
