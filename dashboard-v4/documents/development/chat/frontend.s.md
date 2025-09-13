# AI Chat 前端重构设计文档

## 1. 项目概述

本项目旨在重构一个类似 Claude 的 AI 聊天系统前端，支持以下核心功能：

- 多轮对话管理和消息树结构
- Function Call 集成（支持多厂商模型）
- 消息版本控制和切换
- 实时流式输出和打字机效果
- 消息编辑、刷新、点赞、分享等操作
- 乐观更新和失败重试机制
- Mock 数据支持便于开发调试
- 与后端 API 的完整集成

## 2. 核心设计理念

### 2.1 数据流设计

- **线性存储**：从数据库加载的消息为线性数组，无需构建树结构
- **激活路径**：通过 `is_active` 字段标记用户当前查看的消息链
- **Session 分组**：相同 `session_id` 的消息在前端显示为一个对话组
- **乐观更新**：用户操作立即响应，异步同步到服务器

### 2.2 版本管理

- 同一 `parent_id` 的消息作为不同版本
- 用户可通过版本切换器查看历史版本
- 编辑和刷新都创建新版本，不修改原消息
- 切换版本，根据 session_id 和版本序号，从现有版本列表中搜索当前版本链，

### 2.3 错误处理

- 手动重试机制，不自动重试
- 临时消息状态标记，区分已保存/待保存/失败状态
- 完整的错误恢复流程

## 3. 架构设计

```text
├── types/
│   └── chat.ts                # 所有类型定义
├── hooks/                     # 数据管理层
│   ├── useChatData.ts        # 主要数据管理
│   ├── useActivePath.ts      # 激活路径计算
│   ├── useSessionGroups.ts   # Session 分组管理
│   └── mockChatData.ts           # Mock 测试数据
├── services/                  # API调用层
│   ├── chatApi.ts            # 聊天 API
│   ├── messageApi.ts         # 消息 API
│   └── modelAdapters/        # 模型适配器
│       ├── base.ts           # 基础适配器
│       ├── openai.ts         # OpenAI 适配器
│       └── index.ts          # 适配器工厂
├── components/               #
│   └── chat                  # 显示层
│       ├── ChatContainer.tsx     # 主容器组件
│       ├── SessionGroup.tsx      # Session 消息组
│       ├── UserMessage.tsx       # 用户消息
│       ├── AssistantMessage.tsx  # AI 回答
│       ├── MessageActions.tsx    # 消息操作
│       ├── ChatInput.tsx         # 输入组件
│       └── VersionSwitcher.tsx   # 版本切换
└── mockChatData.ts           # Mock 测试数据
```

## 4. 类型定义

### types/chat.ts

