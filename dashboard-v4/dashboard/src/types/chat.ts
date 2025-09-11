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
/*
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
*/
export interface ModelAdapter {
  name: string;
  supportsFunctionCall: boolean;
  sendMessage(
    messages: OpenAIMessage[],
    options: SendOptions
  ): Promise<AsyncIterable<string>>; // 修改这里
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
