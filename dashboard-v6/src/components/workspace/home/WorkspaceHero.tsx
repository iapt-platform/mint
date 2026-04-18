import { useMemo, type CSSProperties } from "react";
import { theme } from "antd";
import { getGreeting } from "../../../api/greetings";

export default function WorkspaceHero() {
  const greeting = useMemo(() => getGreeting(), []);
  const { token } = theme.useToken();

  const styles: Record<string, CSSProperties> = {
    hero: {
      background: `linear-gradient(160deg, ${token.colorBgContainer} 0%, ${token.colorBgLayout} 100%)`,
      borderBottom: `1px solid ${token.colorBorderSecondary}`,
      padding: "48px 0 36px",
    },
    inner: {
      maxWidth: 880,
      margin: "0 auto",
      padding: "0 32px",
    },
    label: {
      fontSize: 13,
      color: token.colorTextQuaternary,
      letterSpacing: "0.12em",
      textTransform: "uppercase",
      marginBottom: 8,
      fontFamily: "Georgia, serif",
    },
    title: {
      fontSize: 36,
      fontWeight: 700,
      color: token.colorText,
      margin: "0 0 8px",
      letterSpacing: "-0.02em",
      fontFamily: "'Noto Serif SC', 'Source Han Serif CN', Georgia, serif",
    },
    sub: {
      fontSize: 15,
      color: token.colorTextSecondary,
      margin: 0,
      fontFamily: "'Noto Serif SC', 'Source Han Serif CN', Georgia, serif",
    },
  };

  return (
    <div style={styles.hero}>
      <div style={styles.inner}>
        <p style={styles.label}>{greeting.en}</p>
        <h1 style={styles.title}>{greeting.zh}</h1>
        <p style={styles.sub}>{greeting.sub}</p>
      </div>
    </div>
  );
}
