import type { ArticleMode, ArticleType } from "./Article";
import AnthologyDetail from "./AnthologyDetail";
import "./article.css";
import { useState, useMemo } from "react";
import ErrorResult from "../general/ErrorResult";
import ArticleSkeleton from "./ArticleSkeleton";

interface IWidget {
  type?: ArticleType;
  articleId?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  onArticleChange?: (
    type: string,
    articleId: string,
    target: string,
    extra?: { anthologyId?: string }
  ) => void;
  onFinal?: () => void;
  onLoad?: () => void;
  onTitle?: (title: string) => void;
}

const TypeAnthologyWidget = ({
  channelId,
  articleId,
  onArticleChange,
  onTitle,
}: IWidget) => {
  const [loading, setLoading] = useState(false);
  const [errorCode, setErrorCode] = useState<number | null>(null);

  /** ✅ 避免每次 render 都 split */
  const channels = useMemo(
    () => (channelId ? channelId.split("_") : undefined),
    [channelId]
  );

  return (
    <div>
      {loading && <ArticleSkeleton />}

      {!loading && errorCode && <ErrorResult code={errorCode} />}

      {!errorCode && (
        <AnthologyDetail
          visible={!loading}
          channels={channels}
          aid={articleId}
          onArticleClick={(anthologyId, articleId, target) => {
            onArticleChange?.("article", articleId, target, {
              anthologyId,
            });
          }}
          onLoading={setLoading}
          onError={(error: unknown) => {
            console.error(error);
            //TODO get real error code
            setErrorCode(404);
            //setErrorCode(message); //old code
          }}
          onTitle={(value) => {
            onTitle?.(value);
          }}
        />
      )}
    </div>
  );
};

export default TypeAnthologyWidget;
