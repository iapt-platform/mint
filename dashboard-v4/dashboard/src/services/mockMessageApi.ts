// services/mockMessageApi.ts
import {
  CreateMessageRequest,
  MessageListResponse,
  ApiResponse,
  MessageNode,
} from "../types/chat";
// Mock 消息数据库
let mockMessages: MessageNode[] = [
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

  // AI 第二次回答（第一个版本，当前激活）
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

  // 第二个版本的AI回答（用户点了刷新，但当前未激活）
  {
    id: 8,
    uid: "msg-assistant-004",
    chat_id: "chat-001",
    parent_id: "msg-user-002",
    session_id: "session-v2-002",
    role: "assistant",
    content:
      "Karma 和 Dhamma 的关系可以从三个层面来理解：\n\n1. **本质关系**：Karma 法则本身就是 Dhamma 的体现，它揭示了宇宙中行为与结果的必然联系\n\n2. **实践关系**：\n   - 学习 Dhamma → 了解 Karma 的运作机制\n   - 正确认识 Karma → 能够依 Dhamma 而行\n\n3. **解脱关系**：通过 Dhamma 的指导，我们可以创造善业、净化恶业，最终超越业力束缚\n\n总的来说，Dhamma 为我们提供了理解和转化 Karma 的智慧工具。",
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

  // 第三轮对话 - 用户继续提问
  {
    id: 9,
    uid: "msg-user-003",
    chat_id: "chat-001",
    parent_id: "msg-assistant-003", // 基于第一个版本的回答
    session_id: "session-003",
    role: "user",
    content: "佛教的八正道具体包括哪些内容？",
    is_active: true,
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
      "八正道（Noble Eightfold Path）是佛教修行的核心方法，包含八个相互关联的修行要素：\n\n**慧学（智慧）**：\n1. **正见**（Right View）- 正确的世界观和人生观\n2. **正思维**（Right Intention）- 正确的思考和发心\n\n**戒学（道德）**：\n3. **正语**（Right Speech）- 正确的言语\n4. **正业**（Right Action）- 正确的行为\n5. **正命**（Right Livelihood）- 正确的生活方式\n\n**定学（禅定）**：\n6. **正精进**（Right Effort）- 正确的努力\n7. **正念**（Right Mindfulness）- 正确的觉知\n8. **正定**（Right Concentration）- 正确的禅定\n\n这八个方面需要同时修习，相互支持，共同导向解脱。",
    model_id: "gpt-4",
    is_active: true,
    metadata: {
      generation_params: {
        temperature: 0.7,
        max_tokens: 2048,
      },
      token_usage: {
        prompt_tokens: 600,
        completion_tokens: 250,
        total_tokens: 850,
      },
    },
    created_at: "2025-01-15T10:04:20Z",
    updated_at: "2025-01-15T10:04:20Z",
  },

  // 分支路径 - 用户编辑了第一个问题
  {
    id: 11,
    uid: "msg-user-004",
    chat_id: "chat-001",
    parent_id: "msg-system-001", // 回到系统消息后
    session_id: "session-v2-004",
    role: "user",
    content: "请解释一下佛教中的 nirvana（涅槃）概念。", // 用户编辑了第一个问题
    is_active: false, // 用户当前查看的是主线路，这条是分支
    created_at: "2025-01-15T10:05:00Z",
    updated_at: "2025-01-15T10:05:00Z",
  },

  {
    id: 12,
    uid: "msg-assistant-006",
    chat_id: "chat-001",
    parent_id: "msg-user-004",
    session_id: "session-004",
    role: "assistant",
    content:
      'Nirvana（涅槃）是佛教修行的最终目标，具有深刻的内涵：\n\n**字面含义**：\n- 巴利语 "Nibbāna"，梵语 "Nirvāṇa"\n- 原意为"熄灭"、"吹灭"，指烦恼之火的熄灭\n\n**实质内容**：\n1. **烦恼的熄灭**：贪、嗔、痴等根本烦恼的彻底断除\n2. **轮回的终止**：不再受业力牵引而轮回生死\n3. **痛苦的止息**：一切苦的根源得到根除\n4. **究竟的解脱**：获得绝对的自由和安宁\n\n**两种涅槃**：\n- **有余涅槃**：烦恼已断，但身心仍存\n- **无余涅槃**：身心俱灭，彻底解脱\n\n涅槃不是虚无，而是超越一切对立和限制的究竟实相。',
    model_id: "gpt-4",
    is_active: false,
    metadata: {
      generation_params: {
        temperature: 0.7,
      },
      token_usage: {
        prompt_tokens: 150,
        completion_tokens: 280,
        total_tokens: 430,
      },
    },
    created_at: "2025-01-15T10:05:20Z",
    updated_at: "2025-01-15T10:05:20Z",
  },
];
// Mock 数据生成工具
const generateMockId = () => {
  const ids = mockMessages.map((m) => m.id);
  return Math.max(...ids) + 1;
};
const generateUuid = () =>
  `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
const mockDelay = (ms: number = 200) =>
  new Promise((resolve) => setTimeout(resolve, ms));

export const mockMessageApi = {
  // 获取聊天消息列表
  async getMessages(chatId: string): Promise<ApiResponse<MessageListResponse>> {
    await mockDelay();

    console.log(`[Mock API] Getting messages for chat: ${chatId}`);

    return {
      ok: true,
      message: "Messages retrieved successfully",
      data: {
        rows: mockMessages,
        total: mockMessages.length,
      },
    };
  },

  //
  /**
   * 创建新消息
   * 检查request.messages里面每一项的parent_id。如果parent_id是mockMessages里面有的。那么保留这个parent_id.
   * 如果这个parent_id是mockMessages里面没有的，但是是request.messages里面有的。修改为输入messages的修改过的uuid
   * @param chatId
   * @param request
   * @returns
   */
  async createMessages(
    chatId: string,
    request: CreateMessageRequest
  ): Promise<ApiResponse<MessageNode[]>> {
    await mockDelay();

    console.log(`[Mock API] Creating messages for chat: ${chatId}`, request);

    const createdMessages: MessageNode[] = [];
    const now = new Date().toISOString();
    const uidMap = new Map<string, string>();

    for (const msgRequest of request.messages) {
      const uuid = generateUuid();
      msgRequest.uid && uidMap.set(msgRequest.uid, uuid);
      let parent_id = msgRequest.parent_id;
      if (parent_id && uidMap.has(parent_id)) {
        parent_id = uidMap.get(parent_id);
      }
      const newMessage: MessageNode = {
        id: generateMockId(),
        uid: uuid,
        chat_id: chatId,
        parent_id: parent_id,
        session_id: generateUuid(), // 在实际应用中，这可能由前端或后端逻辑确定
        role: msgRequest.role,
        content: msgRequest.content,
        model_id: msgRequest.model_id,
        tool_calls: msgRequest.tool_calls,
        tool_call_id: msgRequest.tool_call_id,
        metadata: msgRequest.metadata,
        is_active: true,
        created_at: now,
        updated_at: now,
        save_status: "saved",
      };

      createdMessages.push(newMessage);
      mockMessages.push(newMessage);
    }

    return {
      ok: true,
      message: "Messages created successfully",
      data: createdMessages,
    };
  },

  // 切换消息版本
  async switchVersion(
    chatId: string,
    messageUids: string[]
  ): Promise<ApiResponse<void>> {
    await mockDelay();

    console.log(
      `[Mock API] Switching version for chat: ${chatId}`,
      messageUids
    );

    // 首先将所有消息设为非活跃
    mockMessages.forEach((msg) => {
      if (msg.chat_id === chatId) {
        msg.is_active = false;
      }
    });

    // 然后激活指定的消息链
    messageUids.forEach((uid) => {
      const message = mockMessages.find((msg) => msg.uid === uid);
      if (message) {
        message.is_active = true;
      }
    });

    return {
      ok: true,
      message: "Version switched successfully",
      data: undefined,
    };
  },

  // 点赞消息
  async likeMessage(messageId: string): Promise<ApiResponse<void>> {
    await mockDelay();

    console.log(`[Mock API] Liking message: ${messageId}`);

    const message = mockMessages.find((msg) => msg.uid === messageId);
    if (!message) {
      return {
        ok: false,
        message: "Message not found",
        data: undefined,
      };
    }

    // 模拟点赞逻辑 - 可以在 metadata 中记录
    if (!message.metadata) {
      message.metadata = {};
    }
    if (!message.metadata.custom_data) {
      message.metadata.custom_data = {};
    }
    message.metadata.custom_data.liked = true;
    message.metadata.custom_data.disliked = false;

    return {
      ok: true,
      message: "Message liked successfully",
      data: undefined,
    };
  },

  // 踩消息
  async dislikeMessage(messageId: string): Promise<ApiResponse<void>> {
    await mockDelay();

    console.log(`[Mock API] Disliking message: ${messageId}`);

    const message = mockMessages.find((msg) => msg.uid === messageId);
    if (!message) {
      return {
        ok: false,
        message: "Message not found",
        data: undefined,
      };
    }

    // 模拟踩的逻辑
    if (!message.metadata) {
      message.metadata = {};
    }
    if (!message.metadata.custom_data) {
      message.metadata.custom_data = {};
    }
    message.metadata.custom_data.liked = false;
    message.metadata.custom_data.disliked = true;

    return {
      ok: true,
      message: "Message disliked successfully",
      data: undefined,
    };
  },

  // 分享消息
  async shareMessage(
    messageId: string
  ): Promise<ApiResponse<{ shareUrl: string }>> {
    await mockDelay();

    console.log(`[Mock API] Sharing message: ${messageId}`);

    const message = mockMessages.find((msg) => msg.uid === messageId);
    if (!message) {
      return {
        ok: false,
        message: "Message not found",
        data: { shareUrl: "" },
      };
    }

    // 生成模拟的分享链接
    const shareUrl = `https://chat.example.com/share/${messageId}`;

    return {
      ok: true,
      message: "Message shared successfully",
      data: { shareUrl },
    };
  },

  // 删除消息
  async deleteMessage(messageId: string): Promise<ApiResponse<void>> {
    await mockDelay();

    console.log(`[Mock API] Deleting message: ${messageId}`);

    const messageIndex = mockMessages.findIndex((msg) => msg.uid === messageId);
    if (messageIndex === -1) {
      return {
        ok: false,
        message: "Message not found",
        data: undefined,
      };
    }

    // 软删除 - 设置 deleted_at
    mockMessages[messageIndex].deleted_at = new Date().toISOString();

    return {
      ok: true,
      message: "Message deleted successfully",
      data: undefined,
    };
  },
};

