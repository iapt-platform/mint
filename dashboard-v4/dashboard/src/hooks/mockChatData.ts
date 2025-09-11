import {
  MessageNode,
  ChatState,
  SessionInfo,
  VersionInfo,
} from "../types/chat";

// 模拟的消息数据 - 包含完整的对话场景
export const mockMessages: MessageNode[] = [
  // System 消息 (根节点)
  {
    id: 1,
    uid: "msg-system-001",
    chat_id: "chat-001",
    session_id: "system-session",
    role: "system",
    content:
      "你是一个巴利语专家和佛教术语解释助手。当用户询问佛教术语时，你可以调用 searchTerm 函数来查询详细信息。",
    is_active: true,
    created_at: "2025-01-15T10:00:00Z",
    updated_at: "2025-01-15T10:00:00Z",
  },

  // 第一轮对话 - 用户询问佛教术语
  {
    id: 2,
    uid: "msg-user-001",
    chat_id: "chat-001",
    parent_id: "msg-system-001",
    session_id: "session-001",
    role: "user",
    content: "什么是 dhamma？请详细解释一下。",
    is_active: true,
    created_at: "2025-01-15T10:01:00Z",
    updated_at: "2025-01-15T10:01:00Z",
  },

  // AI 回答 - 带 Function Call
  {
    id: 3,
    uid: "msg-assistant-001",
    chat_id: "chat-001",
    parent_id: "msg-user-001",
    session_id: "session-001",
    role: "assistant",
    model_id: "gpt-4",
    tool_calls: [
      {
        id: "call_dhamma_001",
        function: "searchTerm",
        arguments: { term: "dhamma" },
      },
    ],
    is_active: true,
    metadata: {
      generation_params: {
        temperature: 0.7,
        max_tokens: 2048,
      },
    },
    created_at: "2025-01-15T10:01:30Z",
    updated_at: "2025-01-15T10:01:30Z",
  },

  // Tool 调用结果
  {
    id: 4,
    uid: "msg-tool-001",
    chat_id: "chat-001",
    parent_id: "msg-assistant-001",
    session_id: "session-001",
    role: "tool",
    content:
      '{"term":"dhamma","definition":"法；教法；正义；真理","etymology":"来自梵语dharma","category":"佛教基本概念","explanation":"Dhamma是佛教中最核心的概念之一，指佛陀的教导、宇宙的法则以及存在的真理。"}',
    tool_call_id: "call_dhamma_001",
    is_active: true,
    created_at: "2025-01-15T10:01:35Z",
    updated_at: "2025-01-15T10:01:35Z",
  },

  // AI 最终回答
  {
    id: 5,
    uid: "msg-assistant-002",
    chat_id: "chat-001",
    parent_id: "msg-tool-001",
    session_id: "session-001",
    role: "assistant",
    content:
      'Dhamma（法）是佛教中最重要的概念之一。根据巴利语词典，Dhamma有以下几层含义：\n\n1. **佛陀的教导**：指佛陀所传授的教法和智慧\n2. **宇宙法则**：指支配宇宙运行的自然规律\n3. **正义与真理**：指正确的行为准则和道德标准\n\nDhamma来自梵语"dharma"，在不同语境下可以指代教法、法则、正义、真理等。对于佛教修行者来说，学习和实践Dhamma是走向解脱的根本途径。',
    model_id: "gpt-4",
    is_active: true,
    metadata: {
      generation_params: {
        temperature: 0.7,
        max_tokens: 2048,
      },
      token_usage: {
        prompt_tokens: 180,
        completion_tokens: 220,
        total_tokens: 400,
      },
      performance: {
        response_time_ms: 1500,
        first_token_time_ms: 600,
      },
    },
    created_at: "2025-01-15T10:01:45Z",
    updated_at: "2025-01-15T10:01:45Z",
  },

  // 第二轮对话 - 用户追问
  {
    id: 6,
    uid: "msg-user-002",
    chat_id: "chat-001",
    parent_id: "msg-assistant-002",
    session_id: "session-002",
    role: "user",
    content: "那 karma 和 dhamma 有什么关系呢？",
    is_active: true,
    created_at: "2025-01-15T10:02:00Z",
    updated_at: "2025-01-15T10:02:00Z",
  },

  // AI 第二次回答（第一个版本）
  {
    id: 7,
    uid: "msg-assistant-003",
    chat_id: "chat-001",
    parent_id: "msg-user-002",
    session_id: "session-002",
    role: "assistant",
    content:
      "Karma（业）和 Dhamma（法）是密切相关的佛教概念：\n\n**Karma（业力法则）**是 Dhamma 的重要组成部分。Karma 描述了行为与后果之间的因果关系，而这个因果法则本身就是宇宙运行的 Dhamma 之一。\n\n简单来说：\n- Dhamma 是更大的框架，包含了所有佛教教义和宇宙法则\n- Karma 是 Dhamma 中的具体法则之一，专门解释行为的因果关系\n\n通过理解 Karma，我们能更好地实践 Dhamma；通过实践 Dhamma，我们能更好地净化 Karma。",
    model_id: "gpt-4",
    is_active: true,
    metadata: {
      generation_params: {
        temperature: 0.7,
        max_tokens: 2048,
      },
      token_usage: {
        prompt_tokens: 420,
        completion_tokens: 150,
        total_tokens: 570,
      },
    },
    created_at: "2025-01-15T10:02:15Z",
    updated_at: "2025-01-15T10:02:15Z",
  },

  // 第二个版本的AI回答（用户点了刷新）
  {
    id: 8,
    uid: "msg-assistant-004",
    chat_id: "chat-001",
    parent_id: "msg-user-002",
    session_id: "session-002",
    role: "assistant",
    content:
      "Karma 和 Dhamma 的关系可以这样理解：\n\n1. **包含关系**：Karma 是 Dhamma 的一部分。Dhamma 是整个佛教教法体系，而 Karma 是其中的核心法则\n\n2. **实践关系**：\n   - 学习 Dhamma → 了解 Karma 的运作原理\n   - 正确理解 Karma → 能够更好地实践 Dhamma\n\n3. **目标一致**：两者都指向同一目标 - 通过正确的认知和行为获得解脱\n\n可以说，Karma 是 Dhamma 在日常生活中的具体体现，而 Dhamma 为理解 Karma 提供了更深层的智慧框架。",
    model_id: "claude-3-sonnet",
    is_active: false, // 这是第二个版本，当前不激活
    metadata: {
      generation_params: {
        temperature: 0.8,
        max_tokens: 2048,
      },
      token_usage: {
        prompt_tokens: 420,
        completion_tokens: 180,
        total_tokens: 600,
      },
    },
    created_at: "2025-01-15T10:03:00Z",
    updated_at: "2025-01-15T10:03:00Z",
  },

  // 第三轮对话 - 用户编辑了第一个问题
  {
    id: 9,
    uid: "msg-user-003",
    chat_id: "chat-001",
    parent_id: "msg-system-001", // 回到系统消息后
    session_id: "session-003",
    role: "user",
    content: "请解释一下佛教中的 nirvana 概念，它与 dhamma 有什么关系？", // 用户编辑了第一个问题
    is_active: false, // 用户当前查看的是第一条线路，这条是备选线路
    created_at: "2025-01-15T10:04:00Z",
    updated_at: "2025-01-15T10:04:00Z",
  },

  {
    id: 10,
    uid: "msg-assistant-005",
    chat_id: "chat-001",
    parent_id: "msg-user-003",
    session_id: "session-003",
    role: "assistant",
    content:
      'Nirvana（涅槃）是佛教修行的最终目标，与 Dhamma 有着密不可分的关系：\n\n**Nirvana 的含义**：\n- 字面意思是"熄灭"，指贪嗔痴等烦恼的彻底熄灭\n- 是痛苦和轮回的终结，达到究竟的解脱状态\n\n**与 Dhamma 的关系**：\n1. **Dhamma 是道路**：佛陀的教法为我们指明了通向 Nirvana 的道路\n2. **Nirvana 是目标**：修行 Dhamma 的最终目的就是证得 Nirvana\n3. **相互验证**：真正的 Dhamma 必然能导向 Nirvana；真正的 Nirvana 必然符合 Dhamma\n\n可以说，没有 Dhamma 就无法理解和达到 Nirvana，而 Nirvana 是 Dhamma 价值的最终体现。',
    model_id: "gpt-4",
    is_active: false,
    metadata: {
      generation_params: {
        temperature: 0.7,
      },
      token_usage: {
        prompt_tokens: 150,
        completion_tokens: 200,
        total_tokens: 350,
      },
    },
    created_at: "2025-01-15T10:04:20Z",
    updated_at: "2025-01-15T10:04:20Z",
  },
];

