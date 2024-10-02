import { useEffect, useState } from "react";

import { get } from "../../request";
import { IArticleDataResponse } from "../api/Article";
import ArticleView from "./ArticleView";
import { ITermResponse } from "../api/Term";
import { ArticleMode } from "./Article";
import "./article.css";
import { message } from "antd";
import ArticleSkeleton from "./ArticleSkeleton";
import ErrorResult from "../general/ErrorResult";

interface IWidget {
  articleId?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  onArticleChange?: Function;
  onFinal?: Function;
  onLoad?: Function;
}
const TypeTermWidget = ({
  channelId,
  articleId,
  mode = "read",
  onArticleChange,
}: IWidget) => {
  const [articleData, setArticleData] = useState<IArticleDataResponse>();
  const [articleHtml, setArticleHtml] = useState<string[]>(["<span />"]);
  const [loading, setLoading] = useState(false);
  const [errorCode, setErrorCode] = useState<number>();

  const channels = channelId?.split("_");

  useEffect(() => {
    if (typeof articleId === "undefined") {
      return;
    }
    const queryMode = mode === "edit" || mode === "wbw" ? "edit" : "read";
    let url = "";
    url = `/v2/terms/${articleId}?mode=${queryMode}`;
    url += channelId ? `&channel=${channelId}` : "";

    console.log("article url", url);
    setLoading(true);
    console.log("url", url);
    get<ITermResponse>(url)
      .then((json) => {
        if (json.ok) {
          setArticleData({
            uid: json.data.guid,
            title: json.data.meaning,
            subtitle: json.data.word,
            summary: json.data.note,
            content: json.data.note ? json.data.note : "",
            content_type: "markdown",
            html: json.data.html,
            path: [],
            status: 30,
            lang: json.data.language,
            created_at: json.data.created_at,
            updated_at: json.data.updated_at,
          });
          if (json.data.html) {
            setArticleHtml([json.data.html]);
          } else if (json.data.note) {
            setArticleHtml([json.data.note]);
          }
        } else {
          message.error(json.message);
        }
      })
      .finally(() => {
        setLoading(false);
      })
      .catch((e) => {
        console.error(e);
        setErrorCode(e);
      });
  }, [articleId, channelId, mode]);

  return (
    <div>
      {loading ? (
        <ArticleSkeleton />
      ) : errorCode ? (
        <ErrorResult code={errorCode} />
      ) : (
        <>
          <ArticleView
            id={articleData?.uid}
            title={articleData?.title}
            subTitle={articleData?.subtitle}
            summary={articleData?.summary}
            content={articleData ? articleData.content : ""}
            html={articleHtml}
            path={articleData?.path}
            created_at={articleData?.created_at}
            updated_at={articleData?.updated_at}
            channels={channels}
            type={"term"}
            articleId={articleId}
          />
        </>
      )}
    </div>
  );
};

export default TypeTermWidget;
