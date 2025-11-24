# Chat System API 使用指南

## 安装和配置

### 1. 运行数据库迁移

```bash
php artisan migrate
```

### 2. 发布配置文件（如需要）

```bash
php artisan vendor:publish --provider="App\Providers\ChatServiceProvider"
```

## API 端点和用法

### 聊天管理 API

#### 1. 创建新聊天

```http
POST /api/v2/chats
Content-Type: application/json

{
    "title": "天气查询对话",
    "user_id": "ba5463f3-72d1-4410-858e-eadd10884713"
}
```

**响应示例:**

```json
{
  "ok": true,
  "message": "ok",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "title": "天气查询对话",
    "user_id": "ba5463f3-72d1-4410-858e-eadd10884713",
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T10:30:00.000000Z"
  }
}
```

#### 2. 获取聊天列表

```http
GET /api/v2/v2/chats?limit=20&user_id=123
```

#### 3. 获取单个聊天详情

```http
GET /api/v2/v2/chats/{chat_uid}
```

#### 4. 更新聊天标题

```http
PUT /api/v2/chats/{chat_uid}
Content-Type: application/json

{
    "title": "更新后的标题"
}
```

#### 5. 删除聊天

```http
DELETE /api/v2/chats/{chat_uid}
```

### 消息管理 API

消息不允许更新和删除，只能新建

#### 1. 获取消息列表

```http
GET /api/v2/chat-messages?chat=123
```

**响应示例:**

```json
{
  "ok": true,
  "message": "ok",
  "data": {
    "rows": [
      {
        "id": 1,
        "uid": "message-1",
        "role": "user",
        "content": "查询今天北京的天气",
        "session_id": "session-1",
        "editor_id": "editor-uuid-456"
      },
      {
        "id": 2,
        "uid": "message-2",
        "session_id": "session-2",
        "parent_id": "message-1",
        "role": "assistant",
        "model_id": "gpt-4",
        "tool_calls": [
          {
            "id": "call_123",
            "function": "get_weather",
            "arguments": {
              "city": "北京",
              "date": "today"
            }
          }
        ]
      },
      {
        "id": 3,
        "uid": "message-3",
        "session_id": "session-2",
        "parent_id": "message-2",
        "role": "tool",
        "content": "今天北京天气晴朗，气温25°C，湿度60%，风力3级",
        "tool_call_id": "call_123"
      },
      {
        "id": 4,
        "uid": "message-4",
        "session_id": "session-2",
        "parent_id": "message-3",
        "role": "tool",
        "content": "今天北京天气晴朗，气温25°C，湿度60%，风力3级",
        "tool_call_id": "call_123",
        "model_id": "model-uuid-123",
        "metadata": {
          "temperature": 0.7,
          "tokens": 150,
          "max_tokens": 1000
        }
      }
    ],
    "total": 4
  }
}
```

#### 1. 创建用户消息

```http
POST /api/v2/chat-messages
Content-Type: application/json

{
    "chat_id":"1234",
    "messages": [
        {
            "role": "user",
            "content": "查询今天北京的天气"
        }
    ]

}
```

#### 2. 创建带工具调用的 Assistant 消息

工具调用的 Assistant 消息,工具结果消息,最终回复消息，应该在结果正常返回后一次发送建立

```http
POST /api/v2/chats/{chat_uid}/messages
Content-Type: application/json

{
    "chat_id":"1234",
    "messages": [
            {
                "parent_id": "user-message-uuid",
                "role": "assistant",
                "model_id": "gpt-4",
                "tool_calls": [
                    {
                        "id": "call_123",
                        "function": "get_weather",
                        "arguments": {
                            "city": "北京",
                            "date": "today"
                        }
                    }
                ]
            },
            {
                "parent_id": "assistant-message-uuid",
                "role": "tool",
                "content": "今天北京天气晴朗，气温25°C，湿度60%，风力3级",
                "tool_call_id": "call_123"
            },
            {
                "parent_id": "assistant-message-uuid",
                "role": "tool",
                "content": "今天北京天气晴朗，气温25°C，湿度60%，风力3级",
                "tool_call_id": "call_123"
            },
    ]
}
```

## 典型业务场景示例

### 场景 1: 完整的 Function Call 对话流程

````javascript
// 1. 创建聊天
const chatResponse = await fetch("/api/v2/chats", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
        title: "天气查询助手",
    }),
});
const chat = await chatResponse.json();

