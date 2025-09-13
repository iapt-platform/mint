// services/modelAdapters/mockOpenAI.ts
import { BaseModelAdapter } from "./base";
import {
  OpenAIMessage,
  SendOptions,
  ParsedChunk,
  ToolCall,
} from "../../types/chat";

export class MockOpenAIAdapter extends BaseModelAdapter {
  name = "mock-gpt-4";
  supportsFunctionCall = true;

  protected mockDelay = (ms: number) =>
    new Promise((resolve) => setTimeout(resolve, ms));

  async sendMessage(
    messages: OpenAIMessage[],
    options: SendOptions
  ): Promise<AsyncIterable<string>> {
    const payload = this.buildRequestPayload(messages, options);

    console.log("[Mock OpenAI] Sending message with payload:", payload);

    return this.createMockStreamIterable(messages, options);
  }

  private async *createMockStreamIterable(
    messages: OpenAIMessage[],
    options: SendOptions
  ): AsyncIterable<string> {
    // 模拟初始延迟
    await this.mockDelay(100);

    const lastMessage = messages[messages.length - 1];
    const userContent = lastMessage?.content || "";

    // 检查是否需要函数调用
    const needsFunctionCall = this.shouldCallFunction(userContent);

    if (needsFunctionCall) {
      // 模拟函数调用流程
      yield* this.generateFunctionCallResponse(userContent);
    } else {
      // 模拟普通文本响应
      yield* this.generateTextResponse(userContent);
    }
  }

  private shouldCallFunction(content: string): boolean {
    const functionTriggers = [
      "天气",
      "weather",
      "温度",
      "气温",
      "搜索",
      "search",
      "查找",
      "find",
      "翻译",
      "translate",
      "巴利",
      "pali",
    ];

    return functionTriggers.some((trigger) =>
      content.toLowerCase().includes(trigger.toLowerCase())
    );
  }

  private async *generateFunctionCallResponse(
    userContent: string
  ): AsyncIterable<string> {
    // 第一步：生成函数调用意图的响应
    const functionCallChunk = {
      choices: [
        {
          delta: {
            content: null,
            function_call: this.determineFunctionCall(userContent),
          },
          finish_reason: "function_call",
        },
      ],
    };

    yield JSON.stringify(functionCallChunk);
    await this.mockDelay(200);

    // 模拟函数执行后的最终响应
    const finalResponseText = this.generateFunctionBasedResponse(userContent);
    yield* this.streamText(finalResponseText);
  }

  private determineFunctionCall(content: string): any {
    if (content.includes("天气") || content.toLowerCase().includes("weather")) {
      const city = this.extractCityFromContent(content);
      return {
        name: "getWeather",
        arguments: JSON.stringify({ city }),
      };
    } else if (
      content.includes("搜索") ||
      content.toLowerCase().includes("search")
    ) {
      const term = this.extractSearchTermFromContent(content);
      return {
        name: "searchTerm",
        arguments: JSON.stringify({ term }),
      };
    }

    return {
      name: "getWeather",
      arguments: JSON.stringify({ city: "北京" }),
    };
  }

  private extractCityFromContent(content: string): string {
    const cities = [
      "北京",
      "上海",
      "广州",
      "深圳",
      "杭州",
      "成都",
      "西安",
      "武汉",
      "New York",
      "London",
      "Tokyo",
      "Paris",
      "Sydney",
    ];

    for (const city of cities) {
      if (content.includes(city)) {
        return city;
      }
    }
    return "北京"; // 默认城市
  }

  private extractSearchTermFromContent(content: string): string {
    // 简单提取逻辑，实际应用中可能需要更复杂的NLP处理
    const matches = content.match(/搜索["']?([^"']+)["']?/);
    if (matches) {
      return matches[1];
    }

    const searchMatches = content.match(/search\s+["']?([^"']+)["']?/i);
    if (searchMatches) {
      return searchMatches[1];
    }

    return "佛法"; // 默认搜索词
  }

