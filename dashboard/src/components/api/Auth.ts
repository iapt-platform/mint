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

export interface ISignUpRequest {
  token: string;
  username: string;
  nickname: string;
  email: string;
  password: string;
  lang: string;
}
export interface ISignUpVerifyResponse {
  ok: boolean;
  message: string | { email: boolean; username: boolean };
  data: string;
}
export interface ISignInResponse {
  ok: boolean;
  message: string;
  data: string;
}
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

export interface IInviteRequest {
  email: string;
  lang: string;
  studio: string;
  subject?: string;
  dashboard?: string;
}
export interface IInviteResponse {
  ok: boolean;
  message: string;
  data: IInviteData;
}

export interface IInviteData {
  id: string;
  user_uid: string;
  email: string;
  status: string;
  created_at: string;
  updated_at: string;
}
export interface IInviteListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IInviteData[];
    count: number;
  };
}
export interface IInviteResponse {
  ok: boolean;
  message: string;
  data: IInviteData;
}

export type TSoftwareEdition = "basic" | "pro";
