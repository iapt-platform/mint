import { IArticleDataResponse } from "../api/Article";
import TypeArticle from "./TypeArticle";
import TypeAnthology from "./TypeAnthology";
import TypeTerm from "./TypeTerm";
import TypePali from "./TypePali";
import "./article.css";
import TypePage from "./TypePage";
import TypeCSPara from "./TypeCSPara";

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
  focus?: string | null;
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
  focus,
  onArticleChange,
  onFinal,
  onLoad,
  onAnthologySelect,
}: IWidget) => {
  return (
    <div>
      {type === "article" ? (
        <TypeArticle
          type={type}
          articleId={articleId}
          channelId={channelId}
          mode={mode}
          anthologyId={anthologyId}
          active={active}
          onArticleChange={(type: ArticleType, id: string, target: string) => {
            if (typeof onArticleChange !== "undefined") {
              onArticleChange(type, id, target);
            }
          }}
          onLoad={(data: IArticleDataResponse) => {
            if (typeof onLoad !== "undefined") {
              onLoad(data);
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
          articleId={articleId}
          channelId={channelId}
          mode={mode}
          onArticleChange={(type: ArticleType, id: string, target: string) => {
            if (typeof onArticleChange !== "undefined") {
              onArticleChange(type, id, target);
            }
          }}
        />
      ) : type === "term" ? (
        <TypeTerm
          articleId={articleId}
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
          articleId={articleId}
          channelId={channelId}
          mode={mode}
          book={book}
          para={para}
          focus={focus}
          onArticleChange={(type: ArticleType, id: string, target: string) => {
            if (typeof onArticleChange !== "undefined") {
              onArticleChange(type, id, target);
            }
          }}
        />
      ) : type === "page" ? (
        <TypePage
          articleId={articleId}
          channelId={channelId}
          focus={focus}
          mode={mode}
          onArticleChange={(type: ArticleType, id: string, target: string) => {
            if (typeof onArticleChange !== "undefined") {
              onArticleChange(type, id, target);
            }
          }}
        />
      ) : type === "cs-para" ? (
        <TypeCSPara
          articleId={articleId}
          channelId={channelId}
          mode={mode}
          onArticleChange={(type: ArticleType, id: string, target: string) => {
            if (typeof onArticleChange !== "undefined") {
              onArticleChange(type, id, target);
            }
          }}
        />
      ) : (
        <></>
      )}
    </div>
  );
};

export default ArticleWidget;
