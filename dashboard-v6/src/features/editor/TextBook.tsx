// ─────────────────────────────────────────────
// Props
// ─────────────────────────────────────────────

import type { ArticleMode, ArticleType } from "../../api/article";
import type { ISearchParams } from "../../components/article/TypePali";
import type { TTarget } from "../../types";
import AnthologyTocTree from "../../components/anthology/AnthologyTocTree";
import TypeCourse from "../../components/article/TypeCourse";
import { useCourse } from "../../components/course/hooks/useCourse";
import Editor from "../../components/editor";

export interface ArticleEditorProps {
  articleId?: string;
  courseId?: string;
  mode?: ArticleMode;
  channelId?: string | null;

  // ── 路由事件回调（由 page 层处理导航）──
  /** 点击目录树中的文章时触发 */
  onArticleClick?: (
    anthologyId: string,
    articleId: string,
    target?: string
  ) => void;
  /** 选择了新的 anthology 时触发 */
  onAnthologySelect?: (anthologyId: string) => void;
  /** 文章内部触发跳转（type: 'article' | 'anthology' 等） */
  onArticleChange?: (
    type: ArticleType,
    id: string,
    target?: TTarget,
    param?: ISearchParams[]
  ) => void;
}

// ─────────────────────────────────────────────
// Component
// ─────────────────────────────────────────────

export default function TextBookEditor({
  articleId,
  courseId,
  mode = "read",
  channelId,
  onArticleClick,
  onArticleChange,
}: ArticleEditorProps) {
  const channels = channelId ? channelId.split("_") : undefined;
  const { data, loading } = useCourse(courseId);

  return (
    <Editor
      sidebarTitle="table of content"
      sidebar={
        loading ? (
          <>loading</>
        ) : (
          <AnthologyTocTree
            anthologyId={data?.anthology_id}
            articleId={articleId}
            channels={channels}
            onClick={(tocAnthology, article, target) => {
              onArticleClick?.(tocAnthology, article, target);
            }}
          />
        )
      }
      articleId={articleId}
      anthologyId={data?.anthology_id}
      channelId={channelId}
    >
      {({ expandButton }) => (
        <TypeCourse
          articleId={articleId}
          mode={mode}
          courseId={courseId}
          channelId={channelId}
          headerExtra={expandButton}
          onArticleChange={onArticleChange}
        />
      )}
    </Editor>
  );
}
