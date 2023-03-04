import { useEffect, useState } from "react";
import { Divider, message, Tag } from "antd";

import { modeChange } from "../../reducers/article-mode";
import { get } from "../../request";
import store from "../../store";
import { IArticleDataResponse, IArticleResponse } from "../api/Article";
import ArticleView from "./ArticleView";
import { ICourseCurrUserResponse } from "../api/Course";
import { ICourseUser, signIn } from "../../reducers/course-user";
import { ITextbook, refresh } from "../../reducers/current-course";
import ExerciseList from "./ExerciseList";
import ExerciseAnswer from "../course/ExerciseAnswer";
import "./article.css";
import CommentListCard from "../comment/CommentListCard";
import TocTree from "./TocTree";
import PaliText from "../template/Wbw/PaliText";
import ArticleSkeleton from "./ArticleSkeleton";

export type ArticleMode = "read" | "edit" | "wbw";
export type ArticleType =
  | "article"
  | "chapter"
  | "paragraph"
  | "cs-para"
  | "sent"
  | "sim"
  | "page"
  | "textbook"
  | "exercise"
  | "exercise-list"
  | "corpus_sent/original"
  | "corpus_sent/commentary"
  | "corpus_sent/nissaya"
  | "corpus_sent/translation";
interface IWidgetArticle {
  type?: ArticleType;
  articleId?: string;
  mode?: ArticleMode;
  active?: boolean;
  onArticleChange?: Function;
  onFinal?: Function;
}
const Widget = ({
  type,
  articleId,
  mode = "read",
  active = false,
  onArticleChange,
  onFinal,
}: IWidgetArticle) => {
  const [articleData, setArticleData] = useState<IArticleDataResponse>();
  const [articleMode, setArticleMode] = useState<ArticleMode>(mode);
  const [extra, setExtra] = useState(<></>);
  const [showSkeleton, setShowSkeleton] = useState(true);

  let channels: string[] = [];
  if (typeof articleId !== "undefined") {
    const aId = articleId.split("_");
    if (aId.length > 1) {
      channels = aId.slice(1);
    }
  }
  useEffect(() => {
    /**
     * 由课本进入查询当前用户的权限和channel
     */
    if (
      type === "textbook" ||
      type === "exercise" ||
      type === "exercise-list"
    ) {
      if (typeof articleId !== "undefined") {
        const id = articleId.split("_");
        get<ICourseCurrUserResponse>(`/v2/course-curr?course_id=${id[0]}`).then(
          (response) => {
            console.log("course user", response);
            if (response.ok) {
              const it: ICourseUser = {
                channelId: response.data.channel_id,
                role: response.data.role,
              };
              store.dispatch(signIn(it));
              /**
               * redux发布课程信息
               */
              const ic: ITextbook = {
                courseId: id[0],
                articleId: id[1],
              };
              store.dispatch(refresh(ic));
            }
          }
        );
      }
    }
  }, [articleId, type]);

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
          const aIds = articleId.split("_");
          url = `/v2/article/${aIds[0]}?mode=${mode}`;
          if (aIds.length > 1) {
            const channels = aIds.slice(1);
            url += "&channel=" + channels.join();
          }
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
          url = `/v2/article/${id[1]}?mode=${mode}&view=textbook&course=${id[0]}`;
          break;
        case "exercise":
          /**
           * 从练习进入
           * id 由4部分组成
           * 课程id_文章id_练习id_username
           */
          const exerciseId = articleId.split("_");
          if (exerciseId.length < 3) {
            message.error("练习id期待3个");
            return;
          }
          console.log("exe", exerciseId);
          url = `/v2/article/${exerciseId[1]}?mode=${mode}&course=${exerciseId[0]}&exercise=${exerciseId[2]}&user=${exerciseId[3]}`;

          setExtra(
            <ExerciseAnswer
              courseId={exerciseId[0]}
              articleId={exerciseId[1]}
              exerciseId={exerciseId[2]}
            />
          );
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
          url = `/v2/article/${exerciseListId[1]}?mode=${mode}&course=${exerciseListId[0]}&exercise=${exerciseListId[2]}`;

          //url = `/v2/article/${exerciseListId[1]}?mode=${mode}&course=${exerciseListId[0]}&exercise=${exerciseListId[2]}&list=true`;
          setExtra(
            <ExerciseList
              courseId={exerciseListId[0]}
              articleId={exerciseListId[1]}
              exerciseId={exerciseListId[2]}
            />
          );
          break;
        default:
          const aid = articleId.split("_");

          url = `/v2/corpus/${type}/${articleId}/${mode}?mode=${mode}`;
          if (aid.length > 0) {
            const channels = aid.slice(1).join();
            url += `&channels=${channels}`;
          }
          break;
      }
      console.log("url", url);
      setShowSkeleton(true);
      get<IArticleResponse>(url).then((json) => {
        console.log("article", json);
        if (json.ok) {
          setArticleData(json.data);
          setShowSkeleton(false);

          setExtra(
            <TocTree
              treeData={json.data.toc?.map((item) => {
                const strTitle = item.title ? item.title : item.pali_title;
                const progress = item.progress?.map((item, id) => (
                  <Tag key={id}>{Math.round(item * 100)}</Tag>
                ));

                return {
                  key: `${item.book}-${item.paragraph}`,
                  title: (
                    <>
                      <PaliText text={strTitle} />
                      {progress}
                    </>
                  ),
                  level: item.level,
                };
              })}
              onSelect={(keys: string[]) => {
                console.log(keys);
                if (typeof onArticleChange !== "undefined" && keys.length > 0) {
                  const aid = articleId.split("_");
                  const channels =
                    aid.length > 1 ? "_" + aid.slice(1).join("_") : undefined;
                  onArticleChange(keys[0] + channels);
                }
              }}
            />
          );
        } else {
          message.error(json.message);
        }
      });
    }
  }, [active, type, articleId, mode, articleMode]);

  return (
    <div>
      {showSkeleton ? (
        <ArticleSkeleton />
      ) : (
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
          type={type}
          articleId={articleId}
        />
      )}

      {extra}
      <Divider />
      <CommentListCard resId={articleData?.uid} resType="article" />
    </div>
  );
};

export default Widget;
