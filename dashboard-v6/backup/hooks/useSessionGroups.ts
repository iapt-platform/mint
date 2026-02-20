// dashboard-v4/dashboard/src/hooks/useSessionGroups.ts
import { useMemo, useCallback } from "react";
import type { MessageNode, SessionInfo, VersionInfo } from "../types/chat"

export function useSessionGroups(
  activePath: MessageNode[],
  rawMessages: MessageNode[]
) {
  const computeSessionVersions = useCallback(
    (sessionId: string): VersionInfo[] => {
      /**
       * 找到session的parent message
       * parent message children 就是versions
       */
      // 找到该session的所有消息
      const sessionMessages = rawMessages.filter(
        (m) => m.session_id === sessionId
      );

      if (sessionMessages.length === 0) {
        return [];
      }
      const firstMsg = sessionMessages.sort((a, b) => a.id - b.id)[0];
      const parentMsg = rawMessages.find(
        (value) => value.uid === firstMsg.parent_id
      );

      if (!parentMsg) {
        return [];
      }
      const childrenMsg = rawMessages.filter(
        (value) => value.parent_id === parentMsg.uid
      );
      console.debug("parentMsg", parentMsg, childrenMsg);

      // 转换为VersionInfo数组
      const versions: VersionInfo[] = childrenMsg.map((msg, index) => {
        return {
          version_index: index,
          message_id: msg.uid,
        };
      });

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
      if (!activeAiMsg) return versions.length - 1;

      // 根据创建时间找到对应的版本索引
      const versionIndex = versions.findIndex(
        (v) => v.message_id === activeAiMsg.uid
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
