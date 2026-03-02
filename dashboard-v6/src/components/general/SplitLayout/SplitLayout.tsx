import { useCallback, useState, type ReactNode } from "react";
import { Button, Splitter } from "antd";
import { MenuFoldOutlined, MenuUnfoldOutlined } from "@ant-design/icons";
import styles from "./SplitLayout.module.css";
import {
  SplitLayoutContext,
  type SplitLayoutContextValue,
} from "./SplitLayoutContext";

// ─────────────────────────────────────────────
// Props
// ─────────────────────────────────────────────

export interface SplitLayoutProps {
  /** 左侧面板标题区域（左侧），支持任意 ReactNode */
  sidebarTitle: ReactNode;
  /** 左侧面板内容 */
  sidebar: ReactNode;
  /**
   * 右侧内容。
   *
   * 支持两种用法：
   *
   * 1. Render Props（方案 A）—— 框架直接把 expandButton 传入：
   *    ```tsx
   *    <SplitLayout ...>
   *      {({ expandButton }) => <MyPage headerExtra={expandButton} />}
   *    </SplitLayout>
   *    ```
   *
   * 2. 普通 ReactNode（方案 B）—— 右侧组件自己调用 useSplitLayout()：
   *    ```tsx
   *    <SplitLayout ...>
   *      <ComplexPage />
   *    </SplitLayout>
   *    ```
   */
  children:
    | ReactNode
    | ((ctx: Pick<SplitLayoutContextValue, "expandButton">) => ReactNode);
  /** 左侧面板默认宽度（px），默认 240 */
  defaultSidebarSize?: number;
  /** 左侧面板最小宽度（px），默认 160 */
  minSidebarSize?: number;
  /** 左侧面板最大宽度（px），默认 480 */
  maxSidebarSize?: number;
}

// ─────────────────────────────────────────────
// Component
// ─────────────────────────────────────────────

export default function SplitLayout({
  sidebarTitle,
  sidebar,
  children,
  defaultSidebarSize = 240,
  minSidebarSize = 160,
  maxSidebarSize = 480,
}: SplitLayoutProps) {
  const [collapsed, setCollapsed] = useState(false);

  const toggle = useCallback(() => setCollapsed((v) => !v), []);

  // 展开按钮：仅在收起状态下渲染真实节点
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

  const ctx: SplitLayoutContextValue = { collapsed, toggle, expandButton };

  // 右侧内容：支持 render props 和普通 ReactNode 两种形式
  const rightContent =
    typeof children === "function" ? children({ expandButton }) : children;

  return (
    <SplitLayoutContext.Provider value={ctx}>
      <Splitter className={styles.splitter}>
        {/* ── 左侧面板 ── */}
        <Splitter.Panel
          size={collapsed ? 0 : defaultSidebarSize}
          min={collapsed ? 0 : minSidebarSize}
          max={maxSidebarSize}
          className={styles.leftPanel}
          collapsible
        >
          <div
            className={styles.sidebarInner}
            style={{ display: collapsed ? "none" : "flex" }}
          >
            {/* 标题行：左侧 title，右侧收起按钮 */}
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

            {/* 侧边栏主体内容 */}
            <div className={styles.sidebarContent}>{sidebar}</div>
          </div>
        </Splitter.Panel>

        {/* ── 右侧面板 ── */}
        <Splitter.Panel className={styles.rightPanel}>
          {rightContent}
        </Splitter.Panel>
      </Splitter>
    </SplitLayoutContext.Provider>
  );
}
