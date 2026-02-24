import { useMemo, type CSSProperties } from "react";
import { getGreeting } from "../../../api/greetings";

export default function WorkspaceHero() {
  // useMemo 确保同一次渲染内问候语固定，不会因重渲染随机变化
  const greeting = useMemo(() => getGreeting(), []);

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

const styles: Record<string, CSSProperties> = {
  hero: {
    background: "linear-gradient(160deg, #fff 0%, #f0ede8 100%)",
    borderBottom: "1px solid #e8e4de",
    padding: "48px 0 36px",
  },
  inner: {
    maxWidth: 880,
    margin: "0 auto",
    padding: "0 32px",
  },
  label: {
    fontSize: 13,
    color: "#b5a898",
    letterSpacing: "0.12em",
    textTransform: "uppercase",
    marginBottom: 8,
    fontFamily: "Georgia, serif",
  },
  title: {
    fontSize: 36,
    fontWeight: 700,
    color: "#2d2416",
    margin: "0 0 8px",
    letterSpacing: "-0.02em",
    fontFamily: "'Noto Serif SC', 'Source Han Serif CN', Georgia, serif",
  },
  sub: {
    fontSize: 15,
    color: "#8c7e6e",
    margin: 0,
    fontFamily: "'Noto Serif SC', 'Source Han Serif CN', Georgia, serif",
  },
};
