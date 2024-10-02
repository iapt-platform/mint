import { IStudio } from "../auth/Studio";
import { IUser } from "../auth/User";
import { IChannel } from "../channel/Channel";
import { TRole } from "./Auth";

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
  content?: string | null;
  sign_up_message?: string | null;
  cover?: string; //封面图片文件名
  teacher_id?: string; //UserID
  publicity: number; //类型-公开/内部
  anthology_id?: string; //文集ID
  channel_id?: string; //标准答案channel
  start_at?: string; //课程开始时间
  end_at?: string; //课程结束时间
  sign_up_start_at: string | null; //报名开始时间
  sign_up_end_at: string | null; //报名结束时间
  join: string;
  request_exp: string;
  number: number;
}
export type TCourseRole =
  | "owner"
  | "teacher"
  | "manager"
  | "assistant"
  | "student";
export type TCourseJoinMode = "invite" | "manual" | "open";
export type TCourseExpRequest = "none" | "begin-end" | "daily";

export interface IMember {
  role: TCourseRole;
  status: TCourseMemberStatus;
}
export interface ICourseDataResponse {
  id: string; //课程ID
  title: string; //标题
  subtitle: string; //副标题
  summary?: string; //副标题
  sign_up_message?: string | null; //报名弹窗消息
  teacher?: IUser; //UserID
  course_count?: number; //课程数
  publicity: number; //类型-公开/内部
  anthology_id?: string; //文集ID
  anthology_title?: string; //文集标题
  anthology_owner?: IStudio; //文集拥有者
  channel_id: string; //标准答案ID
  channel_name?: string; //文集标题
  channel_owner?: IStudio; //文集拥有者
  studio?: IStudio; //课程拥有者
  start_at: string; //课程开始时间
  end_at: string; //课程结束时间
  sign_up_start_at: string; //报名开始时间
  sign_up_end_at: string; //报名结束时间
  content: string; //简介
  cover: string; //封面图片文件名
  cover_url?: string[]; //封面图片文件名
  member_count: number;
  join: TCourseJoinMode; //报名方式
  request_exp: TCourseExpRequest;
  my_status?: TCourseMemberStatus;
  my_status_id?: string;
  count_progressing?: number;
  number: number;
  members?: IMember[];
  my_role?: TCourseRole;
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
  | "none" /*无*/
  | "normal" /*开放课程直接加入*/
  | "joined" /*开放课程已经加入*/
  | "applied" /**学生已经报名 管理员尚未审核 */
  | "canceled" /**学生取消报名 */
  | "agreed" /**学生/助教已经接受邀请 */
  | "disagreed" /**学生/助教已经拒绝邀请 */
  | "left" /**学生自己退出 */
  | "invited" /**管理员已经邀请学生加入 */
  | "revoked" /**管理员撤销邀请 */
  | "accepted" /**已经被管理员录取 */
  | "rejected" /**报名已经被管理员拒绝 */
  | "blocked"; /**被管理员清退 */

export type TCourseMemberAction =
  | "join" /*加入自学课程*/
  | "apply" /**学生报名 */
  | "cancel" /**学生取消报名 */
  | "agree" /**学生/助教接受邀请 */
  | "disagree" /**学生/助教拒绝邀请 */
  | "leave" /**学生/助教自己退出 */
  | "invite" /**管理员邀请学生加入 */
  | "revoke" /**管理员撤销邀请 */
  | "accept" /**管理员录取 */
  | "reject" /**管理员拒绝 */
  | "block"; /**管理员清退 */

interface IActionMap {
  action: TCourseMemberAction;
  status: TCourseMemberStatus;
}
export const actionMap = (action: TCourseMemberAction) => {
  const data: IActionMap[] = [
    { action: "join", status: "joined" },
    { action: "apply", status: "applied" },
    { action: "cancel", status: "canceled" },
    { action: "agree", status: "agreed" },
    { action: "disagree", status: "disagreed" },
    { action: "leave", status: "left" },
    { action: "invite", status: "invited" },
    { action: "revoke", status: "revoked" },
    { action: "accept", status: "accepted" },
    { action: "reject", status: "rejected" },
    { action: "block", status: "blocked" },
  ];

  const current = data.find((value) => value.action === action);
  return current?.status;
};

export interface ICourseMemberData {
  id?: string;
  user_id: string;
  course_id: string;
  course?: ICourseDataResponse;
  channel_id?: string;
  channel?: IChannel;
  role?: TCourseRole;
  operating?: "invite" | "sign_up";
  user?: IUser;
  editor?: IUser;
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
    role: TCourseRole;
    count: number;
  };
}

export interface ICourseMemberDeleteResponse {
  ok: boolean;
  message: string;
  data: boolean;
}

export interface ICourseUser {
  role: TCourseRole;
  channel_id?: string | null;
}
export interface ICourseCurrUserResponse {
  ok: boolean;
  message: string;
  data: ICourseUser;
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
