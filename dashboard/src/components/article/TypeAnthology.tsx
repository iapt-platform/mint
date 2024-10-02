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
  onTitle?: Function;
}
const TypeAnthologyWidget = ({
  type,
  channelId,
  articleId,
  mode = "read",
  onArticleChange,
  onTitle,
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
        channels={channels}
        aid={articleId}
        onArticleClick={(
          anthologyId: string,
          articleId: string,
          target: string
        ) => {
          if (typeof onArticleChange !== "undefined" && articleId) {
            onArticleChange("article", articleId, target, {
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
        onTitle={(value: string) => {
          if (typeof onTitle !== "undefined") {
            onTitle(value);
          }
        }}
      />
    </div>
  );
};

export default TypeAnthologyWidget;
