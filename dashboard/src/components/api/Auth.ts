import { IUser } from "../auth/User";

export type TRole =
  | "owner"
  | "manager"
  | "editor"
  | "member"
  | "reader"
  | "student"
  | "assistant"
  | "unknown";

export interface IUserRequest {
  id?: string;
  userName?: string;
  nickName?: string;
  email?: string;
  avatar?: string;
  roles?: string[];
}

export interface IUserListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IUser[];
    count: number;
  };
}
export interface IUserListResponse2 {
  ok: boolean;
  message: string;
  data: {
    rows: IUserApiData[];
    count: number;
  };
}
export interface IUserResponse {
  ok: boolean;
  message: string;
  data: IUserApiData;
}

export interface IUserApiData {
  id: string;
  userName: string;
  nickName: string;
  email: string;
  avatar?: string;
  avatarName?: string;
  role: string[];
  created_at?: string;
  updated_at?: string;
}

export interface IStudioApiResponse {
  id: string;
  nickName: string;
  studioName?: string;
  realName: string;
  avatar?: string;
  owner: IUser;
}
