import type { CreateChatRequest, ChatResponse, ApiResponse } from "../types/chat"

// Mock 存储，模拟数据库
let mockChats: ChatResponse[] = [
  {
    id: "550e8400-e29b-41d4-a716-446655440000",
    title: "天气查询助手",
    user_id: "ba5463f3-72d1-4410-858e-eadd10884713",
    created_at: "2025-01-15T10:30:00.000000Z",
    updated_at: "2025-01-15T10:30:00.000000Z",
  },
  {
    id: "660f8400-e29b-41d4-a716-446655440001",
    title: "编程问题讨论",
    user_id: "ba5463f3-72d1-4410-858e-eadd10884713",
    created_at: "2025-01-14T15:20:00.000000Z",
    updated_at: "2025-01-14T15:20:00.000000Z",
  },
  {
    id: "770e8400-e29b-41d4-a716-446655440002",
    title: "数学问题求解",
    user_id: "ba5463f3-72d1-4410-858e-eadd10884713",
    created_at: "2025-01-13T09:15:00.000000Z",
    updated_at: "2025-01-13T09:15:00.000000Z",
  },
];

// 工具函数：生成UUID
function generateUUID(): string {
  return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, function (c) {
    const r = (Math.random() * 16) | 0;
    const v = c === "x" ? r : (r & 0x3) | 0x8;
    return v.toString(16);
  });
}

// 工具函数：模拟网络延迟
function mockDelay(ms: number = 500): Promise<void> {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

// 工具函数：创建成功响应
function createSuccessResponse<T>(data: T): ApiResponse<T> {
  return {
    ok: true,
    message: "ok",
    data,
  };
}

// 工具函数：创建错误响应
function createErrorResponse(
  message: string,
  errors?: Record<string, string[]>
): ApiResponse<any> {
  return {
    ok: false,
    message,
    data: "",
    errors,
  };
}

// Mock Chat API 实现
export const mockChatApi = {
  async createChat(
    request: CreateChatRequest
  ): Promise<ApiResponse<ChatResponse>> {
    console.log("[Mock API] Creating chat:", request);

    await mockDelay(300);

    // 验证请求
    if (!request.title || request.title.trim() === "") {
      return createErrorResponse("标题不能为空", {
        title: ["标题是必填字段"],
      });
    }

    if (request.title.length > 255) {
      return createErrorResponse("标题过长", {
        title: ["标题长度不能超过255个字符"],
      });
    }

    // 创建新聊天
    const newChat: ChatResponse = {
      id: generateUUID(),
      title: request.title.trim(),
      user_id: request.user_id,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString(),
    };

    mockChats.unshift(newChat); // 新聊天放在最前面
    console.log("[Mock API] Chat created:", newChat);

    return createSuccessResponse(newChat);
  },

  async getChat(chatId: string): Promise<ApiResponse<ChatResponse>> {
    console.log("[Mock API] Getting chat:", chatId);

    await mockDelay(200);

    const chat = mockChats.find((c) => c.id === chatId);

    if (!chat) {
      return createErrorResponse("聊天不存在");
    }

    return createSuccessResponse(chat);
  },

  async updateChat(
    chatId: string,
    updates: Partial<CreateChatRequest>
  ): Promise<ApiResponse<ChatResponse>> {
    console.log("[Mock API] Updating chat:", chatId, updates);

    await mockDelay(300);

    const chatIndex = mockChats.findIndex((c) => c.id === chatId);

    if (chatIndex === -1) {
      return createErrorResponse("聊天不存在");
    }

    // 验证更新数据
    if (updates.title !== undefined) {
      if (!updates.title || updates.title.trim() === "") {
        return createErrorResponse("标题不能为空", {
          title: ["标题是必填字段"],
        });
      }

      if (updates.title.length > 255) {
        return createErrorResponse("标题过长", {
          title: ["标题长度不能超过255个字符"],
        });
      }
    }

    // 更新聊天
    const updatedChat: ChatResponse = {
      ...mockChats[chatIndex],
      ...updates,
      title: updates.title?.trim() || mockChats[chatIndex].title,
      updated_at: new Date().toISOString(),
    };

    mockChats[chatIndex] = updatedChat;
    console.log("[Mock API] Chat updated:", updatedChat);

    return createSuccessResponse(updatedChat);
  },

  async deleteChat(chatId: string): Promise<ApiResponse<void>> {
    console.log("[Mock API] Deleting chat:", chatId);

    await mockDelay(200);

    const chatIndex = mockChats.findIndex((c) => c.id === chatId);

    if (chatIndex === -1) {
      return createErrorResponse("聊天不存在");
    }

    // 软删除：实际项目中会设置 deleted_at 字段
    // 这里为了简化，直接从数组中移除
    mockChats.splice(chatIndex, 1);
    console.log("[Mock API] Chat deleted");

    return createSuccessResponse(undefined as any);
  },

  // 额外的辅助方法，便于测试和开发
  async getChats(params?: {
    limit?: number;
    user_id?: string;
    offset?: number;
  }): Promise<
    ApiResponse<{
      rows: ChatResponse[];
      total: number;
    }>
  > {
    console.log("[Mock API] Getting chats:", params);

    await mockDelay(300);

    let filteredChats = [...mockChats];

    // 按用户过滤
    if (params?.user_id) {
      filteredChats = filteredChats.filter(
        (chat) => chat.user_id === params.user_id
      );
    }

    // 分页
    const total = filteredChats.length;
    const offset = params?.offset || 0;
    const limit = params?.limit || 20;
    const paginatedChats = filteredChats.slice(offset, offset + limit);

    return createSuccessResponse({
      rows: paginatedChats,
      total,
    });
  },

  // 重置 Mock 数据（用于测试）
  resetMockData(): void {
    console.log("[Mock API] Resetting mock data");
    mockChats = [
      {
        id: "550e8400-e29b-41d4-a716-446655440000",
        title: "天气查询助手",
        user_id: "ba5463f3-72d1-4410-858e-eadd10884713",
        created_at: "2025-01-15T10:30:00.000000Z",
        updated_at: "2025-01-15T10:30:00.000000Z",
      },
      {
        id: "660f8400-e29b-41d4-a716-446655440001",
        title: "编程问题讨论",
        user_id: "ba5463f3-72d1-4410-858e-eadd10884713",
        created_at: "2025-01-14T15:20:00.000000Z",
        updated_at: "2025-01-14T15:20:00.000000Z",
      },
      {
        id: "770e8400-e29b-41d4-a716-446655440002",
        title: "数学问题求解",
        user_id: "ba5463f3-72d1-4410-858e-eadd10884713",
        created_at: "2025-01-13T09:15:00.000000Z",
        updated_at: "2025-01-13T09:15:00.000000Z",
      },
    ];
  },

  // 获取当前 Mock 数据（用于调试）
  getMockData(): ChatResponse[] {
    return [...mockChats];
  },

  // 模拟网络错误
  async createChatWithNetworkError(): Promise<ApiResponse<ChatResponse>> {
    await mockDelay(1000);
    throw new Error("网络连接失败");
  },

  // 模拟服务器错误
  async createChatWithServerError(): Promise<ApiResponse<ChatResponse>> {
    await mockDelay(500);
    return {
      ok: false,
      message: "服务器内部错误",
    } as ApiResponse<any>;
  },

  // 设置延迟时间（用于测试不同网络条件）
  setMockDelay(ms: number): void {
    // 这里可以实现动态调整延迟的逻辑
    console.log(`[Mock API] Mock delay set to ${ms}ms`);
  },
};

// 导出类型，便于在其他地方使用
export type MockChatApi = typeof mockChatApi;
