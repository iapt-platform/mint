import { useMemo, useCallback } from "react";
import { MessageNode } from "../types/chat";

export function useActivePath(
  rawMessages: MessageNode[],
  activePoint?: string
) {
  const computeActivePath = useCallback(() => {
    // 从root消息开始，最新消息的路径构建激活链
    console.debug("ai chat", rawMessages);
    const activePath: MessageNode[] = [];

    // 找到system消息（根节点）
    let rootMsg = rawMessages.find((m) => !m.parent_id);
    if (!rootMsg) return [];

    if (activePoint) {
      //向上
      const activeMsg = rawMessages.find((v) => v.uid === activePoint);
      if (!activeMsg) {
        return [];
      }
      let currParent: MessageNode | undefined = rawMessages.find(
        (m) => m.uid === activeMsg.parent_id
      );
      while (currParent) {
        const pId = currParent.parent_id;
        activePath.unshift(currParent);
        currParent = rawMessages.find((v) => v.uid === pId);
      }
      rootMsg = activeMsg;
    }
    let current: MessageNode | undefined = rootMsg;
    while (current) {
      const currId: string = current.uid;
      activePath.push(current);
      // 找到当前消息的激活子消息
      current = rawMessages.filter((value) => value.parent_id === currId).pop();
    }

    return activePath;
  }, [activePoint, rawMessages]);

  return useMemo(() => computeActivePath(), [computeActivePath]);
}
