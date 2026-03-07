// ─────────────────────────────────────────────
// Props
// ─────────────────────────────────────────────

import type { ArticleMode } from "../../api/Article";
import TypeTerm from "../../components/article/TypeTerm";
import Editor from "../../components/editor";

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
  return (
    <Editor
      sidebarTitle="recent scan"
      sidebar={<>recent list</>}
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
