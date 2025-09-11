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