  private generateFunctionBasedResponse(userContent: string): string {
    if (
      userContent.includes("天气") ||
      userContent.toLowerCase().includes("weather")
    ) {
      const city = this.extractCityFromContent(userContent);
      return `根据天气查询结果，${city}今天的天气情况如下：\n\n🌤️ **天气状况**：晴朗\n🌡️ **温度**：25°C\n💧 **湿度**：60%\n\n今天是个出行的好日子！记得适当补充水分。`;
    } else if (
      userContent.includes("搜索") ||
      userContent.toLowerCase().includes("search")
    ) {
      const term = this.extractSearchTermFromContent(userContent);
      return `我已经为您搜索了"${term}"相关的内容。以下是搜索结果：\n\n📚 **搜索结果**：\n• 找到了 15 个相关条目\n• 包含词汇解释、语法分析等\n• 提供了详细的语言学资料\n\n如需查看具体内容，请告诉我您感兴趣的具体方面。`;
    }

    return "我理解您的请求，已经为您处理完成。如有其他需要帮助的地方，请随时告诉我。";
  }

  private async *generateTextResponse(
    userContent: string
  ): AsyncIterable<string> {
    const responses = [
      "我很高兴为您提供帮助！",
      "这是一个很有趣的问题。",
      "让我为您详细解答一下。",
      "根据我的理解，我认为...",
      "这个问题涉及到多个方面，让我逐一为您分析。",
      "希望这个回答对您有所帮助。",
    ];

    // 根据用户输入选择合适的响应
    let responseText = "";

    if (userContent.length > 50) {
      responseText = `您提出了一个详细的问题。${
        responses[Math.floor(Math.random() * responses.length)]
      }

针对您的问题，我可以从以下几个角度来回答：

1. **主要观点**：${userContent.substring(0, 20)}...这个话题确实值得深入讨论。

2. **相关背景**：这类问题通常需要综合考虑多个因素。

3. **建议方案**：我建议您可以从实际情况出发，结合具体需求来选择最合适的方法。

如果您需要更详细的解释或有其他相关问题，请随时告诉我！`;
    } else {
      responseText = `${responses[Math.floor(Math.random() * responses.length)]}

关于"${userContent}"这个问题，我的理解是这样的：

这确实是一个值得探讨的话题。根据我的知识，我认为最重要的是要考虑实际的应用场景和具体需求。

希望我的回答对您有帮助。如果您还有任何疑问，欢迎继续提问！`;
    }

    yield* this.streamText(responseText);
  }

