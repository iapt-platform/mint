import {
  CloseOutlined,
  MenuFoldOutlined,
  MenuUnfoldOutlined,
} from "@ant-design/icons";
import { Button, Splitter } from "antd";
import { useCallback, useState, type ReactNode } from "react";
import RightToolbar, { type RightToolbarTab } from "./RightToolbar";
import styles from "./SplitLayout.module.css";
import {
  SplitLayoutContext,
  type SplitLayoutContextValue,
} from "./SplitLayoutContext";

// ─────────────────────────────────────────────
// 常量：手工调整左侧栏宽度
// ─────────────────────────────────────────────

/** 左侧面板固定宽度（px）。左侧栏不参与拖拽，修改此值即可调整宽度。 */
const SIDEBAR_WIDTH = 260;

/** 右边工具栏固定宽度（px） */
const TOOLBAR_WIDTH = 40;

// ─────────────────────────────────────────────
// Props
// ─────────────────────────────────────────────

export interface SplitLayoutProps {
  /** 左侧面板标题，支持任意 ReactNode */
  sidebarTitle: ReactNode;
  /** 左侧面板内容 */
  sidebar: ReactNode;
  /**
   * 中间内容区。支持两种用法：
   *
   * 方案 A — Render Props，框架把 expandButton 作为参数传入：
   * ```tsx
   * <SplitLayout ...>
   *   {({ expandButton }) => <MyPage headerExtra={expandButton} />}
   * </SplitLayout>
   * ```
   *
   * 方案 B — 普通 ReactNode，内部自己调用 useSplitLayout()：
   * ```tsx
   * <SplitLayout ...>
   *   <ComplexPage />
   * </SplitLayout>
   * ```
   */
  children:
    | ReactNode
    | ((ctx: Pick<SplitLayoutContextValue, "expandButton">) => ReactNode);

  /**
   * 右边栏 tab 配置。不传则不渲染右边栏。
   * ```tsx
   * rightTabs={[
   *   { key: "chat",   icon: <CommentOutlined />, label: "对话" },
   *   { key: "search", icon: <SearchOutlined />,  label: "搜索" },
   * ]}
   * ```
   */
  rightTabs?: RightToolbarTab[];
  /**
   * 右边栏面板内容映射，key 对应 rightTabs 中的 key。
   * ```tsx
   * rightPanels={{ chat: <ChatPanel />, search: <SearchPanel /> }}
   * ```
   */
  rightPanels?: Record<string, ReactNode>;
  /** 右边栏面板默认宽度（px），默认 500 */
  defaultRightSize?: number;
  /** 右边栏面板最小宽度（px），默认 280 */
  minRightSize?: number;
  /** 右边栏面板最大宽度（px），默认 800 */
  maxRightSize?: number;
}

// ─────────────────────────────────────────────
// Component
// ─────────────────────────────────────────────

