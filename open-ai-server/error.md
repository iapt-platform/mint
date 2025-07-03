# 错误处理指南

## 错误响应格式

### API 错误响应（来自 OpenAI SDK）

```json
{
  "error": "Error message",
  "type": "api_error",
  "code": "invalid_request_error",
  "param": "model",
  "details": {
    "status": 400,
    "headers": {},
    "request_id": "req_123",
    "response": {
      // 原始API响应
    }
  },
  "raw_error": {
    "name": "OpenAIError",
    "message": "Detailed error message",
    "stack": "Error stack trace (development only)"
  }
}
```

### 服务器内部错误响应

```json
{
  "error": "Internal server error",
  "message": "Specific error message",
  "type": "NetworkError",
  "details": {
    "name": "NetworkError",
    "message": "Connection failed",
    "stack": "Error stack trace (development only)",
    "cause": null,
    "errno": -3008,
    "code": "ENOTFOUND",
    "syscall": "getaddrinfo",
    "hostname": "api.example.com"
  }
}
```

### 流式响应错误

```json
{
  "error": "Streaming failed",
  "type": "streaming_error",
  "status": 429,
  "code": "rate_limit_exceeded",
  "details": {
    "name": "RateLimitError",
    "message": "Rate limit exceeded",
    "stack": "Error stack trace (development only)",
    "headers": {},
    "request_id": "req_456"
  }
}
```

## 常见错误类型

### 1. 认证错误 (401)

```bash
curl -X POST http://localhost:4000/api/openai \
  -H "Content-Type: application/json" \
  -d '{
    "open_ai_url": "https://generativelanguage.googleapis.com/v1beta/openai",
    "api_key": "invalid_key",
    "payload": {
      "model": "gemini-2.0-flash-exp",
      "messages": [{"role": "user", "content": "Hello"}]
    }
  }'
```

### 2. 无效模型错误 (400)

```bash
curl -X POST http://localhost:4000/api/openai \
  -H "Content-Type: application/json" \
  -d '{
    "open_ai_url": "https://generativelanguage.googleapis.com/v1beta/openai",
    "api_key": "valid_key",
    "payload": {
      "model": "invalid-model-name",
      "messages": [{"role": "user", "content": "Hello"}]
    }
  }'
```

### 3. 速率限制错误 (429)

当请求过于频繁时会返回速率限制错误。

### 4. 网络连接错误 (500)

当无法连接到 API 服务时返回网络错误。

## 调试技巧

### 1. 使用调试端点

```bash
curl -X POST http://localhost:4000/api/debug \
  -H "Content-Type: application/json" \
  -d '{
    "test": "debug request"
  }'
```

### 2. 设置开发环境

```bash
NODE_ENV=development PORT=4000 npm start
```

在开发环境下会显示完整的错误堆栈信息。

### 3. 检查服务器日志

所有错误都会在服务器端打印详细日志：

```text
API Error: OpenAIError: Invalid API key provided
    at new OpenAIError (/path/to/error)
    ...
```

## 错误排查步骤

1. **检查 API 密钥**：确保 API 密钥有效且有相应权限
2. **验证 URL**：确保使用正确的 baseURL
3. **检查模型名称**：确认模型名称正确且可用
4. **查看速率限制**：检查是否超过 API 调用限制
5. **网络连接**：确认能够访问目标 API 服务
6. **参数验证**：检查请求参数格式是否正确

## 示例：完整错误响应

```json
{
  "error": "The model `invalid-model` does not exist",
  "type": "invalid_request_error",
  "code": "model_not_found",
  "param": "model",
  "details": {
    "status": 404,
    "headers": {
      "content-type": "application/json",
      "x-request-id": "req_123abc"
    },
    "request_id": "req_123abc",
    "response": {
      "error": {
        "message": "The model `invalid-model` does not exist",
        "type": "invalid_request_error",
        "param": "model",
        "code": "model_not_found"
      }
    }
  },
  "raw_error": {
    "name": "NotFoundError",
    "message": "The model `invalid-model` does not exist"
  }
}
```
