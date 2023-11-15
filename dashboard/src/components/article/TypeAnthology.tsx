import { ArticleMode, ArticleType } from "./Article";
import AnthologyDetail from "./AnthologyDetail";
import "./article.css";
import { useState } from "react";
import ErrorResult from "../general/ErrorResult";
import ArticleSkeleton from "./ArticleSkeleton";

interface IWidget {
  type?: ArticleType;
  articleId?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  onArticleChange?: Function;
  onFinal?: Function;
  onLoad?: Function;
}
const TypeAnthologyWidget = ({
  type,
  channelId,
  articleId,
  mode = "read",
  onArticleChange,
}: IWidget) => {
  const [loading, setLoading] = useState(false);
  const [errorCode, setErrorCode] = useState<number>();

  const channels = channelId?.split("_");
  return (
    <div>
      {loading ? (
        <ArticleSkeleton />
      ) : errorCode ? (
        <ErrorResult code={errorCode} />
      ) : (
        <></>
      )}
      <AnthologyDetail
        visible={!loading}
        onArticleSelect={(anthologyId: string, keys: string[]) => {
          if (typeof onArticleChange !== "undefined" && keys.length > 0) {
            onArticleChange("article", keys[0], {
              anthologyId: anthologyId,
            });
          }
        }}
        onLoading={(loading: boolean) => {
          setLoading(loading);
        }}
        onError={(code: number, message: string) => {
          setErrorCode(code);
        }}
        channels={channels}
        aid={articleId}
      />
    </div>
  );
};

export default TypeAnthologyWidget;
