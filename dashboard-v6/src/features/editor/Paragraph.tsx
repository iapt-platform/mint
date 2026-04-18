// ─────────────────────────────────────────────
// Props
// ─────────────────────────────────────────────

import { useLocation } from "react-router";
import type { ArticleMode, ArticleType } from "../../api/article";
import TypePali, {
  type ISearchParams,
} from "../../components/article/TypePali";
import Editor from "../../components/editor";
import PaliTextToc from "../../components/tipitaka/PaliTextToc";
import { useSaveRecent } from "../../hooks/useSaveRecent";
import type { TTarget } from "../../types";
import { useEffect } from "react";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";

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

export default function ParaEditor({
  chapterId,
  mode = "read",
  channelId,
  onArticleChange,
}: ChapterEditorProps) {
  const [book, para] = chapterId
    ? chapterId.split("-").map((item) => parseInt(item))
    : [undefined, undefined];
  const currUser = useAppSelector(currentUser);
  const { save } = useSaveRecent();
  const { search } = useLocation();

  useEffect(() => {
    if (!currUser?.id || !chapterId) return;

    save({
      type: "chapter",
      article_id: chapterId,
      param: search || undefined,
    });
  }, [currUser?.id, chapterId, search, save]);

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
      articleType="chapter"
      channelId={channelId}
      onChannelSelect={(selected) => {
        if (chapterId) {
          const channelParams = [
            {
              key: "channel",
              value: selected.map((item) => item.id).join("_"),
            },
          ];
          console.debug("onChannelSelect", channelParams);
          onArticleChange?.("chapter", chapterId, "_self", channelParams);
        }
      }}
    >
      {({ expandButton }) => (
        <TypePali
          id={chapterId}
          type="para"
          mode={mode}
          channelId={channelId}
          headerExtra={expandButton}
          onArticleChange={onArticleChange}
        />
      )}
    </Editor>
  );
}
