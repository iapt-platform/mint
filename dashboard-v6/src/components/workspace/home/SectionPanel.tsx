import type { ReactNode, CSSProperties } from "react";

type SectionPanelProps = {
  title: string;
  children: ReactNode;
  style?: CSSProperties;
};

export default function SectionPanel({ title, children, style }: SectionPanelProps) {
  return (
    <section style={{ marginBottom: 40, ...style }}>
      <h2 style={styles.title}>
        <span style={styles.dot} />
        {title}
      </h2>
      {children}
    </section>
  );
}

const styles: Record<string, CSSProperties> = {
  title: {
    fontSize: 13,
    fontWeight: 600,
    color: "#8c7e6e",
    letterSpacing: "0.1em",
    textTransform: "uppercase",
    marginBottom: 16,
    display: "flex",
    alignItems: "center",
    gap: 8,
    fontFamily: "Georgia, serif",
  },
  dot: {
    display: "inline-block",
    width: 6,
    height: 6,
    borderRadius: "50%",
    background: "#c4a97a",
    flexShrink: 0,
  },
};
