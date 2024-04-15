//课程详情页面
import { useParams } from "react-router-dom";
import { useEffect, useState } from "react";
import { Divider, message } from "antd";

import CourseIntro from "../../../components/course/CourseIntro";
import TextBook from "../../../components/course/TextBook";
import { IUser } from "../../../components/auth/User";
import { get } from "../../../request";
import {
  ICourseResponse,
  TCourseExpRequest,
  TCourseJoinMode,
} from "../../../components/api/Course";
import CourseHead from "../../../components/course/CourseHead";
import ArticleSkeleton from "../../../components/article/ArticleSkeleton";
import ErrorResult from "../../../components/general/ErrorResult";

export interface ICourse {
  id: string; //课程ID
  title: string; //标题
  subtitle?: string; //副标题
  summary?: string;
  teacher?: IUser; //UserID
  privacy?: number; //公开性-公开/内部
  createdAt?: string; //创建时间
  updatedAt?: string; //修改时间
  anthologyId?: string; //文集ID
  channelId?: string;
  startAt?: string; //课程开始时间
  endAt?: string; //课程结束时间
  signUpStartAt?: string; //报名开始时间
  signUpEndAt?: string; //报名结束时间
  intro?: string; //简介
  coverUrl?: string[]; //封面图片文件名
  join?: TCourseJoinMode;
  exp?: TCourseExpRequest;
}
const Widget = () => {
  const { id } = useParams(); //url 参数
  const [courseInfo, setCourseInfo] = useState<ICourse>();
  const [loading, setLoading] = useState(false);
  const [errorCode, setErrorCode] = useState<number>();

  useEffect(() => {
    const url = `/v2/course/${id}`;
    console.info("course url", url);
    setLoading(true);
    get<ICourseResponse>(url)
      .then((json) => {
        if (json.ok) {
          console.log("api response", json.data);
          const course: ICourse = {
            id: json.data.id,
            title: json.data.title,
            subtitle: json.data.subtitle,
            teacher: json.data.teacher,
            privacy: json.data.publicity,
            createdAt: json.data.created_at,
            updatedAt: json.data.updated_at,
            anthologyId: json.data.anthology_id,
            channelId: json.data.channel_id,
            startAt: json.data.start_at,
            endAt: json.data.end_at,
            signUpStartAt: json.data.sign_up_start_at,
            signUpEndAt: json.data.sign_up_end_at,
            intro: json.data.content,
            coverUrl: json.data.cover_url,
            join: json.data.join,
            exp: json.data.request_exp,
          };
          setCourseInfo(course);
        } else {
          message.error(json.message);
        }
      })
      .finally(() => setLoading(false))
      .catch((e) => {
        console.error(e);
        setErrorCode(e);
      });
  }, [id]);
  return (
    <>
      {loading ? (
        <ArticleSkeleton />
      ) : errorCode ? (
        <ErrorResult code={errorCode} />
      ) : (
        <div>
          <CourseHead {...courseInfo} />
          <Divider />
          <CourseIntro {...courseInfo} />
          <Divider />
          <TextBook
            anthologyId={courseInfo?.anthologyId}
            courseId={courseInfo?.id}
          />
        </div>
      )}
    </>
  );
};

export default Widget;
