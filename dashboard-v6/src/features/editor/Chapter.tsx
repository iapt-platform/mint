// ─────────────────────────────────────────────
// Props
// ─────────────────────────────────────────────

import type { ArticleMode, ArticleType } from "../../api/article";
import TypePali, {
  type ISearchParams,
} from "../../components/article/TypePali";
import Editor from "../../components/editor";
import PaliTextToc from "../../components/tipitaka/PaliTextToc";
import type { TTarget } from "../../types";

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
    target: TTarget,
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
  const [book, para] = chapterId
    ? chapterId.split("-").map((item) => parseInt(item))
    : [undefined, undefined];
  return (
    <Editor
      sidebarTitle="recent scan"
      sidebar={
        <PaliTextToc
          book={book}
          para={para}
          onSelect={(selected) => {
            if (selected) {
              onArticleChange?.("chapter", selected[0], "_self");
            }
          }}
        />
      }
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
