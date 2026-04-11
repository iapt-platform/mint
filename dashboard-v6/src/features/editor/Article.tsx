// ─────────────────────────────────────────────
// Props
// ─────────────────────────────────────────────

import type { ArticleMode } from "../../api/article";
import type { IChannel } from "../../api/channel";
import AnthologyTocTree from "../../components/anthology/AnthologyTocTree";
import TypeArticle from "../../components/article/TypeArticle";
import Editor from "../../components/editor";

export interface ArticleEditorProps {
  articleId?: string;
  anthologyId?: string;
  /** 来自 query param "anthology"（无 anthologyId 路由参数时的备用） */
  anthology?: string | null;
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
  onArticleChange?: (type: string, id: string) => void;
  onChannelSelect?: (selected: IChannel[]) => void;
}

// ─────────────────────────────────────────────
// Component
// ─────────────────────────────────────────────

export default function ArticleEditor({
  articleId,
  anthologyId,
  anthology,
  mode = "read",
  channelId,
  onArticleClick,
  onAnthologySelect,
  onArticleChange,
  onChannelSelect,
}: ArticleEditorProps) {
  const channels = channelId ? channelId.split("_") : undefined;

  return (
    <Editor
      sidebarTitle="table of content"
      sidebar={
        anthologyId ? (
          <AnthologyTocTree
            anthologyId={anthologyId}
            channels={channels}
            onClick={(tocAnthology, article, target) => {
              onArticleClick?.(tocAnthology, article, target);
            }}
          />
        ) : undefined
      }
      articleId={articleId}
      anthologyId={anthologyId}
      channelId={channelId}
      onChannelSelect={onChannelSelect}
    >
      {({ expandButton }) => (
        <TypeArticle
          articleId={articleId}
          mode={mode}
          anthologyId={anthologyId ?? anthology ?? undefined}
          channelId={channelId}
          headerExtra={expandButton}
          onAnthologySelect={onAnthologySelect}
          onArticleChange={onArticleChange}
        />
      )}
    </Editor>
  );
}
