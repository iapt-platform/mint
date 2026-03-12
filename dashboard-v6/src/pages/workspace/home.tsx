// src/pages/workspace/home.tsx
import { useEffect, useState, type CSSProperties } from "react";
import WorkspaceHero from "../../components/workspace/home/WorkspaceHero";
import SectionPanel from "../../components/workspace/home/SectionPanel";
import ModuleGrid from "../../components/workspace/home/ModuleGrid";
import RecentList from "../../components/workspace/home/RecentList";
import { fetchModules, fetchRecentItems } from "../../api/workspace";
import type { ModuleItem, RecentItem } from "../../api/workspace";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";

export default function WorkspaceHome() {
  const [modules, setModules] = useState<ModuleItem[]>([]);
  const [recentItems, setRecentItems] = useState<RecentItem[]>([]);
  const user = useAppSelector(currentUser);

  useEffect(() => {
    if (!user) {
      return;
    }
    fetchModules().then(setModules);
    fetchRecentItems(user?.id).then(setRecentItems);
  }, [user]);

  return (
    <div style={styles.page}>
      <title>欢迎来到wikipali</title>
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
    fontFamily: "'Noto Serif SC', 'Source Han Serif CN', Georgia, serif",
  },
  content: {
    maxWidth: 880,
    margin: "0 auto",
    padding: "32px 32px 64px",
  },
};
