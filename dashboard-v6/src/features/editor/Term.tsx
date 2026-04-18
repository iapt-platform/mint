// ─────────────────────────────────────────────
// Props
// ─────────────────────────────────────────────

import { useEffect } from "react";
import type { ArticleMode } from "../../api/article";
import TypeTerm from "../../components/article/TypeTerm";
import Editor from "../../components/editor";
import { Recent } from "../../components/recent/Recent";
import { useAppSelector } from "../../hooks";
import { useSaveRecent } from "../../hooks/useSaveRecent";
import { currentUser } from "../../reducers/current-user";
import { useLocation } from "react-router";

export interface ArticleEditorProps {
  termId?: string;
  anthologyId?: string;
  /** 来自 query param "anthology"（无 anthologyId 路由参数时的备用） */
  anthology?: string | null;
  mode?: ArticleMode;
  channelId?: string | null;

  // ── 路由事件回调（由 page 层处理导航）──
  /** 选择了新的 term 时触发 */
  onTermSelect?: (id: string) => void;

  onEdit?: () => void;
}

// ─────────────────────────────────────────────
// Component
// ─────────────────────────────────────────────

export default function TermEditor({
  termId,
  anthologyId,
  mode = "read",
  channelId,
  onEdit,
}: ArticleEditorProps) {
  const currUser = useAppSelector(currentUser);

  const { save } = useSaveRecent();
  const { search } = useLocation();

  useEffect(() => {
    if (!currUser?.id || !termId) return;

    save({
      type: "term",
      article_id: termId,
      param: search || undefined,
    });
  }, [currUser?.id, termId, search, save]);

  return (
    <Editor
      sidebarTitle="recent scan"
      sidebar={
        currUser ? (
          <Recent userId={currUser?.id} pageSize={20} type="term" />
        ) : null
      }
      articleId={termId}
      anthologyId={anthologyId}
      channelId={channelId}
    >
      {({ expandButton }) => (
        <TypeTerm
          id={termId}
          mode={mode}
          channelId={channelId}
          headerExtra={expandButton}
          onEdit={onEdit}
        />
      )}
    </Editor>
  );
}