export default function SplitLayout({
  sidebarTitle,
  sidebar,
  children,
  rightTabs,
  rightPanels,
  defaultRightSize = 400,
  minRightSize = 280,
  maxRightSize = 800,
}: SplitLayoutProps) {
  // ── 左侧收起状态（持久化到 localStorage）──
  const COLLAPSED_KEY = "split-layout:sidebar-collapsed";
  const [collapsed, setCollapsed] = useState<boolean>(() => {
    try {
      return localStorage.getItem(COLLAPSED_KEY) === "true";
    } catch {
      return false;
    }
  });
  const toggle = useCallback(() => {
    setCollapsed((v) => {
      const next = !v;
      try {
        localStorage.setItem(COLLAPSED_KEY, String(next));
      } catch {
        // 静默降级
      }
      return next;
    });
  }, []);

  const expandButton = collapsed ? (
    <Button
      type="text"
      size="small"
      icon={<MenuUnfoldOutlined />}
      onClick={toggle}
      className={styles.expandBtn}
      title="展开侧边栏"
    />
  ) : null;

  // ── 右边栏状态 ──
  const [rightActiveKey, setRightActiveKey] = useState<string | null>(null);
  const rightOpen = rightActiveKey !== null;
  const hasRight = !!rightTabs?.length;

  const onRightTabClick = useCallback((key: string) => {
    setRightActiveKey((prev) => (prev === key ? null : key));
  }, []);

  const closeRightPanel = useCallback(() => setRightActiveKey(null), []);

  // ── Context ──
  const ctx: SplitLayoutContextValue = {
    collapsed,
    toggle,
    expandButton,
    rightActiveKey,
    onRightTabClick,
    closeRightPanel,
  };

  const centerContent =
    typeof children === "function" ? children({ expandButton }) : children;

  // 右边栏面板宽度：从 localStorage 读取初始值，拖拽后写回
  const STORAGE_KEY = "split-layout:right-panel-width";
  const [rightPanelWidth, setRightPanelWidth] = useState<number>(() => {
    try {
      const stored = localStorage.getItem(STORAGE_KEY);
      if (stored) {
        const parsed = Number(stored);
        if (
          Number.isFinite(parsed) &&
          parsed >= minRightSize &&
          parsed <= maxRightSize
        ) {
          return parsed;
        }
      }
    } catch {
      // localStorage 不可用（隐私模式等），静默降级
    }
    return defaultRightSize;
  });

  // Splitter sizes 受控：关闭时固定工具栏宽，展开时恢复持久化宽度
  const rightSize = rightOpen ? rightPanelWidth + TOOLBAR_WIDTH : TOOLBAR_WIDTH;

  const handleSplitterResize = useCallback(
    (sizes: number[]) => {
      if (rightOpen && sizes[1] !== undefined) {
        const contentWidth = sizes[1] - TOOLBAR_WIDTH;
        if (contentWidth > 0) {
          setRightPanelWidth(contentWidth);
          try {
            localStorage.setItem(STORAGE_KEY, String(contentWidth));
          } catch {
            // 静默降级
          }
        }
      }
    },
    [rightOpen]
  );

  return (
    <SplitLayoutContext.Provider value={ctx}>
      {/*
       * 整体布局：flex row
       *   左侧栏（固定宽度，不参与 Splitter）
       *   + 单个 Splitter lazy（中间内容 ↔ 右边栏）
       *
       * 左侧栏固定宽度由 SIDEBAR_WIDTH 常量控制，无拖拽，
       * 完全隔离于右侧 Splitter，彻底避免嵌套 Splitter 的坐标偏移 bug。
       */}
      <div className={styles.root}>
        {/* ── 左侧面板（固定宽，CSS 控制，collapsed 时完全隐藏）── */}
        {!collapsed && (
          <div className={styles.sidebar} style={{ width: SIDEBAR_WIDTH }}>
            <div className={styles.sidebarHeader}>
              <span className={styles.sidebarTitle}>{sidebarTitle}</span>
              <Button
                type="text"
                size="small"
                icon={<MenuFoldOutlined />}
                onClick={toggle}
                className={styles.collapseBtn}
                title="收起侧边栏"
              />
            </div>
            <div className={styles.sidebarContent}>{sidebar}</div>
          </div>
        )}

        {/* ── 右侧主区域：单个 Splitter lazy（中间 ↔ 右边栏）── */}
        <div className={styles.mainArea}>
          {hasRight ? (
            <Splitter
              lazy
              className={styles.splitter}
              onResize={handleSplitterResize}
            >
              {/* 中间内容 */}
              <Splitter.Panel className={styles.centerPanel}>
                {centerContent}
              </Splitter.Panel>

              {/* 右边栏 */}
              <Splitter.Panel
                size={rightSize}
                min={minRightSize + TOOLBAR_WIDTH}
                max={maxRightSize + TOOLBAR_WIDTH}
                resizable={rightOpen}
                className={styles.rightAreaPanel}
              >
                <div className={styles.rightArea}>
                  {/* 面板内容区（展开时显示） */}
                  {rightOpen && (
                    <div className={styles.rightPanelContent}>
                      <div className={styles.rightPanelHeader}>
                        <span className={styles.rightPanelTitle}>
                          {
                            rightTabs!.find((t) => t.key === rightActiveKey)
                              ?.label
                          }
                        </span>
                        <Button
                          type="text"
                          size="small"
                          icon={<CloseOutlined />}
                          onClick={closeRightPanel}
                          className={styles.collapseBtn}
                          title="关闭面板"
                        />
                      </div>
                      <div className={styles.rightPanelBody}>
                        {rightPanels?.[rightActiveKey!]}
                      </div>
                    </div>
                  )}

                  {/* 工具栏：始终可见，固定在最右侧 */}
                  <RightToolbar
                    tabs={rightTabs!}
                    activeKey={rightActiveKey}
                    onTabClick={onRightTabClick}
                  />
                </div>
              </Splitter.Panel>
            </Splitter>
          ) : (
            // 没有右边栏时，中间内容直接撑满
            <div className={styles.centerPanel}>{centerContent}</div>
          )}
        </div>
      </div>
    </SplitLayoutContext.Provider>
  );
}