// 模拟版本信息
export const mockVersions: VersionInfo[] = [
  {
    version_index: 0,
    model_id: "gpt-4",
    model_name: "GPT-4",
    created_at: "2025-01-15T10:02:15Z",
    message_count: 1,
    token_usage: 570,
  },
  {
    version_index: 1,
    model_id: "claude-3-sonnet",
    model_name: "Claude 3 Sonnet",
    created_at: "2025-01-15T10:03:00Z",
    message_count: 1,
    token_usage: 600,
  },
];

// 模拟会话组信息
export const mockSessionGroups: SessionInfo[] = [
  {
    session_id: "session-001",
    messages: [
      mockMessages[1], // user message
      mockMessages[2], // assistant with tool calls
      mockMessages[3], // tool result
      mockMessages[4], // final assistant response
    ],
    versions: [
      {
        version_index: 0,
        model_id: "gpt-4",
        created_at: "2025-01-15T10:01:45Z",
        message_count: 3,
        token_usage: 400,
      },
    ],
    current_version: 0,
    user_message: mockMessages[1],
    ai_messages: [mockMessages[2], mockMessages[3], mockMessages[4]],
  },
  {
    session_id: "session-002",
    messages: [
      mockMessages[5], // user message
      mockMessages[6], // assistant response (active version)
    ],
    versions: mockVersions,
    current_version: 0,
    user_message: mockMessages[5],
    ai_messages: [mockMessages[6]],
  },
];