  private async *streamText(text: string): AsyncIterable<string> {
    const words = text.split("");

    for (let i = 0; i < words.length; i++) {
      const chunk = {
        choices: [
          {
            delta: {
              content: words[i],
            },
            finish_reason: i === words.length - 1 ? "stop" : null,
          },
        ],
      };

      yield JSON.stringify(chunk);

      // 模拟打字机效果，随机延迟
      const delay = Math.random() * 50 + 10; // 10-60ms 随机延迟
      await this.mockDelay(delay);
    }

    // 发送结束标记
    const finishChunk = {
      choices: [
        {
          delta: {},
          finish_reason: "stop",
        },
      ],
    };

    yield JSON.stringify(finishChunk);
  }

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
    } catch (error) {
      console.warn("[Mock OpenAI] Failed to parse chunk:", chunk, error);
      return null;
    }
  }

  async handleFunctionCall(functionCall: ToolCall): Promise<any> {
    console.log("[Mock OpenAI] Handling function call:", functionCall);

    // 模拟函数执行延迟
    await this.mockDelay(300);

    switch (functionCall.function) {
      case "searchTerm":
        return await this.mockSearchTerm(functionCall.arguments.term);
      case "getWeather":
        return await this.mockGetWeather(functionCall.arguments.city);
      default:
        console.warn(
          `[Mock OpenAI] Unknown function: ${functionCall.function}`
        );
        throw new Error(`未知函数: ${functionCall.function}`);
    }
  }

  private async mockSearchTerm(term: string): Promise<any> {
    console.log(`[Mock OpenAI] Searching for term: ${term}`);

    // 模拟搜索延迟
    await this.mockDelay(500);

    // 生成模拟的搜索结果
    const mockResults = [
      {
        id: 1,
        word: term,
        definition: `${term}的定义：这是一个重要的概念`,
        grammar: "名词",
        example: `使用${term}的例句示例`,
      },
      {
        id: 2,
        word: `${term}相关词`,
        definition: `与${term}相关的另一个概念`,
        grammar: "动词",
        example: `相关用法示例`,
      },
      {
        id: 3,
        word: `${term}变体`,
        definition: `${term}的变体形式`,
        grammar: "形容词",
        example: `变体使用示例`,
      },
    ];

    return {
      ok: true,
      data: {
        rows: mockResults,
        total: mockResults.length,
      },
    };
  }

  private async mockGetWeather(city: string): Promise<any> {
    console.log(`[Mock OpenAI] Getting weather for city: ${city}`);

    // 模拟天气API延迟
    await this.mockDelay(300);

    // 生成随机天气数据
    const conditions = ["晴朗", "多云", "小雨", "阴天", "晴转多云"];
    const temperatures = ["22°C", "25°C", "18°C", "28°C", "20°C"];
    const humidities = ["45%", "60%", "75%", "55%", "65%"];

    return {
      city,
      temperature:
        temperatures[Math.floor(Math.random() * temperatures.length)],
      condition: conditions[Math.floor(Math.random() * conditions.length)],
      humidity: humidities[Math.floor(Math.random() * humidities.length)],
      timestamp: new Date().toISOString(),
      source: "Mock Weather API",
    };
  }

  // 工具方法：构建请求负载（继承自BaseModelAdapter）
  protected buildRequestPayload(
    messages: OpenAIMessage[],
    options: SendOptions
  ) {
    return {
      model: this.name, // 添加required的model字段
      messages: messages, // 直接使用原始messages，不需要重新映射
      stream: true,
      temperature: options.temperature || 0.7,
      max_tokens: options.max_tokens || 2000,
      top_p: options.top_p || 1,
      functions: options.functions,
      function_call: options.function_call || "auto", // 确保不为undefined
    };
  }
}

// 导出工厂函数，便于在不同环境中使用
export function createMockOpenAIAdapter(): MockOpenAIAdapter {
  return new MockOpenAIAdapter();
}

// 导出配置选项
export interface MockOpenAIOptions {
  responseDelay?: number; // 响应延迟
  streamDelay?: number; // 流式输出延迟
  enableFunctionCall?: boolean; // 是否启用函数调用
  mockErrorRate?: number; // 模拟错误率 (0-1)
}

// 可配置的Mock适配器
export class ConfigurableMockOpenAIAdapter extends MockOpenAIAdapter {
  constructor(private options: MockOpenAIOptions = {}) {
    super();
    this.name = "configurable-mock-gpt-4";
  }

  private get responseDelay() {
    return this.options.responseDelay || 100;
  }

  private get streamDelay() {
    return this.options.streamDelay || 30;
  }

  protected mockDelay = (ms?: number) =>
    new Promise((resolve) => setTimeout(resolve, ms || this.responseDelay));

  async handleFunctionCall(functionCall: ToolCall): Promise<any> {
    if (!this.options.enableFunctionCall) {
      throw new Error("Function call disabled in mock adapter");
    }

    // 模拟随机错误
    if (
      this.options.mockErrorRate &&
      Math.random() < this.options.mockErrorRate
    ) {
      throw new Error("Mock function call error");
    }

    return super.handleFunctionCall(functionCall);
  }
}
