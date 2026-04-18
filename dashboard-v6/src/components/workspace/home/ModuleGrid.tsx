// src/components/workspace/home/ModuleGrid.tsx
import type { CSSProperties } from "react";
import type { ModuleItem } from "../../../api/workspace";
import ModuleCard from "./ModuleCard";

type ModuleGridProps = {
  modules: ModuleItem[];
};

export default function ModuleGrid({ modules }: ModuleGridProps) {
  return (
    <div style={styles.grid}>
      {modules.map(({ key, ...mod }) => (
        <ModuleCard key={key} {...mod} />
      ))}
    </div>
  );
}

const styles: Record<string, CSSProperties> = {
  grid: {
    display: "grid",
    gridTemplateColumns: "repeat(3, 1fr)",
    gap: 16,
  },
};
