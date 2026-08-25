import { MenuFoldOutlined, MenuUnfoldOutlined } from "@ant-design/icons";
import { Affix, Button, Popover, Splitter } from "antd";
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

/** 左侧面板固定宽度（px）。修改此值即可调整宽度，左侧栏不参与拖拽。 */
const SIDEBAR_WIDTH = 280;

/** 右边工具栏固定宽度（px） */
const TOOLBAR_WIDTH = 40;

// ─────────────────────────────────────────────
// localStorage keys
// ─────────────────────────────────────────────

const COLLAPSED_KEY = "split-layout:sidebar-collapsed";
const RIGHT_WIDTH_KEY = "split-layout:right-panel-width";

// ─────────────────────────────────────────────
// Props
// ─────────────────────────────────────────────

export interface SplitLayoutProps {
  /** 左侧面板标题，支持任意 ReactNode */
  sidebarTitle: ReactNode;
  /** 左侧面板内容（收起时隐藏不销毁，避免重复 fetch） */
  sidebar: ReactNode;
  /**
   * 中间内容区。支持两种用法：
   *
   * 方案 A — Render Props：
   * ```tsx
   * <SplitLayout ...>
   *   {({ expandButton }) => <MyPage headerExtra={expandButton} />}
   * </SplitLayout>
   * ```
   * 方案 B — 普通 ReactNode，内部调用 useSplitLayout()：
   * ```tsx
   * <SplitLayout ...><ComplexPage /></SplitLayout>
   * ```
   */
  children:
    | ReactNode
    | ((ctx: Pick<SplitLayoutContextValue, "expandButton">) => ReactNode);

  /**
   * 右边栏 tab 配置。每个 tab 可携带 content 面板内容。
   * - content 懒创建：首次点击后才挂载 DOM
   * - 切换/关闭面板时只隐藏，不销毁
   * - 不传 rightTabs 则不渲染右边栏
   */
  rightTabs?: RightToolbarTab[];

  /**
   * 外部请求打开某个 tab（业务层通过 redux 等触发）。
   * `seq` 每次递增代表一次新请求，内部据此去重并打开对应 key 的 tab。
   */
  openRequest?: { key: string; seq: number };

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
  openRequest,
  defaultRightSize = 500,
  minRightSize = 280,
  maxRightSize = 800,
}: SplitLayoutProps) {
  // ── 左侧收起状态（持久化）──
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
        /* 静默降级 */
      }
      return next;
    });
  }, []);

  const expandButton = collapsed ? (
    <Popover
      placement="bottomLeft"
      content={
        <div style={{ width: 300, height: 500, overflowY: "auto" }}>
          {sidebar}
        </div>
      }
    >
      <Button
        type="text"
        size="small"
        icon={<MenuUnfoldOutlined />}
        onClick={toggle}
        className={styles.expandBtn}
        title="展开侧边栏"
      />
    </Popover>
  ) : null;

  // ── 右边栏状态 ──
  const [rightActiveKey, setRightActiveKey] = useState<string | null>(null);
  const rightOpen = rightActiveKey !== null;
  const hasRight = !!rightTabs?.length;

  const onRightTabClick = useCallback((key: string) => {
    setRightActiveKey((prev) => (prev === key ? null : key));
  }, []);

  const closeRightPanel = useCallback(() => setRightActiveKey(null), []);

  // ── 外部打开请求（去重后打开对应 tab）──
  // 采用 React 官方推荐的「render 阶段调整 state」方式，避免 effect 内同步 setState。
  const [handledOpenSeq, setHandledOpenSeq] = useState<number | null>(null);
  if (openRequest && openRequest.seq !== handledOpenSeq) {
    setHandledOpenSeq(openRequest.seq);
    setRightActiveKey(openRequest.key);
  }

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

  // ── 右边栏面板宽度（持久化）──
  const [rightPanelWidth, setRightPanelWidth] = useState<number>(() => {
    try {
      const stored = localStorage.getItem(RIGHT_WIDTH_KEY);
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
      /* 静默降级 */
    }
    return defaultRightSize;
  });

  const rightSize = rightOpen ? rightPanelWidth + TOOLBAR_WIDTH : TOOLBAR_WIDTH;

  const handleSplitterResize = useCallback(
    (sizes: number[]) => {
      if (rightOpen && sizes[1] !== undefined) {
        const contentWidth = sizes[1] - TOOLBAR_WIDTH;
        if (contentWidth > 0) {
          setRightPanelWidth(contentWidth);
          try {
            localStorage.setItem(RIGHT_WIDTH_KEY, String(contentWidth));
          } catch {
            /* 静默降级 */
          }
        }
      }
    },
    [rightOpen]
  );

  return (
    <SplitLayoutContext.Provider value={ctx}>
      <div className={styles.root}>
        {/* ── 左侧面板：隐藏不销毁，避免 sidebar 内的 fetch 重复触发 ── */}
        <div
          className={styles.sidebar}
          style={{
            width: collapsed ? 0 : SIDEBAR_WIDTH,
            // 收起时用 visibility+overflow 隐藏，不从 DOM 移除
            overflow: "hidden",
            visibility: collapsed ? "hidden" : "visible",
          }}
        >
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

        {/* ── 右侧主区域 ── */}
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

              {/* 右边栏：RightToolbar 内部管理面板的懒创建与隐藏 */}
              <Splitter.Panel
                size={rightSize}
                min={minRightSize + TOOLBAR_WIDTH}
                max={maxRightSize + TOOLBAR_WIDTH}
                resizable={rightOpen}
                className={styles.rightAreaPanel}
              >
                <Affix offsetTop={0}>
                  <RightToolbar
                    tabs={rightTabs!}
                    activeKey={rightActiveKey}
                    onTabClick={onRightTabClick}
                    onClose={closeRightPanel}
                  />
                </Affix>
              </Splitter.Panel>
            </Splitter>
          ) : (
            <div className={styles.centerPanel}>{centerContent}</div>
          )}
        </div>
      </div>
    </SplitLayoutContext.Provider>
  );
}
