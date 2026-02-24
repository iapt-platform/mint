import { useEffect, useState, type CSSProperties } from "react";
import WorkspaceHero from "../../components/workspace/home/WorkspaceHero";
import SectionPanel from "../../components/workspace/home/SectionPanel";
import ModuleGrid from "../../components/workspace/home/ModuleGrid";
import RecentList from "../../components/workspace/home/RecentList";
import { fetchModules, fetchRecentItems } from "../../api/workspace";
import type { ModuleItem, RecentItem } from "../../api/workspace";

export default function WorkspaceHome() {
  const [modules, setModules] = useState<ModuleItem[]>([]);
  const [recentItems, setRecentItems] = useState<RecentItem[]>([]);

  useEffect(() => {
    fetchModules().then(setModules);
    fetchRecentItems().then(setRecentItems);
  }, []);

  return (
    <div style={styles.page}>
      <WorkspaceHero />

      <div style={styles.content}>
        <SectionPanel title="主要栏目">
          <ModuleGrid modules={modules} />
        </SectionPanel>

        <SectionPanel title="最近访问">
          <RecentList items={recentItems} />
        </SectionPanel>
      </div>
    </div>
  );
}

const styles: Record<string, CSSProperties> = {
  page: {
    minHeight: "100vh",
    background: "#f9f8f6",
    fontFamily: "'Noto Serif SC', 'Source Han Serif CN', Georgia, serif",
  },
  content: {
    maxWidth: 880,
    margin: "0 auto",
    padding: "32px 32px 64px",
  },
};
