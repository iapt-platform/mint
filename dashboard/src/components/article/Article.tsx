import { IArticleDataResponse } from "../api/Article";
import TypeArticle from "./TypeArticle";
import TypeAnthology from "./TypeAnthology";
import TypeTerm from "./TypeTerm";
import TypePali from "./TypePali";
import "./article.css";
import TypePage from "./TypePage";
import TypeCSPara from "./TypeCSPara";
import { ISearchParams } from "../../pages/library/article/show";
import TypeCourse from "./TypeCourse";
import { useEffect, useState } from "react";
import { fullUrl } from "../../utils";
import TypeSeries from "./TypeSeries";
import DiscussionCount from "../discussion/DiscussionCount";

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
  parentChannels?: string[];
  book?: string | null;
  para?: string | null;
  anthologyId?: string | null;
  courseId?: string | null;
  active?: boolean;
  focus?: string | null;
  hideInteractive?: boolean;
  hideTitle?: boolean;
  isSubWindow?: boolean;
  onArticleChange?: (
    type: ArticleType,
    id: string,
    target: string,
    param?: ISearchParams[]
  ) => void;
  onLoad?: Function;
  onAnthologySelect?: Function;
  onTitle?: Function;
  onArticleEdit?: Function;
}
const ArticleWidget = ({
  type,
  book,
  para,
  channelId,
  parentChannels,
  articleId,
  anthologyId,
  courseId,
  mode = "read",
  active = false,
  focus,
  hideInteractive = false,
  hideTitle = false,
  isSubWindow = false,
  onArticleChange,
  onLoad,
  onAnthologySelect,
  onTitle,
  onArticleEdit,
}: IWidget) => {
  const [currId, setCurrId] = useState(articleId);
  useEffect(() => setCurrId(articleId), [articleId]);

  return (
    <div>
      <DiscussionCount courseId={type === "textbook" ? courseId : undefined} />
      {type === "article" ? (
        <TypeArticle
          isSubWindow={isSubWindow}
          type={type}
          articleId={onArticleChange ? articleId : currId}
          channelId={channelId}
          parentChannels={parentChannels}
          mode={mode}
          anthologyId={anthologyId}
          active={active}
          hideInteractive={hideInteractive}
          hideTitle={hideTitle}
          onArticleEdit={(value: IArticleDataResponse) => {
            if (typeof onArticleEdit !== "undefined") {
              onArticleEdit(value);
            }
          }}
          onArticleChange={onArticleChange}
          onLoad={(data: IArticleDataResponse) => {
            if (typeof onLoad !== "undefined") {
              onLoad(data);
            }
            if (typeof onTitle !== "undefined") {
              onTitle(data.title);
            }
          }}
          onAnthologySelect={(id: string) => {
            if (typeof onAnthologySelect !== "undefined") {
              onAnthologySelect(id);
            }
          }}
        />
      ) : type === "anthology" ? (
        <TypeAnthology
          type={type}
          articleId={onArticleChange ? articleId : currId}
          channelId={channelId}
          mode={mode}
          onArticleChange={(type: ArticleType, id: string, target: string) => {
            if (typeof onArticleChange !== "undefined") {
              onArticleChange(type, id, target);
            }
          }}
          onTitle={(value: string) => {
            if (typeof onTitle !== "undefined") {
              onTitle(value);
            }
          }}
        />
      ) : type === "term" ? (
        <TypeTerm
          articleId={onArticleChange ? articleId : currId}
          channelId={channelId}
          mode={mode}
          onArticleChange={(type: ArticleType, id: string, target: string) => {
            if (typeof onArticleChange !== "undefined") {
              onArticleChange(type, id, target);
            }
          }}
        />
      ) : type === "chapter" || type === "para" ? (
        <TypePali
          type={type}
          articleId={onArticleChange ? articleId : currId}
          channelId={channelId}
          mode={mode}
          book={book}
          para={para}
          focus={focus}
          onArticleChange={(
            type: ArticleType,
            id: string,
            target: string,
            param?: ISearchParams[]
          ) => {
            if (typeof onArticleChange !== "undefined") {
              onArticleChange(type, id, target, param);
            }
          }}
          onLoad={(data: IArticleDataResponse) => {
            if (typeof onLoad !== "undefined") {
              onLoad(data);
            }
          }}
          onTitle={(value: string) => {
            if (typeof onTitle !== "undefined") {
              onTitle(value);
            }
          }}
        />
      ) : type === "series" ? (
        <TypeSeries
          articleId={onArticleChange ? articleId : currId}
          channelId={channelId}
          onArticleChange={(
            type: ArticleType,
            id: string,
            target: string,
            param: ISearchParams[]
          ) => {
            if (typeof onArticleChange !== "undefined") {
              onArticleChange(type, id, target, param);
            }
          }}
        />
      ) : type === "page" ? (
        <TypePage
          articleId={onArticleChange ? articleId : currId}
          channelId={channelId}
          focus={focus}
          mode={mode}
          onArticleChange={(type: ArticleType, id: string, target: string) => {
            if (typeof onArticleChange !== "undefined") {
              onArticleChange(type, id, target);
            } else {
              if (target === "_blank") {
                let url = `/article/page/${id}?mode=${mode}`;
                if (channelId) {
                  url += `&channel=${channelId}`;
                }
                window.open(fullUrl(url), "_blank");
              } else {
                setCurrId(id);
              }
            }
          }}
        />
      ) : type === "cs-para" ? (
        <TypeCSPara
          articleId={onArticleChange ? articleId : currId}
          channelId={channelId}
          mode={mode}
          onArticleChange={(type: ArticleType, id: string, target: string) => {
            if (typeof onArticleChange !== "undefined") {
              onArticleChange(type, id, target);
            }
          }}
        />
      ) : type === "textbook" ? (
        <TypeCourse
          type={type}
          articleId={onArticleChange ? articleId : currId}
          channelId={channelId}
          courseId={courseId}
          mode={mode}
          onArticleChange={onArticleChange}
        />
      ) : (
        <></>
      )}
    </div>
  );
};

export default ArticleWidget;
