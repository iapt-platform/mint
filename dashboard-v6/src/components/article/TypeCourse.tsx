import { useEffect, useState } from "react";

import { get } from "../../request";
import store from "../../store";

import { signIn } from "../../reducers/course-user";
import {
  memberRefresh,
  refresh,
  type ITextbook,
} from "../../reducers/current-course";

import "./article.css";

import TypeArticle from "./TypeArticle";
import { Link, useNavigate, useSearchParams } from "react-router";

import SelectChannel from "../course/SelectChannel";
import { Space, Tag, Typography } from "antd";
import { useIntl } from "react-intl";
import type { ArticleMode, ArticleType } from "../../api/article";
import type { ISearchParams } from "./TypePali";
import {
  fetchCourse,
  type ICourseCurrUserResponse,
  type ICourseDataResponse,
  type ICourseMemberListResponse,
  type ICourseUser,
} from "../../api/course";
import type { TTarget } from "../../types";

const { Text } = Typography;

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
  articleId?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  headerExtra?: React.ReactNode;
  courseId?: string | null;
  userName?: string;
  onArticleChange?: (
    type: ArticleType,
    id: string,
    target?: TTarget,
    param?: ISearchParams[]
  ) => void;
}
const TypeCourseWidget = ({
  channelId,
  articleId,
  courseId,
  headerExtra,
  mode = "read",
  onArticleChange,
}: IWidget) => {
  const intl = useIntl();
  const [anthologyId, setAnthologyId] = useState<string>();
  const [course, setCourse] = useState<ICourseDataResponse>();
  const [currUser, setCurrUser] = useState<ICourseUser>();
  const [searchParams, setSearchParams] = useSearchParams();
  const [channelPickerOpen, setChannelPickerOpen] = useState(false);
  const navigate = useNavigate();

  useEffect(() => {
    /**
     * 由课本进入查询当前用户的权限和channel
     */
    console.debug("course 由课本进入查询当前用户的权限和channel", courseId);
    if (typeof courseId !== "undefined") {
      const url = `/api/v2/course-curr?course_id=${courseId}`;
      console.info("course url", url);
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
              const url = `/api/v2/course-member?view=course&id=${courseId}`;
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
  }, [courseId]);

  useEffect(() => {
    const output: Record<string, string> = { mode: mode as string };
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
    } else if (course?.channel_id) {
      output["channel"] = course?.channel_id;
    }

    setSearchParams(output);
  }, [currUser, course, mode, searchParams, setSearchParams]);

  useEffect(() => {
    if (!courseId) {
      return;
    }
    fetchCourse(courseId).then((json) => {
      console.debug("course data", json.data);
      if (json.ok) {
        setAnthologyId(json.data.anthology_id);
        setCourse(json.data);
        /**
         * redux发布课程信息
         */
        if (courseId && articleId) {
          const ic: ITextbook = {
            course: json.data,
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
      {!currUser.channel_id && currUser.role === "student" ? (
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
      {headerExtra}
      <Space>
        <Text>
          {"课程："}
          <Link to={`/course/show/${course?.id}`} target="_blank">
            {course?.title}
          </Link>
        </Text>
        <Tag>{intl.formatMessage({ id: `auth.role.${currUser.role}` })}</Tag>
      </Space>
      <TypeArticle
        articleId={articleId}
        channelId={channelsId}
        mode={mode}
        anthologyId={anthologyId}
        active={true}
        onArticleChange={(type: ArticleType, id: string, target?: TTarget) => {
          if (type === "article" && courseId && channelId) {
            if (typeof onArticleChange !== "undefined") {
              const param: ISearchParams[] = [
                { key: "course", value: courseId },
                { key: "channel", value: channelId },
              ];

              onArticleChange("textbook", id, target ?? "_blank", param);
            }
          } else {
            navigate(`/course/show/${courseId}`);
          }
        }}
      />
    </>
  ) : (
    <>loading</>
  );
};

export default TypeCourseWidget;
