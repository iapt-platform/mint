# open ai server

这个 API 将接收 POST 请求，使用 OpenAI SDK 调用 API，并支持流式响应。现在我来创建 package.json 文件，包含所需的依赖：让我再创建一个使用示例文件，展示如何调用这个 API：

## 安装和运行步骤

1. **安装依赖**：

```bash
npm install
```

1. **启动服务器**：

`bash
npm run build
node dist/main-XXX.js config.json

````

## 主要功能特点

**✅ RESTful API**：使用 Express.js 创建 POST 端点 `/api/openai`

**✅ 支持流式响应**：当`payload.stream = true`时启用 Server-Sent Events

**✅ 错误处理**：完整的错误处理和状态码响应

**✅ CORS 支持**：支持跨域请求

**✅ 参数验证**：验证必需的参数

**✅ 健康检查**：提供`/health`端点用于服务监控

## API 使用方法

**请求格式**：

```json
{
  "open_ai_url": "https://api.openai.com/v1",
  "api_key": "your-api-key",
  "payload": {
    "model": "gpt-4",
    "messages": [{ "role": "user", "content": "your message" }],
    "stream": true // 可选，启用流式响应
  }
}
````

**非流式响应**：返回完整的 JSON 结果
**流式响应**：返回 Server-Sent Events 格式的实时数据流

服务器会在端口 3000 上运行，你可以通过环境变量`PORT`来修改端口号。

```bash
# 启动服务器（端口4000）
PORT=4000 npm run dev
```

## 测试

```bash
# 非流式请求
curl -X POST http://localhost:3000/api/openai \
  -H "Content-Type: application/json" \
  -d '{
    "open_ai_url": "https://api.openai.com/v1",
    "api_key": "your-api-key-here",
    "payload": {
      "model": "gpt-4",
      "messages": [
        {
          "role": "user",
          "content": "Tell me a three sentence bedtime story about a unicorn."
        }
      ],
      "max_tokens": 150,
      "temperature": 0.7
    }
  }'

# 流式请求
curl -X POST http://localhost:3000/api/openai \
  -H "Content-Type: application/json" \
  -d '{
    "open_ai_url": "https://api.openai.com/v1",
    "api_key": "your-api-key-here",
    "payload": {
      "model": "gpt-4",
      "messages": [
        {
          "role": "user",
          "content": "Tell me a three sentence bedtime story about a unicorn."
        }
      ],
      "max_tokens": 150,
      "temperature": 0.7,
      "stream": true
    }
  }'
```

```javascript
// 1. 非流式请求示例
async function callOpenAIAPI() {
  const response = await fetch("http://localhost:3000/api/openai", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      open_ai_url: "https://api.openai.com/v1",
      api_key: "your-api-key-here",
      payload: {
        model: "gpt-4",
        messages: [
          {
            role: "user",
            content: "Tell me a three sentence bedtime story about a unicorn.",
          },
        ],
        max_tokens: 150,
        temperature: 0.7,
      },
    }),
  });

  const data = await response.json();
  console.log("Non-streaming response:", data);
}

// 2. 流式请求示例
async function callOpenAIAPIStreaming() {
  const response = await fetch("http://localhost:3000/api/openai", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      open_ai_url: "https://api.openai.com/v1",
      api_key: "your-api-key-here",
      payload: {
        model: "gpt-4",
        messages: [
          {
            role: "user",
            content: "Tell me a three sentence bedtime story about a unicorn.",
          },
        ],
        max_tokens: 150,
        temperature: 0.7,
        stream: true, // 开启流式响应
      },
    }),
  });

  const reader = response.body.getReader();
  const decoder = new TextDecoder();

  try {
    while (true) {
      const { done, value } = await reader.read();
      if (done) break;

      const chunk = decoder.decode(value);
      const lines = chunk.split("\n");

      for (const line of lines) {
        if (line.startsWith("data: ")) {
          const data = line.slice(6);
          if (data === "[DONE]") {
            console.log("Stream finished");
            return;
          }

          try {
            const parsed = JSON.parse(data);
            console.log("Streaming chunk:", parsed);
          } catch (e) {
            // 忽略解析错误
          }
        }
      }
    }
  } finally {
    reader.releaseLock();
  }
}
```
