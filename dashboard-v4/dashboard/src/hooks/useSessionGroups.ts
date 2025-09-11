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
  }, [activePath, computeSessionVersions, findCurrentVersion]);

  return useMemo(() => computeSessionGroups(), [computeSessionGroups]);
}
