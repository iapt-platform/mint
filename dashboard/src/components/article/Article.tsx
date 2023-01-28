import { useEffect, useState } from "react";
import { message } from "antd";

import { modeChange } from "../../reducers/article-mode";
import { get } from "../../request";
import store from "../../store";
import { IArticleDataResponse, IArticleResponse } from "../api/Article";
import ArticleView from "./ArticleView";

export type ArticleMode = "read" | "edit" | "wbw";
export type ArticleType =
  | "article"
  | "chapter"
  | "paragraph"
  | "cs-para"
  | "sent"
  | "sim"
  | "page"
  | "textbook";
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
  const [articleMode, setArticleMode] = useState<ArticleMode>(mode);

  let channels: string[] = [];
  if (typeof articleId !== "undefined") {
    const aId = articleId.split("_");
    if (aId.length > 1) {
      channels = aId.slice(1);
    }
  }

  useEffect(() => {
    console.log("mode", mode, articleMode);
    if (!active) {
      return;
    }
    setArticleMode(mode);
    //发布mode变更
    store.dispatch(modeChange(mode));

    if (mode !== articleMode && mode !== "read" && articleMode !== "read") {
      console.log("set mode", mode, articleMode);
      return;
    }

    if (typeof type !== "undefined" && typeof articleId !== "undefined") {
      let url = "";
      switch (type) {
        case "article":
          url = `/v2/article/${articleId}?mode=${mode}`;
          break;
        case "textbook":
          /**
           * 从课本进入
           * id两部分组成
           * 课程id_文章id
           */
          const id = articleId.split("_");
          if (id.length < 2) {
            message.error("文章id期待2个，实际只给了一个");
            return;
          }
          url = `/v2/article/${id[1]}?mode=${mode}&course=${id[0]}`;
          break;
        case "exercise":
          /**
           * 从练习进入
           * id 由3部分组成
           * 课程id_文章id_练习id
           */
          const exerciseId = articleId.split("_");
          if (exerciseId.length < 3) {
            message.error("练习id期待3个");
            return;
          }
          url = `/v2/article/${exerciseId[1]}?mode=${mode}&course=${exerciseId[0]}&exercise=${exerciseId[2]}`;
          break;
        case "exercise-list":
          /**
           * 从练习进入
           * id 由3部分组成
           * 课程id_文章id_练习id
           */
          const exerciseListId = articleId.split("_");
          if (exerciseListId.length < 3) {
            message.error("练习id期待3个");
            return;
          }
          url = `/v2/article/${exerciseListId[1]}?mode=${mode}&course=${exerciseListId[0]}&exercise=${exerciseListId[2]}&list=true`;
          break;
        default:
          url = `/v2/corpus/${type}/${articleId}/${mode}`;
          break;
      }
      get<IArticleResponse>(url).then((json) => {
        console.log("article", json);
        if (json.ok) {
          setArticleData(json.data);
        } else {
          message.error(json.message);
        }
      });
    }
  }, [active, type, articleId, mode, articleMode]);

  return (
    <ArticleView
      id={articleData?.uid}
      title={articleData?.title}
      subTitle={articleData?.subtitle}
      summary={articleData?.summary}
      content={articleData ? articleData.content : ""}
      html={articleData?.html}
      path={articleData?.path}
      created_at={articleData?.created_at}
      updated_at={articleData?.updated_at}
      channels={channels}
    />
  );
};

export default Widget;
