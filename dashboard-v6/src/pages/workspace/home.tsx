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
import type { ArticleMode } from "../../api/article";

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
            onClick={(item) => {
              const { type, id } = item;
              if (type === "chapter" || type === "para" || type === "cs-para") {
                navigate(
                  `/workspace/tipitaka/${type}/${id}${recentSearch(item.param)}`,
                );
              }
            }}
          />
        </SectionPanel>
      </div>
    </div>
  );
}

const ARTICLE_MODES: ArticleMode[] = ["read", "edit", "wbw", "auto"];

/**
 * 把 recent.param（记录时的查询参数 JSON）还原成 `?channel=...&mode=...`。
 * 只还原 channel 与 mode：book/para 已在路径里；v4 写入的 mode=reading 不是
 * v6 的取值，丢掉走默认。
 */
function recentSearch(param: string | null): string {
  if (!param) return "";
  let obj: Record<string, unknown>;
  try {
    obj = JSON.parse(param);
  } catch {
    return "";
  }
  if (!obj || typeof obj !== "object") return "";
  const query = new URLSearchParams();
  if (typeof obj.channel === "string" && obj.channel) {
    query.set("channel", obj.channel);
  }
  if (ARTICLE_MODES.includes(obj.mode as ArticleMode)) {
    query.set("mode", obj.mode as string);
  }
  const q = query.toString();
  return q ? `?${q}` : "";
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
