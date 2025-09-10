# AI Chat 前端重构设计文档

## 1. 项目概述

本项目旨在重构一个类似 Claude 的 AI 聊天系统前端，支持以下核心功能：

- 多轮对话管理和消息树结构
- Function Call 集成（支持多厂商模型）
- 消息版本控制和切换
- 实时流式输出和打字机效果
- 消息编辑、刷新、点赞、分享等操作
- 乐观更新和失败重试机制
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
- 组件加载时载入全部 message, 版本切换时无需请求服务器，在已有的 message 数组中计算新的当前激活版本

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
│   └── useSessionGroups.ts   # Session 分组管理
├── services/                  # API调用层
│   ├── chatApi.ts            # 聊天 API
│   ├── messageApi.ts         # 消息 API
│   └── modelAdapters/        # 模型适配器
│       ├── base.ts           # 基础适配器
│       ├── openai.ts         # OpenAI 适配器
│       └── index.ts          # 适配器工厂
└── components/               # 显示层
    └── chat
        ├── ChatContainer.tsx     # 主容器组件
        ├── SessionGroup.tsx      # Session 消息组
        ├── UserMessage.tsx       # 用户消息
        ├── AssistantMessage.tsx  # AI 回答
        ├── MessageActions.tsx    # 消息操作
        ├── ChatInput.tsx         # 输入组件
        ├── StreamingMessage.tsx  # 打字机输入组件
        └── VersionSwitcher.tsx   # 版本切换
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
  ): Promise<StreamResponse>;
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

