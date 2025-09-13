// dashboard-v4/dashboard/src/hooks/useChatData.ts
import { useState, useCallback, useMemo, useEffect } from "react";
import {
  MessageNode,
  ChatState,
  ChatActions,
  PendingMessage,
  OpenAIMessage,
  TOpenAIRole,
} from "../types/chat";

import { useActivePath } from "./useActivePath";
import { useSessionGroups } from "./useSessionGroups";
//import { messageApi } from "../services/messageApi";
import { mockMessageApi as messageApi } from "../services/mockMessageApi";
import { getModelAdapter } from "../services/modelAdapters";
import { IAiModel } from "../components/api/ai";

export function useChatData(chatId: string): {
  chatState: ChatState;
  actions: ChatActions;
} {
  // Mock模式：直接使用mock数据
  const [rawMessages, setRawMessages] = useState<MessageNode[]>([]);
  const [pendingMessages, setPendingMessages] = useState<PendingMessage[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [isInitialized, setIsInitialized] = useState(false); // 新增：标记是否已初始化
  const [streamingMessage, setStreamingMessage] = useState<string>();
  const [streamingSessionId, setStreamingSessionId] = useState<string>();
  const [error, setError] = useState<string>();
  const [activePoint, setActivePoint] = useState<string>();
  const [currModel, setCurrModel] = useState<IAiModel>();

  // 合并已保存和待保存的消息用于显示
  const allMessages = useMemo(() => {
    const pending = pendingMessages.flatMap((p) => p.messages);
    return [...rawMessages, ...pending];
  }, [rawMessages, pendingMessages]);

  const activePath = useActivePath(allMessages, activePoint);
  const sessionGroups = useSessionGroups(activePath, allMessages);

  // 加载消息列表
  const loadMessages = useCallback(async () => {
    // 如果 chatId 为空或无效，不执行加载
    if (!chatId || chatId.trim() === "") {
      return;
    }

    try {
      setIsLoading(true);
      setError(undefined); // 清除之前的错误

      const response = await messageApi.getMessages(chatId);
      setRawMessages(response.data.rows);
      setIsInitialized(true);
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : "加载消息失败";
      setError(errorMessage);
      console.error("加载消息失败:", err);
    } finally {
      setIsLoading(false);
    }
  }, [chatId]);

  // 当 chatId 变化时自动加载消息
  useEffect(() => {
    // 重置状态
    setIsInitialized(false);
    setRawMessages([]);
    setPendingMessages([]);
    setError(undefined);
    setActivePoint(undefined);

    // 加载新的消息
    loadMessages();
  }, [chatId, loadMessages]);

  // 手动刷新方法（供外部调用）
  const refreshMessages = useCallback(async () => {
    setIsInitialized(false);
    await loadMessages();
  }, [loadMessages]);

  // 构建对话历史（用于AI API调用）
  const buildConversationHistory = useCallback(
    (baseMessages: MessageNode[], newUserMessage?: MessageNode) => {
      const history: OpenAIMessage[] = activePath
        .filter((m) => m.role !== "tool") // 排除tool消息
        .map((m) => ({
          role: m.role,
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

  // 保存消息组到数据库
  const saveMessageGroup = useCallback(
    async (tempId: string, messages: MessageNode[]) => {
      try {
        const savedMessages = await messageApi.createMessages(chatId, {
          messages: messages.map((m) => ({
            uid: m.uid,
            parent_id: m.parent_id,
            role: m.role,
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

  // 发送消息给AI并处理响应
  const sendMessageToAI = useCallback(
    async (userMessage: MessageNode, pendingGroup: PendingMessage) => {
      try {
        if (!currModel) {
          console.error("no model selected");
          return;
        }
        console.debug("ai chat send message current model", currModel);
        setIsLoading(true);
        setStreamingSessionId(pendingGroup.session_id);

        const conversationHistory = buildConversationHistory(
          rawMessages,
          userMessage
        );
        const adapter = getModelAdapter(currModel.name);

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
            model_id: currModel.uid,
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
    [currModel, buildConversationHistory, rawMessages, saveMessageGroup, chatId]
  );

  // 编辑消息 - 创建新版本
  const editMessage = useCallback(
    async (sessionId: string, content: string, role: TOpenAIRole = "user") => {
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

        const maxId = Math.max(...rawMessages.map((msg) => msg.id));
        // 创建新的用户消息
        const newUserMessage: MessageNode = {
          id: maxId + 1,
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
        console.debug("ai chat", pendingGroup);

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
    [rawMessages, chatId, activePath, sendMessageToAI]
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
  const switchVersion = useCallback((activeMsgId: string) => {
    console.debug("activeMsgId", activeMsgId);
    setActivePoint(activeMsgId);
  }, []);

  // 刷新AI回答
  const refreshResponse = useCallback(
    async (sessionId: string, modelId?: string) => {
      const session = sessionGroups.find((sg) => sg.session_id === sessionId);
      if (!session?.user_message) return;

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
    [sessionGroups, sendMessageToAI]
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
        await refreshMessages(); // 使用 refreshMessages 而不是 loadMessages
      } catch (err) {
        console.error("删除失败:", err);
        setError(err instanceof Error ? err.message : "删除失败");
      }
    },
    [refreshMessages]
  );

  const setModel = useCallback((model: IAiModel | undefined) => {
    setCurrModel(model);
  }, []);

  const actions = useMemo(
    () => ({
      switchVersion,
      editMessage,
      retryMessage,
      refreshResponse,
      loadMessages: refreshMessages, // 对外暴露的是 refreshMessages
      likeMessage,
      dislikeMessage,
      copyMessage,
      shareMessage,
      deleteMessage,
      setModel,
    }),
    [
      switchVersion,
      editMessage,
      retryMessage,
      refreshResponse,
      refreshMessages,
      likeMessage,
      dislikeMessage,
      copyMessage,
      shareMessage,
      deleteMessage,
      setModel,
    ]
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
      is_initialized: isInitialized, // 新增：初始化状态
      streaming_message: streamingMessage,
      streaming_session_id: streamingSessionId,
      current_model: currModel,
      error,
    },
    actions,
  };
}
