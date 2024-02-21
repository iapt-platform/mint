import { useEffect, useState } from "react";

import { get } from "../../request";
import store from "../../store";
import { IArticleDataResponse } from "../api/Article";
import {
  ICourseCurrUserResponse,
  ICourseDataResponse,
  ICourseMemberListResponse,
  ICourseResponse,
  ICourseUser,
} from "../api/Course";
import { signIn } from "../../reducers/course-user";
import {
  ITextbook,
  memberRefresh,
  refresh,
} from "../../reducers/current-course";

import "./article.css";

import { ArticleMode, ArticleType } from "./Article";
import TypeArticle from "./TypeArticle";
import { useSearchParams } from "react-router-dom";

import SelectChannel from "../course/SelectChannel";

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
  courseId?: string | null;
  exerciseId?: string;
  userName?: string;
  active?: boolean;
  onArticleChange?: Function;
  onFinal?: Function;
  onLoad?: Function;
  onLoading?: Function;
  onError?: Function;
}
const TypeCourseWidget = ({
  type,
  book,
  para,
  channelId,
  articleId,
  courseId,
  exerciseId,
  userName,
  mode = "read",
  active = false,
  onArticleChange,
  onFinal,
  onLoad,
  onLoading,
  onError,
}: IWidget) => {
  const [anthologyId, setAnthologyId] = useState<string>();
  const [course, setCourse] = useState<ICourseDataResponse>();
  const [currUser, setCurrUser] = useState<ICourseUser>();
  const [searchParams, setSearchParams] = useSearchParams();
  const [channelPickerOpen, setChannelPickerOpen] = useState(false);

  const channels = channelId?.split("_");

  useEffect(() => {
    /**
     * 由课本进入查询当前用户的权限和channel
     */
    console.debug(
      "course 由课本进入查询当前用户的权限和channel",
      type,
      courseId
    );
    if (type === "textbook") {
      if (typeof courseId !== "undefined") {
        const url = `/v2/course-curr?course_id=${courseId}`;
        console.debug("course url", url);
        get<ICourseCurrUserResponse>(url).then((response) => {
          console.log("course user", response);
          if (response.ok) {
            setCurrUser(response.data);
            if (!response.data.channel_id) {
              setChannelPickerOpen(true);
            }
            //我的角色
            store.dispatch(
              signIn({
                channelId: response.data.channel_id,
                role: response.data.role,
              })
            );

            //如果是老师查询学生列表
            if (response.data.role) {
              if (response.data.role !== "student") {
                const url = `/v2/course-member?view=course&id=${courseId}`;
                console.debug("course member url", url);
                get<ICourseMemberListResponse>(url)
                  .then((json) => {
                    console.debug("course member data", json);
                    if (json.ok) {
                      store.dispatch(memberRefresh(json.data.rows));
                    }
                  })
                  .catch((e) => console.error(e));
              }
            }
          } else {
            console.error(response.message);
          }
        });
      }
    }
  }, [courseId, type]);

  useEffect(() => {
    let output: any = { mode: mode };
    searchParams.forEach((value, key) => {
      console.log(value, key);
      if (key !== "mode" && key !== "channel") {
        output[key] = value;
      }
    });
    if (currUser?.role === "student") {
      if (typeof currUser.channel_id === "string") {
        output["channel"] = currUser.channel_id;
      }
    } else {
      output["channel"] = course?.channel_id;
    }

    setSearchParams(output);
  }, [currUser, course, mode]);

  useEffect(() => {
    const url = `/v2/course/${courseId}`;
    console.debug("course url", url);
    get<ICourseResponse>(url).then((json) => {
      console.debug("course data", json.data);
      if (json.ok) {
        setAnthologyId(json.data.anthology_id);
        setCourse(json.data);
        /**
         * redux发布课程信息
         */
        if (courseId && articleId) {
          const ic: ITextbook = {
            courseId: courseId,
            articleId: articleId,
            channelId: json.data.channel_id,
          };
          store.dispatch(refresh(ic));
        }
      }
    });
  }, [articleId, courseId]);

  let channelsId = "";
  if (currUser && course) {
    if (currUser.role === "student") {
      if (currUser.channel_id) {
        channelsId = currUser.channel_id + "_" + course?.channel_id;
      } else {
        channelsId = course?.channel_id;
      }
    } else {
      channelsId = course?.channel_id;
    }
  }

  return anthologyId && currUser ? (
    <>
      {!currUser.channel_id ? (
        <SelectChannel
          courseId={courseId}
          open={channelPickerOpen}
          onOpenChange={(visible: boolean) => {
            setChannelPickerOpen(visible);
          }}
          onSelected={() => {
            window.location.reload();
          }}
        />
      ) : (
        <></>
      )}
      <TypeArticle
        type={"article"}
        articleId={articleId}
        channelId={channelsId}
        mode={mode}
        anthologyId={anthologyId}
        active={true}
        onArticleChange={(type: ArticleType, id: string, target: string) => {
          if (typeof onArticleChange !== "undefined") {
            onArticleChange(type, id, target);
          }
        }}
        onLoad={(data: IArticleDataResponse) => {}}
        onAnthologySelect={(id: string) => {}}
      />
    </>
  ) : (
    <>loading</>
  );
  /*
  const srcDataMode = mode === "edit" || mode === "wbw" ? "edit" : "read";
  useEffect(() => {
    console.log("srcDataMode", srcDataMode);
    if (!active) {
      return;
    }

    if (typeof type !== "undefined") {
      let url = "";
      switch (type) {
        case "textbook":
          if (typeof articleId !== "undefined") {
            url = `/v2/article/${articleId}?view=textbook&course=${courseId}&mode=${srcDataMode}`;
          }
          break;

        case "exercise":
          if (typeof articleId !== "undefined") {
            url = `/v2/article/${articleId}?mode=${srcDataMode}&course=${courseId}&exercise=${exerciseId}&user=${userName}`;
            setExtra(
              <ExerciseAnswer
                courseId={courseId}
                articleId={articleId}
                exerciseId={exerciseId}
              />
            );
          }
          break;
        case "exercise-list":
          if (typeof articleId !== "undefined") {
            url = `/v2/article/${articleId}?mode=${srcDataMode}&course=${courseId}&exercise=${exerciseId}`;

            setExtra(
              <ExerciseList
                courseId={courseId}
                articleId={articleId}
                exerciseId={exerciseId}
              />
            );
          }
          break;

      }

      console.log("url", url);
      if (typeof onLoading !== "undefined") {
        onLoading(true);
      }

      console.log("url", url);

      get<IArticleResponse>(url)
        .then((json) => {
          console.log("article", json);
          if (json.ok) {
            setArticleData(json.data);
            if (json.data.html) {
              setArticleHtml([json.data.html]);
            } else if (json.data.content) {
              setArticleHtml([json.data.content]);
            }
            setExtra(
              <TocTree
                treeData={json.data.toc?.map((item) => {
                  const strTitle = item.title ? item.title : item.pali_title;
                  const key = item.key
                    ? item.key
                    : `${item.book}-${item.paragraph}`;
                  const progress = item.progress?.map((item, id) => (
                    <Tag key={id}>{Math.round(item * 100) + "%"}</Tag>
                  ));
                  return {
                    key: key,
                    title: (
                      <Space>
                        <PaliText
                          text={strTitle === "" ? "[unnamed]" : strTitle}
                        />
                        {progress}
                      </Space>
                    ),
                    level: item.level,
                  };
                })}
                onSelect={(keys: string[]) => {
                  console.log(keys);
                  if (
                    typeof onArticleChange !== "undefined" &&
                    keys.length > 0
                  ) {
                    onArticleChange(keys[0]);
                  }
                }}
              />
            );

            if (typeof onLoad !== "undefined") {
              onLoad(json.data);
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
        .catch((e) => {
          console.error(e);
        });
    }
  }, [
    active,
    type,
    articleId,
    srcDataMode,
    channelId,
    courseId,
    exerciseId,
    userName,
  ]);
*/
  /*
  return (
    <div>
      <ArticleView
        id={articleData?.uid}
        title={
          articleData?.title_text ? articleData?.title_text : articleData?.title
        }
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
        onPathChange={(
          node: ITocPathNode,
          e: React.MouseEvent<HTMLSpanElement | HTMLAnchorElement, MouseEvent>
        ) => {
          let newType = type;
          if (typeof onArticleChange !== "undefined") {
            const newArticleId = node.key
              ? node.key
              : `${node.book}-${node.paragraph}`;
            const target = e.ctrlKey || e.metaKey ? "_blank" : "self";
            onArticleChange(newType, newArticleId, target);
          }
        }}
      />
      <Divider />
      {extra}
      <Divider />
    </div>
  );
  */
};

export default TypeCourseWidget;
