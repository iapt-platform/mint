import { message } from "antd";
import { useEffect, useState } from "react";
import { get } from "../../request";
import { IArticleDataResponse, IArticleResponse } from "../api/Article";
import ArticleView from "./ArticleView";

export type ArticleMode = "read" | "edit";
export type ArticleType =
  | "article"
  | "chapter"
  | "paragraph"
  | "cs-paragraph"
  | "sentence"
  | "sim"
  | "page";
interface IWidgetArticle {
  type?: string;
  articleId?: string;
  mode?: ArticleMode;
  active?: boolean;
}
const Widget = ({
  type,
  articleId,
  mode = "read",
  active = false,
}: IWidgetArticle) => {
  const [articleData, setArticleData] = useState<IArticleDataResponse>();
  let channels: string[] = [];
  if (typeof articleId !== "undefined") {
    const aId = articleId.split("_");
    if (aId.length > 1) {
      channels = aId.slice(1);
    }
  }

  useEffect(() => {
    if (!active) {
      return;
    }
    if (typeof type !== "undefined" && typeof articleId !== "undefined") {
      get<IArticleResponse>(`/v2/${type}/${articleId}/${mode}`).then((json) => {
        if (json.ok) {
          setArticleData(json.data);
        } else {
          message.error(json.message);
        }
      });
    }
  }, [active, type, articleId, mode]);
  return (
    <ArticleView
      id={articleData?.uid}
      title={articleData?.title}
      subTitle={articleData?.subtitle}
      summary={articleData?.summary}
      content={articleData ? articleData.content : ""}
      path={articleData?.path}
      created_at={articleData?.created_at}
      updated_at={articleData?.updated_at}
      channels={channels}
    />
  );
};

export default Widget;
