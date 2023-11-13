import { useEffect, useState } from "react";

import { get } from "../../request";
import { IArticleDataResponse } from "../api/Article";
import ArticleView from "./ArticleView";
import { ITermResponse } from "../api/Term";
import { ArticleMode, ArticleType } from "./Article";
import "./article.css";
import { message } from "antd";

interface IWidget {
  type?: ArticleType;
  articleId?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  active?: boolean;
  onArticleChange?: Function;
  onFinal?: Function;
  onLoad?: Function;
  onLoading?: Function;
  onError?: Function;
}
const TypeTermWidget = ({
  type,
  channelId,
  articleId,
  mode = "read",
  active = false,
  onArticleChange,
  onLoading,
  onError,
}: IWidget) => {
  const [articleData, setArticleData] = useState<IArticleDataResponse>();
  const [articleHtml, setArticleHtml] = useState<string[]>(["<span />"]);

  const channels = channelId?.split("_");

  useEffect(() => {
    if (!active) {
      return;
    }
    if (typeof articleId === "undefined") {
      return;
    }
    const queryMode = mode === "edit" || mode === "wbw" ? "edit" : "read";
    let url = "";
    url = `/v2/terms/${articleId}?mode=${queryMode}`;
    url += channelId ? `&channel=${channelId}` : "";

    console.log("article url", url);

    if (typeof onLoading !== "undefined") {
      onLoading(true);
    }
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
          if (typeof onError !== "undefined") {
            onError(json.data, json.message);
          }
          message.error(json.message);
        }
      })
      .finally(() => {
        if (typeof onLoading !== "undefined") {
          onLoading(false);
        }
      })
      .catch((error) => {
        console.error(error);
      });
  }, [active, type, articleId, channelId, mode]);

  return (
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
      type={type}
      articleId={articleId}
    />
  );
};

export default TypeTermWidget;