```typescript
// 工具调用相关类型
export interface ToolCall {
  id: string;
  function: string;
  arguments: Record<string, any>;
}

// 消息元数据
export interface MessageMetadata {
  generation_params?: {
    temperature?: number;
    max_tokens?: number;
    top_p?: number;
    frequency_penalty?: number;
    presence_penalty?: number;
  };
  token_usage?: {
    prompt_tokens?: number;
    completion_tokens?: number;
    total_tokens?: number;
  };
  performance?: {
    response_time_ms?: number;
    first_token_time_ms?: number;
  };
  tool_stats?: {
    total_calls?: number;
    successful_calls?: number;
    execution_time_ms?: number;
  };
  custom_data?: Record<string, any>;
}

// 消息节点（对应数据库结构）
export interface MessageNode {
  id: number; // DB自增ID，用于版本排序
  uid: string; // UUID
  chat_id: string;
  parent_id?: string;
  session_id: string;
  role: "system" | "user" | "assistant" | "tool";
  content?: string;
  model_id?: string;
  tool_calls?: ToolCall[];
  tool_call_id?: string;
  metadata?: MessageMetadata;
  is_active: boolean;
  editor_id?: string;
  created_at: string;
  updated_at: string;
  deleted_at?: string;

  // 临时状态字段（前端使用）
  save_status?: "saved" | "pending" | "failed";
  temp_id?: string; // 临时ID，用于未保存消息
}

// 版本信息
export interface VersionInfo {
  version_index: number; // 版本索引（0,1,2...）
  model_id?: string; // 该版本使用的模型
  model_name?: string; // 模型显示名称
  created_at: string; // 版本创建时间
  message_count: number; // 该版本包含的消息数量
  token_usage?: number; // 该版本的token使用量
}

// Session 信息
export interface SessionInfo {
  session_id: string;
  messages: MessageNode[]; // 该session的所有消息（按激活路径过滤）
  versions: VersionInfo[]; // 该session所有版本信息
  current_version: number; // 当前显示的版本索引
  user_message?: MessageNode; // 该session的用户消息（便于访问）
  ai_messages: MessageNode[]; // 该session的AI消息列表
}

// 待保存消息组
export interface PendingMessage {
  temp_id: string;
  session_id: string;
  messages: MessageNode[]; // 待保存的消息组
  retry_count: number;
  error?: string;
  created_at: string;
}

// 聊天状态
export interface ChatState {
  chat_id: string;
  title: string;
  raw_messages: MessageNode[]; // 从DB加载的原始线性数据
  active_path: MessageNode[]; // 当前激活路径上的消息
  session_groups: SessionInfo[]; // 按session分组的显示数据
  pending_messages: PendingMessage[]; // 待保存的消息组
  is_loading: boolean;
  streaming_message?: string;
  streaming_session_id?: string;
  current_model: string;
  error?: string;
}

// 聊天操作接口
export interface ChatActions {
  switchVersion: (sessionId: string, versionIndex: number) => Promise<void>;
  editMessage: (
    sessionId: string,
    content: string,
    role?: "user" | "assistant"
  ) => Promise<void>;
  retryMessage: (tempId: string) => Promise<void>;
  refreshResponse: (sessionId: string, modelId?: string) => Promise<void>;
  loadMessages: () => Promise<void>;
  likeMessage: (messageId: string) => Promise<void>;
  dislikeMessage: (messageId: string) => Promise<void>;
  copyMessage: (messageId: string) => void;
  shareMessage: (messageId: string) => Promise<string>;
  deleteMessage: (messageId: string) => Promise<void>;
}

// API 请求类型
export interface CreateMessageRequest {
  messages: Array<{
    parent_id?: string;
    role: "user" | "assistant" | "tool";
    content?: string;
    model_id?: string;
    tool_calls?: ToolCall[];
    tool_call_id?: string;
    metadata?: MessageMetadata;
  }>;
}

export interface CreateChatRequest {
  title: string;
  user_id?: string;
}

// API 响应类型
export interface ApiResponse<T> {
  ok: boolean;
  message: string;
  data: T;
}

export interface ChatResponse {
  id: string;
  title: string;
  user_id?: string;
  created_at: string;
  updated_at: string;
}

export interface MessageListResponse {
  rows: MessageNode[];
  total: number;
}

// 模型适配器相关类型
export interface ModelAdapter {
  name: string;
  supportsFunctionCall: boolean;
  sendMessage(
    messages: OpenAIMessage[],
    options: SendOptions
  ): Promise<AsyncIterable<string>>;
  parseStreamChunk(chunk: string): ParsedChunk | null;
  handleFunctionCall(functionCall: ToolCall): Promise<any>;
}

export interface OpenAIMessage {
  role: "system" | "user" | "assistant" | "function" | "tool";
  content?: string;
  name?: string;
  tool_calls?: ToolCall[];
  tool_call_id?: string;
}

export interface SendOptions {
  temperature?: number;
  max_tokens?: number;
  top_p?: number;
  functions?: Array<{
    name: string;
    description: string;
    parameters: any;
  }>;
  function_call?: string | { name: string };
}

export interface ParsedChunk {
  content?: string;
  function_call?: {
    name?: string;
    arguments?: string;
  };
  finish_reason?: string;
}

// 组件 Props 类型
export interface SessionGroupProps {
  session: SessionInfo;
  onVersionSwitch: (sessionId: string, versionIndex: number) => void;
  onRefresh: (sessionId: string, modelId?: string) => void;
  onEdit: (sessionId: string, content: string) => void;
  onRetry?: (tempId: string) => void;
  onLike?: (messageId: string) => void;
  onDislike?: (messageId: string) => void;
  onCopy?: (messageId: string) => void;
  onShare?: (messageId: string) => Promise<string>;
}

export interface UserMessageProps {
  message: MessageNode;
  onEdit?: (content: string) => void;
  onCopy?: () => void;
}

export interface AssistantMessageProps {
  messages: MessageNode[];
  onRefresh?: () => void;
  onEdit?: (content: string) => void;
  isPending?: boolean;
  onLike?: (messageId: string) => void;
  onDislike?: (messageId: string) => void;
  onCopy?: (messageId: string) => void;
  onShare?: (messageId: string) => Promise<string>;
}

export interface VersionSwitcherProps {
  versions: VersionInfo[];
  currentVersion: number;
  onSwitch: (versionIndex: number) => void;
}

export interface ChatInputProps {
  onSend: (content: string) => void;
  disabled?: boolean;
  placeholder?: string;
}

// Mock 数据支持
export interface UseChatDataOptions {
  useMockData?: boolean;
  mockDelay?: number;
}
```

