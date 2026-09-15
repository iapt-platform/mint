// ─────────────────────────────────────────────
// Props
// ─────────────────────────────────────────────

import { useEffect, useState } from "react";
import { useLocation } from "react-router";
import type {
  ArticleMode,
  ArticleType,
  ICSParaNavData,
} from "../../api/article";
import { fetchCSParaNav } from "../../api/article";
import TypePali, {
  type ISearchParams,
} from "../../components/article/TypePali";
import NavigateButton from "../../components/article/components/NavigateButton";
import ArticleSkeleton from "../../components/article/components/ArticleSkeleton";
import ErrorResult from "../../components/general/ErrorResult";
import Editor from "../../components/editor";
import PaliTextToc from "../../components/tipitaka/PaliTextToc";
import { useSaveRecent } from "../../hooks/useSaveRecent";
import type { TTarget } from "../../types";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";
import { HttpError } from "../../request";

export interface CsParaEditorProps {
  /** cs-para id，形如 `book_para_page`，例如 `169_3_64` */
  articleId?: string;
  mode?: ArticleMode;
  channelId?: string | null;

  // ── 路由事件回调（由 page 层处理导航）──
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

export default function CsParaEditor({
  articleId,
  mode = "read",
  channelId,
  onArticleChange,
}: CsParaEditorProps) {
  const [nav, setNav] = useState<ICSParaNavData>();
  const [errorCode, setErrorCode] = useState<number>();
  const [errorMessage, setErrorMessage] = useState<string>();
  const currUser = useAppSelector(currentUser);
  const { save } = useSaveRecent();
  const { search } = useLocation();

  // 记录最近访问
  useEffect(() => {
    if (!currUser?.id || !articleId) return;
    const paramObj = search
      ? Object.fromEntries(new URLSearchParams(search))
      : undefined;
    save({
      type: "cs-para",
      article_id: articleId,
      param: JSON.stringify(paramObj),
    });
  }, [currUser?.id, articleId, search, save]);

  // 拉取 cs-para 导航数据，推导实际要渲染的段落区间
  useEffect(() => {
    if (typeof articleId === "undefined") {
      console.error("articleId 不能为空");
      return;
    }
    const pageParam = articleId.split("_");
    if (pageParam.length !== 3) {
      console.error("pageParam 必须为三个");
      return;
    }
    setNav(undefined);
    setErrorCode(undefined);
    setErrorMessage(undefined);
    fetchCSParaNav(articleId)
      .then((json) => {
        if (json.ok) {
          setNav(json.data);
        } else {
          setErrorCode(500);
          setErrorMessage(json.message);
        }
      })
      .catch((e) => {
        console.error(e);
        if (e instanceof HttpError) {
          setErrorCode(e.status);
          setErrorMessage(e.message);
        } else {
          setErrorCode(500);
        }
      });
  }, [articleId]);

  // 由导航数据推导实际要渲染的段落区间 id（book-start-end）
  const paraId = nav
    ? `${nav.curr.book}-${nav.curr.start}-${nav.end}`
    : undefined;
  const book = nav?.curr.book;
  const para = nav?.curr.start;

  const goto = (
    offset: number,
    event: React.MouseEvent<HTMLElement, MouseEvent>
  ) => {
    if (!articleId) return;
    const pageParam = articleId.split("_");
    if (pageParam.length !== 3) return;
    const nextPage = parseInt(pageParam[2]) + offset;
    if (nextPage < 0) return;
    const id = `${pageParam[0]}_${pageParam[1]}_${nextPage}`;
    const target = event.ctrlKey || event.metaKey ? "_blank" : "_self";
    onArticleChange?.("cs-para", id, target);
  };

  if (errorCode !== undefined) {
    return <ErrorResult code={errorCode} message={errorMessage} />;
  }

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
      articleId={paraId}
      articleType="para"
      channelId={channelId}
      onChannelSelect={(selected) => {
        if (articleId) {
          const channelParams = [
            {
              key: "channel",
              value: selected.map((item) => item.id).join("_"),
            },
          ];
          console.debug("onChannelSelect", channelParams);
          onArticleChange?.("cs-para", articleId, "_self", channelParams);
        }
      }}
    >
      {({ expandButton }) =>
        nav ? (
          <>
            <TypePali
              id={paraId}
              type="para"
              mode={mode}
              channelId={channelId}
              headerExtra={expandButton}
              hideNav
              onArticleChange={onArticleChange}
            />
            <NavigateButton
              prevTitle={nav.prev?.content.slice(0, 10)}
              nextTitle={nav.next?.content.slice(0, 10)}
              onPrev={(event) => goto(-1, event)}
              onNext={(event) => goto(1, event)}
            />
          </>
        ) : (
          <ArticleSkeleton />
        )
      }
    </Editor>
  );
}
