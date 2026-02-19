import type { CreateChatRequest, ChatResponse, ApiResponse } from "../types/chat"

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
