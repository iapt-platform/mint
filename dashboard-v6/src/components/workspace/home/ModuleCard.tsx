// src/components/workspace/home/ModuleCard.tsx

import type { CSSProperties } from "react";
import { theme } from "antd";
import { useNavigate } from "react-router";
import {
  BookOutlined,
  FileTextOutlined,
  CheckSquareOutlined,
  ArrowRightOutlined,
} from "@ant-design/icons";
import type { ModuleItem } from "../../../api/workspace";

const iconMap: Record<string, React.ReactNode> = {
  BookOutlined: <BookOutlined />,
  FileTextOutlined: <FileTextOutlined />,
  CheckSquareOutlined: <CheckSquareOutlined />,
};

type ModuleCardProps = ModuleItem;

export default function ModuleCard({
  title,
  titleZh,
  icon,
  description,
  stats,
  color,
  bg,
  accent,
  path,
}: ModuleCardProps) {
  const navigate = useNavigate();
  const { token } = theme.useToken();

  const styles: Record<string, CSSProperties> = {
    card: {
      borderRadius: token.borderRadiusLG,
      padding: 20,
      border: `1px solid ${token.colorBorderSecondary}`,
    },
    inner: {
      display: "flex",
      flexDirection: "column",
      gap: 12,
      position: "relative",
    },
    icon: {
      fontSize: 22,
      width: 44,
      height: 44,
      borderRadius: 10,
      border: "1.5px solid",
      display: "flex",
      alignItems: "center",
      justifyContent: "center",
      background: token.colorBgContainer + "99",
    },
    info: {
      flex: 1,
    },
    titleRow: {
      display: "flex",
      alignItems: "baseline",
      gap: 8,
      marginBottom: 6,
    },
    title: {
      fontSize: 16,
      fontWeight: 700,
      letterSpacing: "-0.01em",
      fontFamily: "Georgia, serif",
    },
    titleZh: {
      fontSize: 12,
      color: token.colorTextTertiary,
    },
    desc: {
      fontSize: 13,
      color: token.colorTextSecondary,
      lineHeight: 1.6,
      margin: "0 0 8px",
    },
    stats: {
      fontSize: 12,
      fontWeight: 500,
    },
    arrow: {
      position: "absolute",
      top: 0,
      right: 0,
      fontSize: 14,
      opacity: 0.5,
    },
  };

  return (
    <>
      <div
        style={{ ...styles.card, background: bg }}
        className="workspace-module-card"
        onClick={() => navigate(path)}
      >
        <div style={styles.inner}>
          <div style={{ ...styles.icon, color, borderColor: color + "33" }}>
            {iconMap[icon]}
          </div>
          <div style={styles.info}>
            <div style={styles.titleRow}>
              <span style={{ ...styles.title, color: accent }}>{title}</span>
              <span style={styles.titleZh}>{titleZh}</span>
            </div>
            <p style={styles.desc}>{description}</p>
            <span style={{ ...styles.stats, color }}>{stats}</span>
          </div>
          <ArrowRightOutlined style={{ ...styles.arrow, color }} />
        </div>
      </div>

      <style>{`
  .workspace-module-card {
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: ${token.boxShadowTertiary};
  }
  .workspace-module-card:hover {
    transform: translateY(-3px);
    box-shadow: ${token.boxShadow};
  }
`}</style>
    </>
  );
}
