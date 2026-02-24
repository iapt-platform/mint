import type { CSSProperties } from "react";
import { ClockCircleOutlined } from "@ant-design/icons";
import type { RecentItem as RecentItemType } from "../../../api/workspace";

const typeColor: Record<string, string> = {
  tipitaka: "#b5854a",
  article: "#4a7fb5",
  task: "#4ab58a",
};

type RecentItemProps = RecentItemType & {
  onClick?: () => void;
};

export default function RecentItem({
  emoji,
  title,
  subtitle,
  type,
  time,
  onClick,
}: RecentItemProps) {
  const color = typeColor[type];

  return (
    <>
      <div style={styles.row} className="workspace-recent-item" onClick={onClick}>
        <div style={styles.emoji}>{emoji}</div>
        <div style={styles.info}>
          <span style={styles.title}>{title}</span>
          <span style={styles.subtitle}>{subtitle}</span>
        </div>
        <div style={styles.right}>
          <span style={{ ...styles.tag, color, background: color + "15" }}>
            {type}
          </span>
          <span style={styles.time}>
            <ClockCircleOutlined style={{ marginRight: 4, fontSize: 11 }} />
            {time}
          </span>
        </div>
      </div>

      <style>{`
        .workspace-recent-item {
          transition: background 0.15s ease;
          cursor: pointer;
        }
        .workspace-recent-item:hover {
          background: #f7f7f5 !important;
        }
      `}</style>
    </>
  );
}

const styles: Record<string, CSSProperties> = {
  row: {
    display: "flex",
    alignItems: "center",
    gap: 14,
    padding: "14px 20px",
    borderBottom: "1px solid #f0ece6",
    background: "#fff",
  },
  emoji: {
    fontSize: 20,
    width: 36,
    textAlign: "center",
    flexShrink: 0,
  },
  info: {
    flex: 1,
    display: "flex",
    flexDirection: "column",
    gap: 2,
    minWidth: 0,
  },
  title: {
    fontSize: 14,
    fontWeight: 500,
    color: "#2d2416",
    overflow: "hidden",
    textOverflow: "ellipsis",
    whiteSpace: "nowrap",
  },
  subtitle: {
    fontSize: 12,
    color: "#a09080",
  },
  right: {
    display: "flex",
    flexDirection: "column",
    alignItems: "flex-end",
    gap: 4,
    flexShrink: 0,
  },
  tag: {
    fontSize: 11,
    fontWeight: 600,
    padding: "2px 8px",
    borderRadius: 4,
    letterSpacing: "0.04em",
    textTransform: "uppercase",
  },
  time: {
    fontSize: 11,
    color: "#b5a898",
    display: "flex",
    alignItems: "center",
  },
};
