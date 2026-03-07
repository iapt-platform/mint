// ─────────────────────────────────────────────
// Props
// ─────────────────────────────────────────────

import type { ArticleMode, ArticleType } from "../../api/Article";
import TypePali, {
  type ISearchParams,
} from "../../components/article/TypePali";
import Editor from "../../components/editor";

export interface ChapterEditorProps {
  chapterId?: string;
  mode?: ArticleMode;
  channelId?: string | null;

  // ── 路由事件回调（由 page 层处理导航）──
  /** 选择了新的 chapter 时触发 */
  onSelect?: (id: string) => void;
  onArticleChange?: (
    type: ArticleType,
    id: string,
    target: string,
    param?: ISearchParams[]
  ) => void;
}

// ─────────────────────────────────────────────
// Component
// ─────────────────────────────────────────────

export default function ChapterEditor({
  chapterId,
  mode = "read",
  channelId,
  onArticleChange,
}: ChapterEditorProps) {
  return (
    <Editor
      sidebarTitle="recent scan"
      sidebar={<>recent list</>}
      articleId={chapterId}
      channelId={channelId}
    >
      {({ expandButton }) => (
        <TypePali
          id={chapterId}
          type="chapter"
          mode={mode}
          channelId={channelId}
          headerExtra={expandButton}
          onArticleChange={onArticleChange}
        />
      )}
    </Editor>
  );
}
