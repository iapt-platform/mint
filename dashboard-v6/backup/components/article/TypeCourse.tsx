import { useEffect, useMemo, useState } from "react";
import { get } from "../../request";
import store from "../../store";

import type {
  ICourseCurrUserResponse,
  ICourseDataResponse,
  ICourseMemberListResponse,
  ICourseResponse,
  ICourseUser,
} from "../../api/Course";

import { signIn } from "../../reducers/course-user";
import {
  type ITextbook,
  memberRefresh,
  refresh,
} from "../../reducers/current-course";

import "./article.css";

import type { ArticleMode, ArticleType } from "./Article";
import TypeArticle from "./TypeArticle";
import { Link, useNavigate, useSearchParams } from "react-router";

import SelectChannel from "../course/SelectChannel";
import { Space, Tag, Typography } from "antd";
import { useIntl } from "react-intl";
import type { ISearchParams } from "../../pages/library/article/show";

const { Text } = Typography;

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
  onArticleChange?: (
    type: ArticleType,
    id: string,
    target: string,
    param?: ISearchParams[]
  ) => void;
  onFinal?: () => void;
  onLoad?: () => void;
  onLoading?: (loading: boolean) => void;
  onError?: (msg: string) => void;
}

const TypeCourseWidget = ({
  type,
  channelId,
  articleId,
  courseId,
  mode = "read",
  onArticleChange,
}: IWidget) => {
  const intl = useIntl();
  const navigate = useNavigate();

  const [anthologyId, setAnthologyId] = useState<string>();
  const [course, setCourse] = useState<ICourseDataResponse>();
  const [currUser, setCurrUser] = useState<ICourseUser>();
  const [channelPickerOpen, setChannelPickerOpen] = useState(false);

  const [searchParams, setSearchParams] = useSearchParams();

  /** ---------------- 课程用户信息 ---------------- */
  useEffect(() => {
    if (type !== "textbook" || !courseId) return;

    let ignore = false;

    (async () => {
      const res = await get<ICourseCurrUserResponse>(
        `/v2/course-curr?course_id=${courseId}`
      );

      if (!res.ok || ignore) return;

      setCurrUser(res.data);

      if (!res.data.channel_id) setChannelPickerOpen(true);

      store.dispatch(
        signIn({
          channelId: res.data.channel_id,
          role: res.data.role,
        })
      );

      /** 老师加载成员列表 */
      if (res.data.role && res.data.role !== "student") {
        const list = await get<ICourseMemberListResponse>(
          `/v2/course-member?view=course&id=${courseId}`
        );

        if (list.ok) {
          store.dispatch(memberRefresh(list.data.rows));
        }
      }
    })();

    return () => {
      ignore = true;
    };
  }, [courseId, type]);

  /** ---------------- 同步 URL 参数 ---------------- */
  const paramsString = searchParams.toString();

  useEffect(() => {
    if (!currUser && !course) return;

    const output: Record<string, string> = { mode: mode ?? "read" };

    new URLSearchParams(paramsString).forEach((value, key) => {
      if (key !== "mode" && key !== "channel") {
        output[key] = value;
      }
    });

    if (currUser?.role === "student") {
      if (currUser.channel_id) {
        output.channel = currUser.channel_id;
      }
    } else if (course?.channel_id) {
      output.channel = course.channel_id;
    }

    setSearchParams(output);
  }, [currUser, course, mode, paramsString]);

  /** ---------------- 课程信息 ---------------- */
  useEffect(() => {
    if (!courseId) return;

    let ignore = false;

    (async () => {
      const json = await get<ICourseResponse>(`/v2/course/${courseId}`);

      if (!json.ok || ignore) return;

      setAnthologyId(json.data.anthology_id);
      setCourse(json.data);

      if (articleId) {
        const ic: ITextbook = {
          course: json.data,
          courseId,
          articleId,
          channelId: json.data.channel_id,
        };

        store.dispatch(refresh(ic));
      }
    })();

    return () => {
      ignore = true;
    };
  }, [articleId, courseId]);

  /** ---------------- 计算 channelId ---------------- */
  const channelsId = useMemo(() => {
    if (!currUser || !course) return "";

    if (currUser.role === "student") {
      return currUser.channel_id
        ? `${currUser.channel_id}_${course.channel_id}`
        : (course.channel_id ?? "");
    }

    return course.channel_id ?? "";
  }, [currUser, course]);

  /** ---------------- loading ---------------- */
  if (!anthologyId || !currUser) return <>loading</>;

  return (
    <>
      {currUser.role === "student" && !currUser.channel_id && (
        <SelectChannel
          courseId={courseId}
          open={channelPickerOpen}
          onOpenChange={setChannelPickerOpen}
          onSelected={() => window.location.reload()}
        />
      )}

      <Space>
        <Text>
          课程：
          <Link to={`/course/show/${course?.id}`} target="_blank">
            {course?.title}
          </Link>
        </Text>

        <Tag>
          {intl.formatMessage({
            id: `auth.role.${currUser.role}`,
          })}
        </Tag>
      </Space>

      <TypeArticle
        type="article"
        articleId={articleId}
        channelId={channelsId}
        mode={mode}
        anthologyId={anthologyId}
        active
        onArticleChange={(type: ArticleType, id: string, target: string) => {
          if (type === "article" && courseId && channelId) {
            onArticleChange?.("textbook", id, target, [
              { key: "course", value: courseId },
              { key: "channel", value: channelId },
            ]);
          } else {
            navigate(`/course/show/${courseId}`);
          }
        }}
      />
    </>
  );
};

export default TypeCourseWidget;