## 5. 数据管理层

### hooks/useActivePath.ts

**核心功能**：从线性消息数组计算当前激活的消息链

**算法说明**：

1. 找到 system 消息作为根节点
2. 沿着 `is_active=true` 的路径构建消息链
3. 通过 `parent_id` 关系链接父子消息
4. 返回完整的激活路径数组

**接口**：

```typescript
export function useActivePath(rawMessages: MessageNode[]): MessageNode[];
```

### hooks/useSessionGroups.ts

**核心功能**：将激活路径上的消息按 session_id 分组，计算版本信息

**算法说明**：

1. 按 `session_id` 对激活消息分组（排除 system 消息）
2. 为每个 session 计算版本信息：
   - 从 raw_messages 中找到相同 parent_id 的不同版本
   - 按创建时间排序版本
   - 计算当前激活版本的索引
3. 构建 SessionInfo 结构，包含用户消息和 AI 消息

**接口**：

```typescript
export function useSessionGroups(
  activePath: MessageNode[],
  rawMessages: MessageNode[]
): SessionInfo[];
```

### hooks/useChatData.ts

**核心功能**：主要的聊天数据管理 Hook，支持 Mock 模式

**主要职责**：

- 管理聊天状态（消息、pending、loading 等）
- 处理用户操作（发送、编辑、重试等）
- 支持 Mock 数据模式便于开发调试
- 协调 API 调用和本地状态更新

**接口**：

```typescript
export function useChatData(
  chatId: string,
  options?: UseChatDataOptions
): { chatState: ChatState; actions: ChatActions };
```

**Mock 模式支持**：

- `useMockData: true` 启用 Mock 模式
- 模拟 API 延迟和流式响应
- 提供完整的测试数据集

## 6. API 调用层

### services/chatApi.ts

**功能**：聊天会话相关的 API 调用

**主要接口**：

- `createChat(request: CreateChatRequest): Promise<ApiResponse<ChatResponse>>`
- `getChat(chatId: string): Promise<ApiResponse<ChatResponse>>`
- `updateChat(chatId: string, updates: Partial<CreateChatRequest>): Promise<ApiResponse<ChatResponse>>`
- `deleteChat(chatId: string): Promise<ApiResponse<void>>`

### services/messageApi.ts

**功能**：消息相关的 API 调用

**主要接口**：

- `getMessages(chatId: string): Promise<ApiResponse<MessageListResponse>>`
- `createMessages(chatId: string, request: CreateMessageRequest): Promise<ApiResponse<MessageNode[]>>`
- `switchVersion(chatId: string, messageUids: string[]): Promise<ApiResponse<void>>`
- `likeMessage/dislikeMessage/shareMessage/deleteMessage`: 消息操作接口

### services/modelAdapters/

**设计模式**：适配器模式，支持多模型厂商

#### base.ts

**功能**：定义模型适配器的基础抽象类

**核心方法**：

- `sendMessage`: 发送消息，返回流式响应
- `parseStreamChunk`: 解析流数据块
- `handleFunctionCall`: 处理函数调用

#### openai.ts

**功能**：OpenAI 模型的具体实现

**关键特性**：

- 支持流式响应处理
- Function Call 循环处理逻辑
- 错误处理和重试机制

**实现要点**：

```typescript
// sendMessage 返回 Promise<AsyncIterable<string>>
async sendMessage(messages: OpenAIMessage[], options: SendOptions): Promise<AsyncIterable<string>> {
  // 创建内部流生成器
  return this.createStreamIterable(payload);
}

private async *createStreamIterable(payload: any): AsyncIterable<string> {
  // 实际的流处理逻辑
}
```

#### index.ts

**功能**：适配器工厂，管理不同模型的适配器实例

**主要接口**：

- `getModelAdapter(modelId: string): ModelAdapter`
- `registerAdapter(modelId: string, adapter: ModelAdapter): void`

## 7. 显示层组件

### components/ChatContainer.tsx

**功能**：主容器组件，协调整个聊天界面

**职责**：

- 初始化和管理聊天数据
- 处理用户操作事件
- 渲染消息列表和输入区域
- 错误状态显示

**Props**：`{ chatId: string }`

### components/SessionGroup.tsx

**功能**：渲染单个对话 session，包含用户问题和 AI 完整回答

**核心特性**：

- 显示用户消息和 AI 消息组
- 版本切换控制
- 失败重试 UI
- 消息状态指示（pending、failed）

