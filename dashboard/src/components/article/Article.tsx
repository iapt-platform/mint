import { useState } from "react";
import { Result } from "antd";

import { IArticleDataResponse } from "../api/Article";

import "./article.css";

import ArticleSkeleton from "./ArticleSkeleton";

import TypeArticle from "./TypeArticle";
import TypeAnthology from "./TypeAnthology";

export type ArticleMode = "read" | "edit" | "wbw";
export type ArticleType =
  | "anthology"
  | "article"
  | "series"
  | "chapter"
  | "para"
  | "cs-para"
  | "sent"
  | "sim"
  | "page"
  | "textbook"
  | "exercise"
  | "exercise-list"
  | "sent-original"
  | "sent-commentary"
  | "sent-nissaya"
  | "sent-translation"
  | "term";
/**
 * 每种article type 对应的路由参数
 * article/id?anthology=id&channel=id1,id2&mode=ArticleMode
 * chapter/book-para?channel=id1,id2&mode=ArticleMode
 * para/book?par=para1,para2&channel=id1,id2&mode=ArticleMode
 * cs-para/book-para?channel=id1,id2&mode=ArticleMode
 * sent/id?channel=id1,id2&mode=ArticleMode
 * sim/id?channel=id1,id2&mode=ArticleMode
 * textbook/articleId?course=id&mode=ArticleMode
 * exercise/articleId?course=id&exercise=id&username=name&mode=ArticleMode
 * exercise-list/articleId?course=id&exercise=id&mode=ArticleMode
 * sent-original/id
 */
interface IWidget {
  type?: ArticleType;
  articleId?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  book?: string | null;
  para?: string | null;
  anthologyId?: string | null;
  courseId?: string;
  exerciseId?: string;
  userName?: string;
  active?: boolean;
  onArticleChange?: Function;
  onFinal?: Function;
  onLoad?: Function;
  onAnthologySelect?: Function;
}
const ArticleWidget = ({
  type,
  book,
  para,
  channelId,
  articleId,
  anthologyId,
  courseId,
  exerciseId,
  userName,
  mode = "read",
  active = false,
  onArticleChange,
  onFinal,
  onLoad,
  onAnthologySelect,
}: IWidget) => {
  const [showSkeleton, setShowSkeleton] = useState(true);
  const [unauthorized, setUnauthorized] = useState(false);

  return (
    <div>
      {showSkeleton ? (
        <ArticleSkeleton />
      ) : unauthorized ? (
        <Result
          status="403"
          title="无权访问"
          subTitle="您无权访问该内容。您可能没有登录，或者内容的所有者没有给您所需的权限。"
          extra={<></>}
        />
      ) : (
        <></>
      )}
      {type === "article" ? (
        <TypeArticle
          type={type}
          articleId={articleId}
          channelId={channelId}
          mode={mode}
          anthologyId={anthologyId}
          active={active}
          onArticleChange={(type: ArticleType, id: string) => {
            if (typeof onArticleChange !== "undefined") {
              onArticleChange(type, id);
            }
          }}
          onLoad={(data: IArticleDataResponse) => {
            if (typeof onLoad !== "undefined") {
              onLoad(data);
            }
          }}
          onLoading={(loading: boolean) => setShowSkeleton(loading)}
          onError={(code: number, message: string) => {}}
          onAnthologySelect={(id: string) => {
            if (typeof onAnthologySelect !== "undefined") {
              onAnthologySelect(id);
            }
          }}
        />
      ) : type === "anthology" ? (
        <TypeAnthology
          articleId={articleId}
          channelId={channelId}
          mode={mode}
          onArticleChange={(type: ArticleType, id: string) => {
            if (typeof onArticleChange !== "undefined") {
              onArticleChange(type, id);
            }
          }}
          onLoading={(loading: boolean) => setShowSkeleton(loading)}
          onError={(code: number, message: string) => {}}
        />
      ) : (
        <></>
      )}
    </div>
  );
};

export default ArticleWidget;
