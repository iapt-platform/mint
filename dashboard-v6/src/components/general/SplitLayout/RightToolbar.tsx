import { Button, Tooltip } from "antd";
import type { ReactNode } from "react";
import styles from "./RightToolbar.module.css";

export interface RightToolbarTab {
  /** 唯一 key，对应面板内容 */
  key: string;
  /** 图标 */
  icon: ReactNode;
  /** Tooltip 提示文字 */
  label: string;
}

interface RightToolbarProps {
  tabs: RightToolbarTab[];
  /** 当前激活的 tab key，null 表示面板已关闭 */
  activeKey: string | null;
  /** 点击图标时回调：已激活则关闭（传 null），未激活则打开 */
  onTabClick: (key: string) => void;
}

export default function RightToolbar({
  tabs,
  activeKey,
  onTabClick,
}: RightToolbarProps) {
  return (
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
  );
}