// 2. 用户提问
const userMessage = await fetch(`/api/v2/chat-messages`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
        chat_id: "chat-id",
        messages: [
            {
                role: "user",
                content: "今天上海天气怎么样？",
            },
        ],
    }),
});
const userMsg = await userMessage.json();

// 3. Assistant 调用工具

const assistantCall = await fetch(`/api/v2/chats-messages`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
        chat_id: "chat-id",
        messages: [
            {
                parent_id: userMsg.data.id,
                role: "assistant",
                model_name: "gpt-4",
                tool_calls: [
                    {
                        id: "call_weather_001",
                        function: "get_weather",
                        arguments: { city: "上海", date: "today" },
                    },
                ],
            },
            {
                parent_id: assistantMsg.data.id,
                role: "tool",
                content: "上海今日天气：晴，22-28°C，东南风3级，湿度65%",
                tool_call_id: "call_weather_001",
            },
            {
                parent_id: toolMsg.data.id,
                role: "assistant",
                content:
                    "今天上海的天气很不错！晴天，气温在22-28度之间，很适合外出。东南风3级，体感舒适，湿度适中。建议穿轻薄的长袖或短袖都可以。",
                model_id: 'model-uuid-123',
                metadata: {
                    temperature: 0.7,
                    tokens: 150,
                    max_tokens: 1000
                },
            },
        ],
    }),
});
const assistantMsg = await assistantCall.json();



## 数据模型说明

### Chat 模型

-   `id`: 数据库自增 ID
-   `uid`: UUID 标识符，用于 API 调用
-   `title`: 聊天标题
-   `user_id`: 用户 ID（可选）
-   `created_at/updated_at`: 时间戳

### ChatMessage 模型

-   `id`: 数据库自增 ID（用于版本排序，替代 version 字段）
-   `uid`: UUID 标识符，用于 API 调用
-   `chat_id`: 所属聊天 ID
-   `parent_id`: 父消息 UUID（构建依赖关系）
-   `session_id`: 会话组 UUID（前端显示分组）
-   `role`: 消息角色（user/assistant/tool）
-   `content`: 消息内容
-   `model_id`: 使用的 AI 模型 UUID
-   `tool_calls`: 工具调用信息（JSON 格式）
-   `tool_call_id`: 工具调用 ID（tool 消息专用）
-   `metadata`: 元数据信息（temperature、tokens 等，JSON 格式）
-   `editor_id`: 编辑者 UUID
-   `is_active`: 是否为当前激活版本

## 重要变更说明

### 1. 版本控制机制改变

-   **移除**: `version`字段
-   **新机制**: 使用数据库自增 ID 自然排序实现版本控制
-   **优势**: 避免多浏览器并发冲突，ID 天然递增保证版本顺序

### 2. 新增字段

-   `metadata`: JSON 字段存储模型参数和执行信息
-   `editor_id`: UUID 字段记录编辑者信息
-   `model_id`: 改为 UUID 格式，便于模型管理

### 3. 字段类型调整

-   `user_id`: 从 BIGINT 改为 UUID 格式
-   `model_name` → `model_id`: 名称改为 UUID 引用

## 错误处理

API 使用标准 HTTP 状态码：

-   `200`: 操作成功
-   `201`: 资源创建成功
-   `400`: 请求参数错误
-   `404`: 资源不存在
-   `422`: 验证失败
-   `500`: 服务器错误

错误响应格式：

```json
{
    "ok":false,
    "message": "错误描述",
    "errors": {
        "field_name": ["具体错误信息"]
    }
}
````

## 性能优化建议

1. **分页查询**: 大量消息时使用分页参数`limit`
2. **索引优化**: 数据库已包含必要索引
3. **缓存策略**: 对频繁查询的聊天历史考虑使用 Redis 缓存
4. **批量操作**: 使用批量更新 API 减少网络请求
5. **懒加载**: 前端可按需加载消息版本和历史记录

## 安全考虑

1. **权限验证**: 根据需要添加用户认证中间件
2. **输入验证**: 所有输入都经过 FormRequest 验证
3. **SQL 注入防护**: 使用 Eloquent ORM 和参数化查询
4. **XSS 防护**: 输出时进行适当转义
5. **CSRF 保护**: API 接口建议配置 CSRF 中间件

## 扩展功能

系统支持以下扩展：

1. **多用户支持**: 通过 user_id 字段隔离数据
2. **消息搜索**: 可在 content 字段上建立全文索引
3. **文件附件**: content 字段支持 JSON 格式存储多媒体内容
4. **消息导出**: 可导出完整对话历史
