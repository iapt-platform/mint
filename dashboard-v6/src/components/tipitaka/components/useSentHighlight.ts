import { useEffect, useRef } from "react";
import { useAppSelector } from "../../../hooks";
import { anchor, highlight, message } from "../../../reducers/discussion";

export const SENT_HIGHLIGHT_BG = "#1890ff33";

/**
 * 阅读模式的译文跟着讨论面板走（编辑模式由 SentCell 负责，口径相同）：
 * - 定位：anchor 指向容器里的某句译文（`[data-sent-uid]`）时滚到那句
 * - 高亮：正在看讨论的那句、「本章」列表选中的那句
 */
export const useSentHighlight = <T extends HTMLElement = HTMLDivElement>() => {
  const anchorInfo = useAppSelector(anchor);
  const discussionMessage = useAppSelector(message);
  const highlightId = useAppSelector(highlight);
  const rootRef = useRef<T>(null);

  useEffect(() => {
    if (!anchorInfo?.resId) return;
    rootRef.current
      ?.querySelector<HTMLElement>(
        `[data-sent-uid="${CSS.escape(anchorInfo.resId)}"]`
      )
      ?.scrollIntoView({ behavior: "smooth", block: "center", inline: "nearest" });
  }, [anchorInfo]);

  const isHighlighted = (id?: string) =>
    !!id && (id === discussionMessage?.resId || id === highlightId);

  return { rootRef, isHighlighted };
};
