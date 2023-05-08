import { IStudio } from "../auth/StudioName";
import { IUser } from "../auth/User";
import { IUserRequest, TRole } from "./Auth";

export interface ICourseListApiResponse {
  article: string;
  title: string;
  level: string;
  children: number;
}

export interface ICourseDataRequest {
  id?: string; //课程ID
  title: string; //标题
  subtitle?: string; //副标题
  summary?: string; //副标题
  content?: string;
  cover?: string; //封面图片文件名
  teacher_id?: string; //UserID
  publicity: number; //类型-公开/内部
  anthology_id?: string; //文集ID
  channel_id?: string; //标准答案channel
  start_at?: string; //课程开始时间
  end_at?: string; //课程结束时间
  join: string;
  request_exp: string;
}
export type TCourseJoinMode = "invite" | "manual" | "open";
export type TCourseExpRequest = "none" | "begin-end" | "daily";
export interface ICourseDataResponse {
  id: string; //课程ID
  title: string; //标题
  subtitle: string; //副标题
  summary?: string; //副标题
  teacher?: IUser; //UserID
  course_count?: number; //课程数
  publicity: number; //类型-公开/内部
  anthology_id?: string; //文集ID
  anthology_title?: string; //文集标题
  anthology_owner?: IStudio; //文集拥有者
  channel_id: string; //标准答案ID
  channel_name?: string; //文集标题
  channel_owner?: IStudio; //文集拥有者
  start_at: string; //课程开始时间
  end_at: string; //课程结束时间
  content: string; //简介
  cover: string; //封面图片文件名
  member_count: number;
  join: TCourseJoinMode;
  request_exp: TCourseExpRequest;
  my_status: TCourseMemberStatus;
  count_progressing?: number;
  created_at: string; //创建时间
  updated_at: string; //修改时间
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
export interface ICourseNumberResponse {
  ok: boolean;
  message: string;
  data: {
    create: number;
    teach: number;
    study: number;
  };
}

export type TCourseMemberStatus =
  | "normal" /*开放课程直接加入*/
  | "invited" /**管理员已经邀请学生加入 */
  | "sign_up" /**学生已经报名 管理员尚未审核 */
  | "accepted" /**已经接受 */
  | "rejected" /**已经拒绝 */
  | "left" /**学生自己退出 */
  | "blocked"; /**学生被管理员屏蔽 */
export interface ICourseMemberData {
  id?: string;
  user_id: string;
  course_id: string;
  channel_id?: string;
  role?: string;
  operating?: "invite" | "sign_up";
  user?: IUserRequest;
  status?: TCourseMemberStatus;
  created_at?: string;
  updated_at?: string;
}
export interface ICourseMemberResponse {
  ok: boolean;
  message: string;
  data: ICourseMemberData;
}
export interface ICourseMemberListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: ICourseMemberData[];
    role: TRole;
    count: number;
  };
}

export interface ICourseMemberDeleteResponse {
  ok: boolean;
  message: string;
  data: boolean;
}

export interface ICourseCurrUserResponse {
  ok: boolean;
  message: string;
  data: {
    role: string;
    channel_id: string;
  };
}

export interface IExerciseListData {
  user: IUser;
  wbw: number;
  translation: number;
  question: number;
  html: string;
}
export interface ICourseExerciseResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IExerciseListData[];
    count: number;
  };
}