export interface StreamResponse {
  messages: MessageNode[];
  metadata?: MessageMetadata;
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
  onShare?: (messageId: string) => void;
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
  onShare?: (messageId: string) => void;
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
```

## 5. 数据管理层

### hooks/useActivePath.ts

```typescript
import { useMemo, useCallback } from "react";
import { MessageNode } from "../types/chat";

export function useActivePath(rawMessages: MessageNode[]) {
  const computeActivePath = useCallback(() => {
    // 从system消息开始，沿着is_active=true的路径构建激活链
    const messageMap = new Map(rawMessages.map((m) => [m.uid, m]));
    const activePath: MessageNode[] = [];

    // 找到system消息（根节点）
    const systemMsg = rawMessages.find(
      (m) => m.role === "system" && !m.parent_id && m.is_active
    );
    if (!systemMsg) return [];

    // 沿着激活路径构建链
    let current: MessageNode | undefined = systemMsg;
    while (current) {
      activePath.push(current);

      // 找到当前消息的激活子消息
      current = rawMessages.find(
        (m) => m.parent_id === current?.uid && m.is_active
      );
    }

    return activePath;
  }, [rawMessages]);

  return useMemo(() => computeActivePath(), [computeActivePath]);
}
```

### hooks/useSessionGroups.ts

```typescript
import { useMemo, useCallback } from "react";
import { MessageNode, SessionInfo, VersionInfo } from "../types/chat";

export function useSessionGroups(
  activePath: MessageNode[],
  rawMessages: MessageNode[]
) {
  const computeSessionVersions = useCallback(
    (sessionId: string): VersionInfo[] => {
      // 找到该session的所有消息
      const sessionMessages = rawMessages.filter(
        (m) => m.session_id === sessionId
      );

      // 按不同的创建时间和父消息分组，计算版本
      const versionMap = new Map<string, MessageNode[]>();

      sessionMessages.forEach((msg) => {
        // 使用第一个AI消息的创建时间作为版本标识
        const firstAiMsg = sessionMessages
          .filter((m) => m.role === "assistant")
          .sort((a, b) => a.id - b.id)[0];

        const versionKey = firstAiMsg ? firstAiMsg.created_at : msg.created_at;

        if (!versionMap.has(versionKey)) {
          versionMap.set(versionKey, []);
        }
        versionMap.get(versionKey)!.push(msg);
      });

      // 转换为VersionInfo数组
      const versions: VersionInfo[] = Array.from(versionMap.entries())
        .map(([timestamp, messages], index) => {
          const aiMessage = messages.find((m) => m.role === "assistant");
          return {
            version_index: index,
            model_id: aiMessage?.model_id,
            created_at: timestamp,
            message_count: messages.length,
            token_usage: aiMessage?.metadata?.token_usage?.total_tokens,
          };
        })
        .sort(
          (a, b) =>
            new Date(a.created_at).getTime() - new Date(b.created_at).getTime()
        );

      return versions;
    },
    [rawMessages]
  );

  const findCurrentVersion = useCallback(
    (sessionMessages: MessageNode[], versions: VersionInfo[]): number => {
      // 找到当前激活的AI消息
      const activeAiMsg = sessionMessages.find(
        (m) => m.role === "assistant" && m.is_active
      );
      if (!activeAiMsg) return 0;

      // 根据创建时间找到对应的版本索引
      const versionIndex = versions.findIndex(
        (v) => v.created_at === activeAiMsg.created_at
      );
      return Math.max(0, versionIndex);
    },
    []
  );

  const computeSessionGroups = useCallback((): SessionInfo[] => {
    const sessionMap = new Map<string, MessageNode[]>();

    // 按session_id分组激活路径上的消息（排除system消息）
    activePath.forEach((msg) => {
      if (msg.role !== "system") {
        const sessionId = msg.session_id;
        if (!sessionMap.has(sessionId)) {
          sessionMap.set(sessionId, []);
        }
        sessionMap.get(sessionId)!.push(msg);
      }
    });

    // 为每个session计算版本信息
    const sessionGroups: SessionInfo[] = [];

    sessionMap.forEach((messages, sessionId) => {
      const versions = computeSessionVersions(sessionId);
      const currentVersion = findCurrentVersion(messages, versions);

      const userMessage = messages.find((m) => m.role === "user");
      const aiMessages = messages.filter((m) => m.role !== "user");

      sessionGroups.push({
        session_id: sessionId,
        messages,
        versions,
        current_version: currentVersion,
        user_message: userMessage,
        ai_messages: aiMessages,
      });
    });

    // 按消息ID排序，保证显示顺序
    return sessionGroups.sort((a, b) => {
      const aFirstId = Math.min(...a.messages.map((m) => m.id));
      const bFirstId = Math.min(...b.messages.map((m) => m.id));
      return aFirstId - bFirstId;
    });
  }, [activePath, rawMessages, computeSessionVersions, findCurrentVersion]);

  return useMemo(() => computeSessionGroups(), [computeSessionGroups]);
}
```

### hooks/useChatData.ts

```typescript
import { useState, useCallback, useMemo } from "react";
import {
  MessageNode,
  ChatState,
  ChatActions,
  PendingMessage,
} from "../types/chat";
import { useActivePath } from "./useActivePath";
import { useSessionGroups } from "./useSessionGroups";
import { chatApi, messageApi } from "../services";
import { getModelAdapter } from "../services/modelAdapters";

export function useChatData(chatId: string): {
  chatState: ChatState;
  actions: ChatActions;
} {
  const [rawMessages, setRawMessages] = useState<MessageNode[]>([]);
  const [pendingMessages, setPendingMessages] = useState<PendingMessage[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [streamingMessage, setStreamingMessage] = useState<string>();
  const [streamingSessionId, setStreamingSessionId] = useState<string>();
  const [currentModel, setCurrentModel] = useState("gpt-4");
  const [error, setError] = useState<string>();

  // 合并已保存和待保存的消息用于显示
  const allMessages = useMemo(() => {
    const pending = pendingMessages.flatMap((p) => p.messages);
    return [...rawMessages, ...pending];
  }, [rawMessages, pendingMessages]);

  const activePath = useActivePath(allMessages);
  const sessionGroups = useSessionGroups(activePath, allMessages);

  // 加载消息列表
  const loadMessages = useCallback(async () => {
    try {
      setIsLoading(true);
      const response = await messageApi.getMessages(chatId);
      setRawMessages(response.data.rows);
    } catch (err) {
      setError(err instanceof Error ? err.message : "加载消息失败");
    } finally {
      setIsLoading(false);
    }
  }, [chatId]);

  // 构建对话历史（用于AI API调用）
  const buildConversationHistory = useCallback(
    (baseMessages: MessageNode[], newUserMessage?: MessageNode) => {
      const history = activePath
        .filter((m) => m.role !== "tool") // 排除tool消息
        .map((m) => ({
          role: m.role as any,
          content: m.content || "",
          tool_calls: m.tool_calls,
          tool_call_id: m.tool_call_id,
        }));

      if (newUserMessage) {
        history.push({
          role: "user",
          content: newUserMessage.content || "",
        });
      }

      return history;
    },
    [activePath]
  );

  // 发送消息给AI并处理响应
  const sendMessageToAI = useCallback(
    async (userMessage: MessageNode, pendingGroup: PendingMessage) => {
      try {
        setIsLoading(true);
        setStreamingSessionId(pendingGroup.session_id);

        const conversationHistory = buildConversationHistory(
          rawMessages,
          userMessage
        );
        const adapter = getModelAdapter(currentModel);

        // 处理Function Call的循环逻辑
        let currentMessages = conversationHistory;
        let maxIterations = 10;
        let allAiMessages: MessageNode[] = [];

        while (maxIterations-- > 0) {
          // 流式处理AI响应
          let responseContent = "";
          let functionCalls: any[] = [];
          let metadata: any = {};

          const streamResponse = await adapter.sendMessage(currentMessages, {
            temperature: 0.7,
            max_tokens: 2048,
          });

          // 模拟流式输出处理
          await new Promise((resolve, reject) => {
            const processStream = async () => {
              try {
                // 这里应该是实际的流处理逻辑
                for await (const chunk of streamResponse) {
                  const parsed = adapter.parseStreamChunk(chunk);
                  if (parsed?.content) {
                    responseContent += parsed.content;
                    setStreamingMessage(responseContent);
                  }
                  if (parsed?.function_call) {
                    // 处理function call
                  }
                }
                resolve(undefined);
              } catch (err) {
                reject(err);
              }
            };
            processStream();
          });

          // 创建AI响应消息
          const aiMessage: MessageNode = {
            id: 0,
            uid: `temp_ai_${pendingGroup.temp_id}_${allAiMessages.length}`,
            temp_id: pendingGroup.temp_id,
            chat_id: chatId,
            session_id: pendingGroup.session_id,
            parent_id:
              allAiMessages.length === 0
                ? userMessage.uid
                : allAiMessages[allAiMessages.length - 1].uid,
            role: "assistant",
            content: responseContent,
            model_id: currentModel,
            tool_calls: functionCalls.length > 0 ? functionCalls : undefined,
            metadata,
            is_active: true,
            save_status: "pending",
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString(),
          };

          allAiMessages.push(aiMessage);

          // 如果有function calls，处理它们
          if (functionCalls.length > 0) {
            const toolResults = await Promise.all(
              functionCalls.map((call) => adapter.handleFunctionCall(call))
            );

            const toolMessages = functionCalls.map((call, index) => ({
              id: 0,
              uid: `temp_tool_${pendingGroup.temp_id}_${index}`,
              temp_id: pendingGroup.temp_id,
              chat_id: chatId,
              session_id: pendingGroup.session_id,
              parent_id: aiMessage.uid,
              role: "tool" as const,
              content: JSON.stringify(toolResults[index]),
              tool_call_id: call.id,
              is_active: true,
              save_status: "pending" as const,
              created_at: new Date().toISOString(),
              updated_at: new Date().toISOString(),
            }));

            allAiMessages.push(...toolMessages);

            // 更新对话历史，继续循环
            currentMessages.push(
              {
                role: "assistant",
                content: responseContent,
                tool_calls: functionCalls,
              },
              ...toolMessages.map((tm) => ({
                role: "tool" as const,
                content: tm.content || "",
                tool_call_id: tm.tool_call_id,
              }))
            );

            continue;
          }

          // 没有function call，结束循环
          break;
        }

        // 更新pending消息组
        setPendingMessages((prev) =>
          prev.map((p) =>
            p.temp_id === pendingGroup.temp_id
              ? { ...p, messages: [...p.messages, ...allAiMessages] }
              : p
          )
        );

        // 保存整个消息组到数据库
        await saveMessageGroup(pendingGroup.temp_id, [
          userMessage,
          ...allAiMessages,
        ]);
      } catch (err) {
        console.error("AI响应失败:", err);
        setPendingMessages((prev) =>
          prev.map((p) =>
            p.temp_id === pendingGroup.temp_id
              ? {
                  ...p,
                  error: err instanceof Error ? err.message : "未知错误",
                  retry_count: p.retry_count + 1,
                }
              : p
          )
        );
      } finally {
        setIsLoading(false);
        setStreamingMessage(undefined);
        setStreamingSessionId(undefined);
      }
    },
    [rawMessages, currentModel, chatId, buildConversationHistory]
  );

  // 保存消息组到数据库
  const saveMessageGroup = useCallback(
    async (tempId: string, messages: MessageNode[]) => {
      try {
        const savedMessages = await messageApi.createMessages(chatId, {
          messages: messages.map((m) => ({
            parent_id: m.parent_id,
            role: m.role as any,
            content: m.content,
            model_id: m.model_id,
            tool_calls: m.tool_calls,
            tool_call_id: m.tool_call_id,
            metadata: m.metadata,
          })),
        });

        // 更新本地状态：移除pending，添加到已保存消息
        setPendingMessages((prev) => prev.filter((p) => p.temp_id !== tempId));
        setRawMessages((prev) => [...prev, ...savedMessages.data]);
      } catch (err) {
        console.error("保存消息组失败:", err);
        setPendingMessages((prev) =>
          prev.map((p) =>
            p.temp_id === tempId
              ? {
                  ...p,
                  error: err instanceof Error ? err.message : "保存失败",
                  messages: p.messages.map((m) => ({
                    ...m,
                    save_status: "failed" as const,
                  })),
                }
              : p
          )
        );
      }
    },
    [chatId]
  );

  // 编辑消息 - 创建新版本
  const editMessage = useCallback(
    async (
      sessionId: string,
      content: string,
      role: "user" | "assistant" = "user"
    ) => {
      const tempId = `temp_${Date.now()}`;

      try {
        // 找到要编辑的消息的父消息
        let parentId: string | undefined;

        if (sessionId === "new") {
          // 新消息，找到最后一个激活消息作为父消息
          const lastMessage = activePath[activePath.length - 1];
          parentId = lastMessage?.uid;
        } else {
          // 编辑现有session，找到该session的父消息
          const sessionMessages = activePath.filter(
            (m) => m.session_id === sessionId
          );
          const firstMessage = sessionMessages[0];
          parentId = firstMessage?.parent_id;
        }

        const newSessionId =
          sessionId === "new" ? `session_${tempId}` : `session_${tempId}`;

        // 创建新的用户消息
        const newUserMessage: MessageNode = {
          id: 0,
          uid: `temp_user_${tempId}`,
          temp_id: tempId,
          chat_id: chatId,
          parent_id: parentId,
          session_id: newSessionId,
          role: "user",
          content,
          is_active: true,
          save_status: "pending",
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
        };

        // 创建待保存消息组
        const pendingGroup: PendingMessage = {
          temp_id: tempId,
          session_id: newSessionId,
          messages: [newUserMessage],
          retry_count: 0,
          created_at: new Date().toISOString(),
        };

        setPendingMessages((prev) => [...prev, pendingGroup]);

        // 如果是用户消息，发送给AI
        if (role === "user") {
          await sendMessageToAI(newUserMessage, pendingGroup);
        }
      } catch (err) {
        console.error("编辑消息失败:", err);
        setPendingMessages((prev) =>
          prev.map((p) =>
            p.temp_id === tempId
              ? {
                  ...p,
                  messages: p.messages.map((m) => ({
                    ...m,
                    save_status: "failed" as const,
                  })),
                  error: err instanceof Error ? err.message : "编辑失败",
                }
              : p
          )
        );
      }
    },
    [chatId, activePath, sendMessageToAI]
  );

  // 重试失败的消息
  const retryMessage = useCallback(
    async (tempId: string) => {
      const pendingGroup = pendingMessages.find((p) => p.temp_id === tempId);
      if (!pendingGroup) return;

      const userMessage = pendingGroup.messages.find((m) => m.role === "user");
      if (!userMessage) return;

      // 重置状态并重试
      setPendingMessages((prev) =>
        prev.map((p) =>
          p.temp_id === tempId
            ? {
                ...p,
                messages: [{ ...userMessage, save_status: "pending" }],
                error: undefined,
              }
            : p
        )
      );

      await sendMessageToAI(userMessage, {
        ...pendingGroup,
        messages: [userMessage],
      });
    },
    [pendingMessages, sendMessageToAI]
  );

  // 切换版本
  const switchVersion = useCallback(
    async (sessionId: string, versionIndex: number) => {
      try {
        // 找到指定版本的消息
        const sessionMessages = rawMessages.filter(
          (m) => m.session_id === sessionId
        );
        const versions =
          sessionGroups.find((sg) => sg.session_id === sessionId)?.versions ||
          [];

        if (versionIndex >= versions.length) return;

        const targetVersion = versions[versionIndex];
        const versionMessages = sessionMessages.filter(
          (m) => m.created_at === targetVersion.created_at
        );

        // 调用API更新激活状态
        await messageApi.switchVersion(
          chatId,
          versionMessages.map((m) => m.uid)
        );

        // 重新加载数据
        await loadMessages();
      } catch (err) {
        console.error("切换版本失败:", err);
        setError(err instanceof Error ? err.message : "切换版本失败");
      }
    },
    [rawMessages, sessionGroups, chatId, loadMessages]
  );

  // 刷新AI回答
  const refreshResponse = useCallback(
    async (sessionId: string, modelId?: string) => {
      const session = sessionGroups.find((sg) => sg.session_id === sessionId);
      if (!session?.user_message) return;

      // 使用指定的模型或当前模型
      const useModel = modelId || currentModel;
      const tempId = `temp_refresh_${Date.now()}`;

      try {
        // 创建基于原用户消息的新AI回答
        const userMsg = session.user_message;
        const newSessionId = `session_${tempId}`;

        const pendingGroup: PendingMessage = {
          temp_id: tempId,
          session_id: newSessionId,
          messages: [
            {
              ...userMsg,
              temp_id: tempId,
              session_id: newSessionId,
              save_status: "pending",
            },
          ],
          retry_count: 0,
          created_at: new Date().toISOString(),
        };

        setPendingMessages((prev) => [...prev, pendingGroup]);

        // 发送给AI获取新回答
        await sendMessageToAI(pendingGroup.messages[0], pendingGroup);
      } catch (err) {
        console.error("刷新回答失败:", err);
        setError(err instanceof Error ? err.message : "刷新失败");
      }
    },
    [sessionGroups, currentModel, sendMessageToAI]
  );

  // 消息操作功能
  const likeMessage = useCallback(async (messageId: string) => {
    try {
      await messageApi.likeMessage(messageId);
      // 可以添加本地状态更新
    } catch (err) {
      console.error("点赞失败:", err);
    }
  }, []);

  const dislikeMessage = useCallback(async (messageId: string) => {
    try {
      await messageApi.dislikeMessage(messageId);
      // 可以添加本地状态更新
    } catch (err) {
      console.error("点踩失败:", err);
    }
  }, []);

  const copyMessage = useCallback(
    (messageId: string) => {
      const message = allMessages.find((m) => m.uid === messageId);
      if (message?.content) {
        navigator.clipboard.writeText(message.content);
      }
    },
    [allMessages]
  );

  const shareMessage = useCallback(
    async (messageId: string): Promise<string> => {
      try {
        const response = await messageApi.shareMessage(messageId);
        return response.data.shareUrl;
      } catch (err) {
        console.error("分享失败:", err);
        throw err;
      }
    },
    []
  );

  const deleteMessage = useCallback(
    async (messageId: string) => {
      try {
        await messageApi.deleteMessage(messageId);
        await loadMessages(); // 重新加载数据
      } catch (err) {
        console.error("删除失败:", err);
        setError(err instanceof Error ? err.message : "删除失败");
      }
    },
    [loadMessages]
  );

  return {
    chatState: {
      chat_id: chatId,
      title: "", // 可以从props传入或另行管理
      raw_messages: rawMessages,
      active_path: activePath,
      session_groups: sessionGroups,
      pending_messages: pendingMessages,
      is_loading: isLoading,
      streaming_message: streamingMessage,
      streaming_session_id: streamingSessionId,
      current_model: currentModel,
      error,
    },
    actions: {
      switchVersion,
      editMessage,
      retryMessage,
      refreshResponse,
      loadMessages,
      likeMessage,
      dislikeMessage,
      copyMessage,
      shareMessage,
      deleteMessage,
    },
  };
}
```

## 6. API 调用层

### services/chatApi.ts

```typescript
import { CreateChatRequest, ChatResponse, ApiResponse } from "../types/chat";

export const chatApi = {
  async createChat(
    request: CreateChatRequest
  ): Promise<ApiResponse<ChatResponse>> {
    const response = await fetch("/api/v2/chats", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(request),
    });
    return response.json();
  },

  async getChat(chatId: string): Promise<ApiResponse<ChatResponse>> {
    const response = await fetch(`/api/v2/chats/${chatId}`);
    return response.json();
  },

  async updateChat(
    chatId: string,
    updates: Partial<CreateChatRequest>
  ): Promise<ApiResponse<ChatResponse>> {
    const response = await fetch(`/api/v2/chats/${chatId}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(updates),
    });
    return response.json();
  },

  async deleteChat(chatId: string): Promise<ApiResponse<void>> {
    const response = await fetch(`/api/v2/chats/${chatId}`, {
      method: "DELETE",
    });
    return response.json();
  },
};
```

### services/messageApi.ts

```typescript
import {
  CreateMessageRequest,
  MessageListResponse,
  ApiResponse,
  MessageNode,
} from "../types/chat";

export const messageApi = {
  async getMessages(chatId: string): Promise<ApiResponse<MessageListResponse>> {
    const response = await fetch(`/api/v2/chat-messages?chat=${chatId}`);
    return response.json();
  },

  async createMessages(
    chatId: string,
    request: CreateMessageRequest
  ): Promise<ApiResponse<MessageNode[]>> {
    const response = await fetch("/api/v2/chat-messages", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        chat_id: chatId,
        ...request,
      }),
    });
    return response.json();
  },

  async switchVersion(
    chatId: string,
    messageUids: string[]
  ): Promise<ApiResponse<void>> {
    const response = await fetch("/api/v2/chat-messages/switch-version", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        chat_id: chatId,
        message_uids: messageUids,
      }),
    });
    return response.json();
  },

  async likeMessage(messageId: string): Promise<ApiResponse<void>> {
    const response = await fetch(`/api/v2/chat-messages/${messageId}/like`, {
      method: "POST",
    });
    return response.json();
  },

  async dislikeMessage(messageId: string): Promise<ApiResponse<void>> {
    const response = await fetch(`/api/v2/chat-messages/${messageId}/dislike`, {
      method: "POST",
    });
    return response.json();
  },

  async shareMessage(
    messageId: string
  ): Promise<ApiResponse<{ shareUrl: string }>> {
    const response = await fetch(`/api/v2/chat-messages/${messageId}/share`, {
      method: "POST",
    });
    return response.json();
  },

  async deleteMessage(messageId: string): Promise<ApiResponse<void>> {
    const response = await fetch(`/api/v2/chat-messages/${messageId}`, {
      method: "DELETE",
    });
    return response.json();
  },
};
```

### services/modelAdapters/base.ts

```typescript
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
```

### services/modelAdapters/openai.ts

```typescript
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

  async *sendMessage(
    messages: OpenAIMessage[],
    options: SendOptions
  ): AsyncIterable<string> {
    const payload = this.buildRequestPayload(messages, options);

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
    // 实现具体的函数调用逻辑
    // 这里应该根据function name调用相应的处理器

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
    // 调用搜索API
    const response = await fetch(
      `/v2/search-pali-wbw?view=pali&key=${term}&limit=20&offset=0`
    );
    const result = await response.json();
    return result.ok ? result.data.rows : { error: "搜索失败" };
  }

  private async getWeather(city: string) {
    // 模拟天气查询
    return {
      city,
      temperature: "25°C",
      condition: "晴朗",
      humidity: "60%",
    };
  }
}
```

### services/modelAdapters/index.ts

```typescript
import { ModelAdapter } from "../../types/chat";
import { OpenAIAdapter } from "./openai";

const adapters = new Map<string, ModelAdapter>();

// 注册适配器
adapters.set("gpt-4", new OpenAIAdapter());
adapters.set("gpt-3.5-turbo", new OpenAIAdapter());

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
```

## 7. 显示层组件

### components/ChatContainer.tsx

```typescript
import React, { useEffect } from "react";
import { useChatData } from "../hooks/useChatData";
import { SessionGroup } from "./SessionGroup";
import { ChatInput } from "./ChatInput";
import { StreamingMessage } from "./StreamingMessage";

interface ChatContainerProps {
  chatId: string;
}

export function ChatContainer({ chatId }: ChatContainerProps) {
  const { chatState, actions } = useChatData(chatId);

  useEffect(() => {
    actions.loadMessages();
  }, [chatId, actions.loadMessages]);

  return (
    <div className="chat-container">
      <div className="messages-area">
        {chatState.session_groups.map((session) => (
          <SessionGroup
            key={session.session_id}
            session={session}
            onVersionSwitch={actions.switchVersion}
            onRefresh={actions.refreshResponse}
            onEdit={actions.editMessage}
            onRetry={actions.retryMessage}
            onLike={actions.likeMessage}
            onDislike={actions.dislikeMessage}
            onCopy={actions.copyMessage}
            onShare={actions.shareMessage}
          />
        ))}

        {/* 流式消息显示 */}
        {chatState.streaming_message && (
          <StreamingMessage
            content={chatState.streaming_message}
            sessionId={chatState.streaming_session_id}
          />
        )}

        {/* 错误提示 */}
        {chatState.error && (
          <div className="error-message">{chatState.error}</div>
        )}
      </div>

      <ChatInput
        onSend={(content) => actions.editMessage("new", content)}
        disabled={chatState.is_loading}
        placeholder="输入你的问题..."
      />
    </div>
  );
}
```

### components/SessionGroup.tsx

```typescript
import React from "react";
import { Button } from "antd";
import { SessionGroupProps } from "../types/chat";
import { UserMessage } from "./UserMessage";
import { AssistantMessage } from "./AssistantMessage";
import { VersionSwitcher } from "./VersionSwitcher";

export function SessionGroup({
  session,
  onVersionSwitch,
  onRefresh,
  onEdit,
  onRetry,
  onLike,
  onDislike,
  onCopy,
  onShare,
}: SessionGroupProps) {
  const hasFailed = session.messages.some((m) => m.save_status === "failed");
  const isPending = session.messages.some((m) => m.save_status === "pending");
  const hasMultipleVersions = session.versions.length > 1;

  return (
    <div
      className={`session-group ${isPending ? "pending" : ""} ${
        hasFailed ? "failed" : ""
      }`}
    >
      {/* 用户消息 */}
      {session.user_message && (
        <UserMessage
          message={session.user_message}
          onEdit={(content) => onEdit && onEdit(session.session_id, content)}
          onCopy={() => onCopy && onCopy(session.user_message!.uid)}
        />
      )}

      {/* AI回答区域 */}
      <div className="ai-response">
        {/* 失败重试提示 */}
        {hasFailed && onRetry && (
          <div className="retry-section">
            <span className="error-message">回答生成失败</span>
            <Button
              size="small"
              type="primary"
              onClick={() => onRetry(session.messages[0].temp_id!)}
            >
              重试
            </Button>
          </div>
        )}

        {/* 版本切换器 */}
        {hasMultipleVersions && !isPending && !hasFailed && (
          <VersionSwitcher
            versions={session.versions}
            currentVersion={session.current_version}
            onSwitch={(versionIndex) =>
              onVersionSwitch(session.session_id, versionIndex)
            }
          />
        )}

        {/* AI消息内容 */}
        {!hasFailed && session.ai_messages.length > 0 && (
          <AssistantMessage
            messages={session.ai_messages}
            onRefresh={() => onRefresh && onRefresh(session.session_id)}
            onEdit={(content) =>
              onEdit && onEdit(session.session_id, content, "assistant")
            }
            isPending={isPending}
            onLike={onLike}
            onDislike={onDislike}
            onCopy={onCopy}
            onShare={onShare}
          />
        )}
      </div>
    </div>
  );
}
```

### components/UserMessage.tsx

```typescript
import React, { useState } from "react";
import { Button, Input } from "antd";
import { EditOutlined, CopyOutlined } from "@ant-design/icons";
import { UserMessageProps } from "../types/chat";

const { TextArea } = Input;

export function UserMessage({ message, onEdit, onCopy }: UserMessageProps) {
  const [isEditing, setIsEditing] = useState(false);
  const [editContent, setEditContent] = useState(message.content || "");

  const handleEdit = () => {
    if (onEdit && editContent.trim()) {
      onEdit(editContent.trim());
      setIsEditing(false);
    }
  };

  const handleCancel = () => {
    setEditContent(message.content || "");
    setIsEditing(false);
  };

  return (
    <div className="user-message">
      <div className="message-header">
        <span className="role-label">You</span>
        <div className="message-actions">
          {!isEditing && (
            <>
              <Button
                size="small"
                type="text"
                icon={<EditOutlined />}
                onClick={() => setIsEditing(true)}
              />
              <Button
                size="small"
                type="text"
                icon={<CopyOutlined />}
                onClick={onCopy}
              />
            </>
          )}
        </div>
      </div>

      <div className="message-content">
        {isEditing ? (
          <div className="edit-area">
            <TextArea
              value={editContent}
              onChange={(e) => setEditContent(e.target.value)}
              autoSize={{ minRows: 2, maxRows: 8 }}
              autoFocus
            />
            <div className="edit-actions">
              <Button size="small" onClick={handleCancel}>
                取消
              </Button>
              <Button size="small" type="primary" onClick={handleEdit}>
                保存
              </Button>
            </div>
          </div>
        ) : (
          <div className="message-text">
            {message.content}
            {message.save_status === "pending" && (
              <span className="status-indicator pending">发送中...</span>
            )}
            {message.save_status === "failed" && (
              <span className="status-indicator failed">发送失败</span>
            )}
          </div>
        )}
      </div>
    </div>
  );
}
```

### components/AssistantMessage.tsx

```typescript
import React from "react";
import { Button, Space } from "antd";
import {
  RefreshOutlined,
  LikeOutlined,
  DislikeOutlined,
  CopyOutlined,
  ShareOutlined,
} from "@ant-design/icons";
import { AssistantMessageProps } from "../types/chat";

export function AssistantMessage({
  messages,
  onRefresh,
  onEdit,
  isPending,
  onLike,
  onDislike,
  onCopy,
  onShare,
}: AssistantMessageProps) {
  const mainMessage = messages.find((m) => m.role === "assistant" && m.content);
  const toolMessages = messages.filter((m) => m.role === "tool");

  const handleCopy = () => {
    if (mainMessage?.content && onCopy) {
      onCopy(mainMessage.uid);
    }
  };

  const handleShare = async () => {
    if (mainMessage && onShare) {
      try {
        const shareUrl = await onShare(mainMessage.uid);
        // 可以显示分享链接或复制到剪贴板
        navigator.clipboard.writeText(shareUrl);
      } catch (err) {
        console.error("分享失败:", err);
      }
    }
  };

  return (
    <div className="assistant-message">
      <div className="message-header">
        <span className="role-label">Assistant</span>
        {mainMessage?.model_id && (
          <span className="model-info">{mainMessage.model_id}</span>
        )}

        {!isPending && (
          <div className="message-actions">
            <Space size="small">
              <Button
                size="small"
                type="text"
                icon={<RefreshOutlined />}
                onClick={onRefresh}
              />
              <Button
                size="small"
                type="text"
                icon={<LikeOutlined />}
                onClick={() => mainMessage && onLike && onLike(mainMessage.uid)}
              />
              <Button
                size="small"
                type="text"
                icon={<DislikeOutlined />}
                onClick={() =>
                  mainMessage && onDislike && onDislike(mainMessage.uid)
                }
              />
              <Button
                size="small"
                type="text"
                icon={<CopyOutlined />}
                onClick={handleCopy}
              />
              <Button
                size="small"
                type="text"
                icon={<ShareOutlined />}
                onClick={handleShare}
              />
            </Space>
          </div>
        )}
      </div>

      <div className="message-content">
        {/* Tool calls 显示 */}
        {toolMessages.length > 0 && (
          <div className="tool-calls">
            {toolMessages.map((toolMsg, index) => (
              <div key={toolMsg.uid} className="tool-result">
                <span className="tool-label">Tool {index + 1}</span>
                <div className="tool-content">{toolMsg.content}</div>
              </div>
            ))}
          </div>
        )}

        {/* 主要回答内容 */}
        {mainMessage?.content && (
          <div className="message-text">
            {mainMessage.content}
            {isPending && (
              <span className="status-indicator pending">生成中...</span>
            )}
          </div>
        )}

        {/* Token 使用信息 */}
        {mainMessage?.metadata?.token_usage && (
          <div className="token-info">
            Token: {mainMessage.metadata.token_usage.total_tokens}
          </div>
        )}
      </div>
    </div>
  );
}
```

### components/VersionSwitcher.tsx

```typescript
import React from "react";
import { Button, Space, Tooltip } from "antd";
import { LeftOutlined, RightOutlined } from "@ant-design/icons";
import { VersionSwitcherProps } from "../types/chat";

export function VersionSwitcher({
  versions,
  currentVersion,
  onSwitch,
}: VersionSwitcherProps) {
  if (versions.length <= 1) return null;

  const canGoPrev = currentVersion > 0;
  const canGoNext = currentVersion < versions.length - 1;

  const currentVersionInfo = versions[currentVersion];

  return (
    <div className="version-switcher">
      <Space align="center">
        <Button
          size="small"
          type="text"
          icon={<LeftOutlined />}
          disabled={!canGoPrev}
          onClick={() => canGoPrev && onSwitch(currentVersion - 1)}
        />

        <Tooltip
          title={
            <div>
              <div>
                版本 {currentVersion + 1} / {versions.length}
              </div>
              <div>模型: {currentVersionInfo.model_id || "未知"}</div>
              <div>
                创建: {new Date(currentVersionInfo.created_at).toLocaleString()}
              </div>
              {currentVersionInfo.token_usage && (
                <div>Token: {currentVersionInfo.token_usage}</div>
              )}
            </div>
          }
        >
          <span className="version-info">
            {currentVersion + 1} / {versions.length}
          </span>
        </Tooltip>

        <Button
          size="small"
          type="text"
          icon={<RightOutlined />}
          disabled={!canGoNext}
          onClick={() => canGoNext && onSwitch(currentVersion + 1)}
        />
      </Space>
    </div>
  );
}
```

### components/ChatInput.tsx

```typescript
import React, { useState, useCallback } from "react";
import { Button, Input, Space } from "antd";
import { SendOutlined, PaperClipOutlined } from "@ant-design/icons";
import { ChatInputProps } from "../types/chat";

const { TextArea } = Input;

export function ChatInput({ onSend, disabled, placeholder }: ChatInputProps) {
  const [inputValue, setInputValue] = useState("");

  const handleSend = useCallback(() => {
    if (!inputValue.trim() || disabled) return;

    onSend(inputValue.trim());
    setInputValue("");
  }, [inputValue, disabled, onSend]);

  const handleKeyPress = useCallback(
    (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        handleSend();
      }
    },
    [handleSend]
  );

  return (
    <div className="chat-input">
      <div className="input-area">
        <TextArea
          value={inputValue}
          onChange={(e) => setInputValue(e.target.value)}
          onKeyPress={handleKeyPress}
          placeholder={placeholder || "输入你的问题..."}
          autoSize={{ minRows: 1, maxRows: 6 }}
          disabled={disabled}
        />

        <div className="input-actions">
          <Space>
            <Button
              size="small"
              type="text"
              icon={<PaperClipOutlined />}
              disabled={disabled}
            />
            <Button
              type="primary"
              icon={<SendOutlined />}
              onClick={handleSend}
              disabled={!inputValue.trim() || disabled}
            />
          </Space>
        </div>
      </div>
    </div>
  );
}
```

### components/StreamingMessage.tsx

```typescript
import React from "react";

interface StreamingMessageProps {
  content: string;
  sessionId?: string;
}

export function StreamingMessage({ content }: StreamingMessageProps) {
  return (
    <div className="streaming-message">
      <div className="message-header">
        <span className="role-label">Assistant</span>
        <span className="streaming-indicator">正在生成中...</span>
      </div>

      <div className="message-content">
        <div className="message-text">
          {content}
          <span className="cursor">|</span>
        </div>
      </div>
    </div>
  );
}
```

## 8. 使用示例

### 基本使用

```typescript
import React from "react";
import { ChatContainer } from "./components/ChatContainer";

function App() {
  const chatId = "your-chat-id";

  return (
    <div className="app">
      <ChatContainer chatId={chatId} />
    </div>
  );
}

export default App;
```

### 样式示例 (CSS)

```css
.chat-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
}

.messages-area {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
}

.session-group {
  margin-bottom: 24px;
  border-radius: 8px;
  padding: 16px;
}

.session-group.pending {
  opacity: 0.7;
}

.session-group.failed {
  border: 1px solid #ff4d4f;
  background-color: #fff2f0;
}

.user-message,
.assistant-message {
  margin-bottom: 12px;
}

.message-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.role-label {
  font-weight: bold;
  color: #1890ff;
}

.message-content {
  padding: 12px;
  border-radius: 6px;
  background-color: #f5f5f5;
}

.version-switcher {
  margin: 8px 0;
  text-align: center;
}

.chat-input {
  border-top: 1px solid #d9d9d9;
  padding: 16px;
}

.streaming-message .cursor {
  animation: blink 1s infinite;
}

@keyframes blink {
  0%,
  50% {
    opacity: 1;
  }
  51%,
  100% {
    opacity: 0;
  }
}

.retry-section {
  padding: 12px;
  background-color: #fff2f0;
  border: 1px solid #ffccc7;
  border-radius: 6px;
  margin-bottom: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.error-message {
  color: #ff4d4f;
  font-size: 14px;
}

.status-indicator {
  margin-left: 8px;
  font-size: 12px;
}

.status-indicator.pending {
  color: #1890ff;
}

.status-indicator.failed {
  color: #ff4d4f;
}

.tool-calls {
  margin-bottom: 12px;
  padding: 8px;
  background-color: #f0f0f0;
  border-radius: 4px;
}

.tool-result {
  margin-bottom: 8px;
}

.tool-label {
  font-size: 12px;
  color: #666;
  font-weight: bold;
}

.tool-content {
  margin-top: 4px;
  font-family: monospace;
  font-size: 12px;
  padding: 4px;
  background-color: #fff;
  border-radius: 2px;
}

.token-info {
  margin-top: 8px;
  font-size: 12px;
  color: #666;
  text-align: right;
}

.edit-area {
  margin-top: 8px;
}

.edit-actions {
  margin-top: 8px;
  text-align: right;
}

.edit-actions .ant-btn {
  margin-left: 8px;
}

.input-area {
  position: relative;
}

.input-actions {
  position: absolute;
  right: 8px;
  bottom: 8px;
  display: flex;
  align-items: center;
}

.version-info {
  font-size: 12px;
  color: #666;
  padding: 0 8px;
  user-select: none;
}

.model-info {
  font-size: 12px;
  color: #666;
  background-color: #f0f0f0;
  padding: 2px 6px;
  border-radius: 4px;
}

.streaming-indicator {
  font-size: 12px;
  color: #1890ff;
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% {
    opacity: 0.6;
  }
  50% {
    opacity: 1;
  }
  100% {
    opacity: 0.6;
  }
}
```

## 9. 核心流程说明

### 9.1 消息发送流程

1. **用户输入** → `ChatInput.onSend` → `actions.editMessage('new', content)`
2. **创建临时消息** → 显示用户消息（pending 状态）
3. **调用 AI API** → 流式显示 AI 回答 → `StreamingMessage`
4. **Function Call 处理** → 循环处理工具调用
5. **保存到数据库** → 批量保存整个对话组
6. **状态同步** → 更新本地状态，移除临时标记

### 9.2 版本切换流程

1. **用户点击版本按钮** → `VersionSwitcher.onSwitch`
2. **计算目标版本** → 找到对应版本的消息组
3. **调用 API 更新** → 更新数据库激活状态
4. **重新加载数据** → 刷新本地状态
5. **重新渲染** → 显示新版本内容

### 9.3 消息编辑流程

1. **用户点击编辑** → `UserMessage` 进入编辑模式
2. **用户确认修改** → 创建新版本消息
3. **自动获取 AI 回答** → 发送新问题到 AI
4. **保存新对话链** → 批量保存到数据库

### 9.4 失败重试流程

1. **检测失败状态** → `save_status: 'failed'`
2. **显示重试按钮** → `SessionGroup` 渲染重试 UI
3. **用户点击重试** → `actions.retryMessage(tempId)`
4. **重新发送请求** → 使用原始用户消息重新调用 AI
5. **更新状态** → 成功则保存，失败则继续显示重试

## 10. 扩展功能

### 10.1 多模型支持

```typescript
// 添加新的模型适配器
class ClaudeAdapter extends BaseModelAdapter {
  name = "claude-3-sonnet";
  supportsFunctionCall = true;

  async *sendMessage(messages: OpenAIMessage[], options: SendOptions) {
    // Claude API 特定实现
  }

  parseStreamChunk(chunk: string): ParsedChunk | null {
    // Claude 响应格式解析
  }

  async handleFunctionCall(functionCall: ToolCall): Promise<any> {
    // Claude function call 处理
  }
}

// 注册新适配器
registerAdapter("claude-3-sonnet", new ClaudeAdapter());
```

### 10.2 消息搜索

```typescript
const searchMessages = useCallback(
  async (query: string) => {
    const response = await messageApi.searchMessages(chatId, query);
    return response.data;
  },
  [chatId]
);
```

### 10.3 导出功能

```typescript
const exportChat = useCallback(
  async (format: "json" | "markdown" | "pdf") => {
    const response = await chatApi.exportChat(chatId, format);
    // 处理导出文件
  },
  [chatId]
);
```

### 10.4 实时协作

```typescript
// WebSocket 连接处理多用户协作
const useRealtimeSync = (chatId: string) => {
  useEffect(() => {
    const ws = new WebSocket(`ws://localhost:8080/chat/${chatId}`);

    ws.onmessage = (event) => {
      const update = JSON.parse(event.data);
      // 处理其他用户的消息更新
    };

    return () => ws.close();
  }, [chatId]);
};
```

## 11. 性能优化

### 11.1 渲染优化

- 使用 React.memo 防止不必要的重新渲染
- 消息列表虚拟化（react-window）
- 懒加载历史消息版本

### 11.2 网络优化

- 请求去重和缓存
- 批量 API 调用
- 断点续传支持

### 11.3 内存管理

- 限制内存中保存的消息数量
- 及时清理未使用的临时状态
- 图片和文件的懒加载

## 12. 测试策略

### 12.1 单元测试

- Hook 功能测试：`useChatData`, `useActivePath`, `useSessionGroups`
- 组件渲染测试：所有 UI 组件的基本渲染
- API 服务测试：模拟 API 响应

### 12.2 集成测试

- 完整对话流程测试
- 版本切换功能测试
- 错误处理和重试测试

### 12.3 端到端测试

- 用户操作模拟：发送消息、编辑、版本切换
- 流式响应测试
- 多标签页同步测试

## 13. 部署配置

### 13.1 环境变量

```env
REACT_APP_API_BASE_URL=https://api.yourapp.com
REACT_APP_OPENAI_PROXY=https://api.yourapp.com/v2/openai-proxy
REACT_APP_OPENAI_KEY=your_openai_key
REACT_APP_WS_URL=wss://api.yourapp.com/ws
```

### 13.2 构建优化

```json
{
  "scripts": {
    "build": "react-scripts build",
    "build:analyze": "npm run build && npx webpack-bundle-analyzer build/static/js/*.js"
  }
}
```

## 14. 总结

这个重构方案提供了：

1. **清晰的架构分层**：数据管理、API 调用、UI 显示职责明确
2. **完整的类型定义**：所有类型统一在 `chat.ts` 中管理
3. **灵活的模型适配**：支持多厂商 AI 模型的无缝集成
4. **优秀的用户体验**：乐观更新、实时反馈、错误恢复
5. **强大的版本管理**：消息版本控制和切换功能
6. **可扩展的设计**：支持未来功能扩展和性能优化

设计遵循了 React 最佳实践，使用现代 Hooks 模式，确保代码的可维护性和可测试性。通过模块化设计，各个部分可以独立开发和测试，便于团队协作。

## 建议开发策略

1. 第一阶段：直接使用类型定义和组件结构
2. 第二阶段：实现核心 Hook 逻辑，先不考虑流式处理
3. 第三阶段：完善 API 调用和流式处理
4. 第四阶段：优化错误处理和用户体验
