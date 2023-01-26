//课程详情页面
import { Link, useParams } from "react-router-dom";
import { useEffect, useState } from "react";
import { Breadcrumb, Divider, message } from "antd";

import CourseShow from "../../../components/library/course/CourseShow";
import CourseIntro from "../../../components/library/course/CourseIntro";
import TextBook from "../../../components/course/TextBook";

import { IUser } from "../../../components/auth/User";
import { get } from "../../../request";
import { ICourseResponse } from "../../../components/api/Course";

export interface ICourse {
  id: string; //课程ID
  title: string; //标题
  subtitle?: string; //副标题
  teacher?: IUser; //UserID
  privacy?: number; //公开性-公开/内部
  createdAt?: string; //创建时间
  updatedAt?: string; //修改时间
  anthologyId?: string; //文集ID
  channelId?: string;
  startAt?: string; //课程开始时间
  endAt?: string; //课程结束时间
  intro?: string; //简介
  coverUrl?: string; //封面图片文件名
}
const Widget = () => {
  // TODO
  const { id } = useParams(); //url 参数
  const [courseInfo, setCourseInfo] = useState<ICourse>();
  useEffect(() => {
    get<ICourseResponse>(`/v2/course/${id}`).then((json) => {
      if (json.ok) {
        console.log(json.data);
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
          intro: json.data.content,
          coverUrl: json.data.cover,
        };
        setCourseInfo(course);
      } else {
        message.error(json.message);
      }
    });
  }, [id]);
  return (
    <div>
      <CourseShow {...courseInfo} />
      <Divider />
      <CourseIntro {...courseInfo} />
      <Divider />
      <TextBook
        anthologyId={courseInfo?.anthologyId}
        courseId={courseInfo?.id}
      />
    </div>
  );
};

export default Widget;