// 工具函数：获取 mock 数据用于调试
export const mockMessageUtils = {
  // 获取所有 mock 消息
  getAllMessages: () => [...mockMessages],

  // 重置 mock 数据
  resetMessages: () => {
    mockMessages = mockMessages.slice(0, 8); // 保留初始数据
  },

  // 添加新的 mock 消息
  addMockMessage: (message: Partial<MessageNode>) => {
    const fullMessage: MessageNode = {
      id: generateMockId(),
      uid: generateUuid(),
      chat_id: "chat-1",
      session_id: generateUuid(),
      role: "user",
      content: "",
      is_active: true,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString(),
      save_status: "saved",
      ...message,
    };
    mockMessages.push(fullMessage);
    return fullMessage;
  },

  // 设置特定消息的活跃状态
  setMessageActive: (messageId: string, isActive: boolean) => {
    const message = mockMessages.find((msg) => msg.uid === messageId);
    if (message) {
      message.is_active = isActive;
    }
  },

  // 获取特定会话的消息
  getSessionMessages: (chatId: string, sessionId: string) => {
    return mockMessages.filter(
      (msg) => msg.chat_id === chatId && msg.session_id === sessionId
    );
  },

  // 模拟创建完整的对话会话（用户问题 + AI回答）
  createMockSession: (
    chatId: string,
    userContent: string,
    assistantContent: string,
    parentId?: string
  ) => {
    const sessionId = generateUuid();
    const now = new Date().toISOString();

    const userMessage = mockMessageUtils.addMockMessage({
      chat_id: chatId,
      parent_id: parentId,
      session_id: sessionId,
      role: "user",
      content: userContent,
      created_at: now,
      updated_at: now,
    });

    const assistantMessage = mockMessageUtils.addMockMessage({
      chat_id: chatId,
      parent_id: userMessage.uid,
      session_id: sessionId,
      role: "assistant",
      content: assistantContent,
      model_id: "gpt-4",
      metadata: {
        token_usage: {
          prompt_tokens: Math.floor(Math.random() * 200) + 50,
          completion_tokens: Math.floor(Math.random() * 150) + 30,
          total_tokens: 0,
        },
        performance: {
          response_time_ms: Math.floor(Math.random() * 2000) + 500,
          first_token_time_ms: Math.floor(Math.random() * 300) + 100,
        },
      },
      created_at: new Date(Date.now() + 1000).toISOString(),
      updated_at: new Date(Date.now() + 1000).toISOString(),
    });

    // 计算总 token
    if (assistantMessage.metadata?.token_usage) {
      assistantMessage.metadata.token_usage.total_tokens =
        assistantMessage.metadata.token_usage.prompt_tokens! +
        assistantMessage.metadata.token_usage.completion_tokens!;
    }

    return { userMessage, assistantMessage };
  },
};

/**
 * 调用范例
 * // 在 useChatData 或其他地方
    import { mockMessageApi } from './services/mockMessageApi';

    // 条件性使用 mock API
    const messageApi = process.env.NODE_ENV === 'development' 
    ? mockMessageApi 
    : realMessageApi;
 */
