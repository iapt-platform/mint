import { createContext, useContext } from "react";
import type { ReactNode } from "react";
import type { RightToolbarTab } from "./RightToolbar";

// ── 左侧栏 ──────────────────────────────────

export interface SplitLayoutContextValue {
  /** 左侧面板是否已收起 */
  collapsed: boolean;
  toggle: () => void;
  /**
   * 展开按钮节点（左侧收起时为真实按钮，展开时为 null）。
   * 右侧内容区通过 render props 或 useSplitLayout() 取得，自行决定放置位置。
   */
  expandButton: ReactNode;

  // ── 右边栏 ────────────────────────────────
  /** 当前激活的右边栏 tab key，null 表示面板已关闭 */
  rightActiveKey: string | null;
  /** 切换右边栏 tab（已激活则关闭，未激活则打开） */
  onRightTabClick: (key: string) => void;
  /** 关闭右边栏面板 */
  closeRightPanel: () => void;
}

export const SplitLayoutContext = createContext<SplitLayoutContextValue | null>(
  null
);

export function useSplitLayout(): SplitLayoutContextValue {
  const ctx = useContext(SplitLayoutContext);
  if (!ctx) {
    throw new Error("useSplitLayout must be used within <SplitLayout>");
  }
  return ctx;
}

// Re-export so callers only need to import from this file
export type { RightToolbarTab };