**Props**：`SessionGroupProps`

### components/UserMessage.tsx

**功能**：用户消息显示和编辑

**特性**：

- 内联编辑功能
- 消息状态显示
- 复制功能

**Props**：`UserMessageProps`

### components/AssistantMessage.tsx

**功能**：AI 回答显示，支持多消息组合（Function Call 场景）

**特性**：

- 显示主要回答内容
- Tool Call 结果展示
- 消息操作按钮（点赞、复制、分享等）
- Token 使用量显示

**Props**：`AssistantMessageProps`

### components/VersionSwitcher.tsx

**功能**：版本切换控制器

**特性**：

- 前后版本导航
- 版本信息提示（模型、时间、Token）
- 当只有一个版本时自动隐藏

**Props**：`VersionSwitcherProps`

### components/ChatInput.tsx

**功能**：用户输入组件

**特性**：

- 多行文本输入
- Enter 发送，Shift+Enter 换行
- 附件上传按钮（预留）
- 发送状态控制

**Props**：`ChatInputProps`

## 8. Mock 数据支持

### mockChatData.ts

**功能**：提供完整的测试数据集，支持各种场景

**数据场景**：

- 完整的 Function Call 对话流程
- 多版本消息示例
- 分支对话（用户编辑问题）
- 各种消息状态（pending、failed、streaming）

**主要导出**：

- `mockChatState`: 正常对话状态
- `mockChatStateWithPending`: 带待发送消息
- `mockChatStateWithError`: 错误状态
- `mockChatStateStreaming`: 流式输出状态

**工具函数**：

- `getActivePath()`: 获取激活路径
- `mockApiDelay()`: 模拟网络延迟
- `mockStreamResponse()`: 模拟流式响应
- `mockFunctionCallResponse`: 模拟函数调用结果

## 9. 核心流程说明

### 9.1 消息发送流程

1. **用户输入** → `ChatInput.onSend` → `actions.editMessage('new', content)`
2. **创建临时消息** → 乐观更新 UI，显示 pending 状态
3. **调用 AI API** → 支持 Mock 模式和真实 API 模式
4. **流式显示** → 实时更新 `streaming_message` 状态
5. **Function Call 处理** → 循环处理工具调用，避免递归
6. **保存到数据库** → 批量保存整个对话组
7. **状态同步** → 更新本地状态，移除临时标记

### 9.2 版本切换流程

1. **用户点击版本按钮** → `VersionSwitcher.onSwitch`
2. **计算目标版本** → 找到对应版本的消息组
3. **调用 API 更新** → 更新数据库激活状态
4. **重新加载数据** → 刷新本地状态
5. **重新渲染** → 显示新版本内容

### 9.3 Mock 模式开发

**启用方式**：

```typescript
const { chatState, actions } = useChatData(chatId, {
  useMockData: process.env.NODE_ENV === "development",
  mockDelay: 500,
});
```

**Mock 特性**：

- 模拟 API 延迟
- 模拟流式输出效果
- 模拟 Function Call 处理
- 模拟各种错误状态

## 10. 性能优化

### 10.1 渲染优化

- React.memo 防止不必要重渲染
- 消息列表虚拟化（长对话）
- 懒加载历史消息版本

### 10.2 状态管理优化

- 最小化状态更新粒度
- 使用 useCallback/useMemo 优化计算
- 及时清理临时状态

### 10.3 网络优化

- 请求去重和缓存
- 批量 API 调用
- 流式响应优化

## 11. 开发建议

### 11.1 开发顺序

1. **建立项目结构**：创建文件夹和基础文件
2. **实现类型定义**：复制完整的 types/chat.ts
3. **创建 Mock 数据**：使用提供的 mockChatData.ts
4. **开发基础 Hook**：先实现 useActivePath 和 useSessionGroups
5. **实现 UI 组件**：从简单组件开始（ChatInput、UserMessage）
6. **集成 Mock 模式**：完善 useChatData 的 Mock 支持
7. **真实 API 集成**：替换 Mock 调用为真实 API

### 11.2 调试策略

- 使用 Mock 模式进行 UI 开发
- 逐步验证数据流转换
- 测试各种边界情况（错误、重试、版本切换）

### 11.3 测试覆盖

- Hook 逻辑单元测试
- 组件渲染测试
- Mock 数据完整性验证
- 用户交互流程测试

这个设计提供了完整的架构蓝图，支持快速开发和调试，同时保持了良好的可维护性和扩展性。Mock 数据支持让开发者无需依赖后端 API 即可完成前端功能开发。
