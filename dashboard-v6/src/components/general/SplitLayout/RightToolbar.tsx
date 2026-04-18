import { CloseOutlined } from "@ant-design/icons";
import { Button, Tooltip } from "antd";
import type { ReactNode } from "react";
import styles from "./RightToolbar.module.css";

export interface RightToolbarTab {
  /** 唯一 key */
  key: string;
  /** 图标 */
  icon: ReactNode;
  /** Tooltip 提示文字 & 面板标题 */
  label: string;
  /**
   * 面板内容（可选）。
   * - 首次点击时懒创建，之后切换/关闭只隐藏不销毁。
   * - 不传则点击图标不展开面板（纯按钮行为）。
   */
  content?: ReactNode;
}

interface RightToolbarProps {
  tabs: RightToolbarTab[];
  /** 当前激活的 tab key，null 表示面板已关闭 */
  activeKey: string | null;
  onTabClick: (key: string) => void;
  onClose: () => void;
}

export default function RightToolbar({
  tabs,
  activeKey,
  onTabClick,
  onClose,
}: RightToolbarProps) {
  // 记录哪些 tab 曾经被打开过（懒创建：首次点击后才挂载 DOM）
  // 使用模块级 WeakMap 不行，用组件内 Set ref 也可以，
  // 但最简单的方式是直接在渲染时用 Set 累积 —— 由于 activeKey 驱动，
  // 这里改用 rendered Set 存在 closure 里不合适，改用 useState 的 Set。
  // 注意：Set 是引用类型，useState 里直接 mutate 需要注意，
  // 这里每次 add 都返回新 Set 保证 immutability。
  const [mounted, setMounted] = React.useState<Set<string>>(new Set());

  // 当 activeKey 变化时，将新 key 加入 mounted set（懒创建）
  React.useEffect(() => {
    if (activeKey && !mounted.has(activeKey)) {
      setMounted((prev) => new Set([...prev, activeKey]));
    }
  }, [activeKey, mounted]);

  const activeTab = tabs.find((t) => t.key === activeKey);
  const panelOpen = activeKey !== null && !!activeTab?.content;

  return (
    <div className={styles.container}>
      {/* ── 面板内容区 ── */}
      {panelOpen && (
        <div className={styles.panel}>
          {/* 面板 header */}
          <div className={styles.panelHeader}>
            <span className={styles.panelTitle}>{activeTab!.label}</span>
            <Button
              type="text"
              size="small"
              icon={<CloseOutlined />}
              onClick={onClose}
              className={styles.closeBtn}
              title="关闭面板"
            />
          </div>

          {/* 各 tab 内容：懒创建，隐藏不销毁 */}
          <div className={styles.panelBody}>
            {tabs
              .filter((t) => t.content && mounted.has(t.key))
              .map((t) => (
                <div
                  key={t.key}
                  style={{ display: t.key === activeKey ? "contents" : "none" }}
                >
                  {t.content}
                </div>
              ))}
          </div>
        </div>
      )}

      {/* ── 图标工具栏 ── */}
      <div className={styles.toolbar}>
        {tabs.map((tab) => (
          <Tooltip key={tab.key} title={tab.label} placement="left">
            <Button
              type="text"
              size="small"
              icon={tab.icon}
              onClick={() => onTabClick(tab.key)}
              className={`${styles.tabBtn} ${activeKey === tab.key ? styles.active : ""}`}
              aria-label={tab.label}
              aria-pressed={activeKey === tab.key}
            />
          </Tooltip>
        ))}
      </div>
    </div>
  );
}

// React import（需要 useEffect / useState）
import React from "react";
