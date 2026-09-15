// src/pages/workspace/home.tsx
import { useEffect, useState, type CSSProperties } from "react";
import { useIntl } from "react-intl";
import WorkspaceHero from "../../components/workspace/home/WorkspaceHero";
import SectionPanel from "../../components/workspace/home/SectionPanel";
import ModuleGrid from "../../components/workspace/home/ModuleGrid";
import RecentList from "../../components/workspace/home/RecentList";
import { fetchModules, fetchRecentItems } from "../../api/workspace";
import type { ModuleItem, RecentItem } from "../../api/workspace";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";
import { useNavigate } from "react-router";

export default function WorkspaceHome() {
  const [modules, setModules] = useState<ModuleItem[]>([]);
  const [recentItems, setRecentItems] = useState<RecentItem[]>([]);
  const user = useAppSelector(currentUser);
  const navigate = useNavigate();
  const intl = useIntl();

  useEffect(() => {
    if (!user) {
      return;
    }
    fetchModules().then(setModules);
    fetchRecentItems(user?.id).then(setRecentItems);
  }, [user]);

  return (
    <div style={styles.page}>
      <title>{intl.formatMessage({ id: "pages.workspace.home.title" })}</title>
      <WorkspaceHero />

      <div style={styles.content}>
        <SectionPanel title="主要栏目">
          <ModuleGrid modules={modules} />
        </SectionPanel>

        <SectionPanel title="最近访问">
          <RecentList
            items={recentItems}
            onClick={(type, id) => {
              if (type === "chapter" || type === "para" || type === "cs-para") {
                navigate(`/workspace/tipitaka/${type}/${id}`);
              }
            }}
          />
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
