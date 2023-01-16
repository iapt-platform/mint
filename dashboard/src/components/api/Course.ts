import { ITocPathNode } from "../corpus/TocPath";
import type { IStudioApiResponse } from "./Auth";

export interface ICourseListApiResponse {
  article: string;
  title: string;
  level: string;
  children: number;
}

export interface ICourseDataRequest {
  uid: string;//课程ID
  title: string;//标题
  subtitle: string;//副标题
  teacher: number;//UserID
  course_count: number;//课程数
  //content: string;
  //content_type: string;
  //path?: ITocPathNode[];
  type: number;//类型-公开/内部
  //lang: string;
  created_at: string;//创建时间
  updated_at: string;//修改时间
  article_id: number;//文集ID
  course_start_at: string;//课程开始时间
  course_end_at: string;//课程结束时间
  intro_markdown: string;//简介
  cover_img_name: string;//封面图片文件名
}
export interface ICourseDataResponse {
  uid: string;//课程ID
  title: string;//标题
  subtitle: string;//副标题
  teacher: number;//UserID
  course_count: number;//课程数
  //content: string;
  //content_type: string;
  //path?: ITocPathNode[];
  type: number;//类型-公开/内部
  //lang: string;
  created_at: string;//创建时间
  updated_at: string;//修改时间
  article_id: number;//文集ID
  course_start_at: string;//课程开始时间
  course_end_at: string;//课程结束时间
  intro_markdown: string;//简介
  cover_img_name: string;//封面图片文件名
}
export interface ICourseResponse {
  ok: boolean;
  message: string;
  data: ICourseDataResponse;
}
export interface ICourseListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: ICourseDataResponse[];
    count: number;
  };
}

export interface ICourseCreateRequest {
  title: string;
  lang: string;
  studio: string;
}

export interface IAnthologyCreateRequest {
  title: string;
  lang: string;
  studio: string;
}
