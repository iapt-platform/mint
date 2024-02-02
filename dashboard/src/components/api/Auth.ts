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
}

export interface IUserListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IUser[];
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
}

export interface IStudioApiResponse {
  id: string;
  nickName: string;
  studioName?: string;
  realName: string;
  avatar?: string;
  owner: IUser;
}
