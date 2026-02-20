// dashboard-v4/dashboard/src/services/messageApi.ts
import type {
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