// 模拟完整的聊天状态
export const mockChatState: ChatState = {
  chat_id: "chat-001",
  title: "佛教术语学习对话",
  raw_messages: mockMessages,
  active_path: [
    mockMessages[0], // system
    mockMessages[1], // user 1
    mockMessages[2], // assistant 1 (with tool calls)
    mockMessages[3], // tool result
    mockMessages[4], // assistant final
    mockMessages[5], // user 2
    mockMessages[6], // assistant 2 (active version)
  ],
  session_groups: mockSessionGroups,
  pending_messages: [],
  is_loading: false,
  current_model: "gpt-4",
  streaming_message: undefined,
  streaming_session_id: undefined,
};

// 模拟带有待发送消息的状态
export const mockChatStateWithPending: ChatState = {
  ...mockChatState,
  pending_messages: [
    {
      temp_id: "temp_123456",
      session_id: "session_temp_001",
      messages: [
        {
          id: 0,
          uid: "temp_user_123456",
          temp_id: "temp_123456",
          chat_id: "chat-001",
          parent_id: "msg-assistant-002",
          session_id: "session_temp_001",
          role: "user",
          content: "佛教中的八正道具体是什么？",
          is_active: true,
          save_status: "pending",
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
        },
      ],
      retry_count: 0,
      created_at: new Date().toISOString(),
    },
  ],
  is_loading: true,
};

// 模拟失败状态的数据
export const mockChatStateWithError: ChatState = {
  ...mockChatState,
  pending_messages: [
    {
      temp_id: "temp_failed_001",
      session_id: "session_failed_001",
      messages: [
        {
          id: 0,
          uid: "temp_user_failed",
          temp_id: "temp_failed_001",
          chat_id: "chat-001",
          parent_id: "msg-assistant-002",
          session_id: "session_failed_001",
          role: "user",
          content: "什么是禅宗？",
          is_active: true,
          save_status: "failed",
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
        },
      ],
      retry_count: 2,
      error: "API调用失败，请重试",
      created_at: new Date().toISOString(),
    },
  ],
  error: "API调用失败，请重试",
};

// 工具函数：根据 session_id 过滤消息
export function getMessagesBySession(sessionId: string): MessageNode[] {
  return mockMessages.filter(
    (msg) => msg.session_id === sessionId && msg.is_active
  );
}

// 工具函数：获取激活路径
export function getActivePath(): MessageNode[] {
  return mockMessages.filter((msg) => msg.is_active);
}

// 工具函数：模拟API响应格式
export function createMockApiResponse<T>(data: T) {
  return {
    ok: true,
    message: "success",
    data,
  };
}

// 导出所有mock数据
const mock = {
  messages: mockMessages,
  sessions: mockSessionGroups,
  chatState: mockChatState,
  chatStateWithPending: mockChatStateWithPending,
  chatStateWithError: mockChatStateWithError,
  versions: mockVersions,
  getMessagesBySession,
  getActivePath,
  createMockApiResponse,
};
export default mock;
