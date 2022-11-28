export type Role = "owner" | "manager" | "editor" | "member";

export interface IUserApiResponse {
  id: string;
  userName: string;
  nickName: string;
  avatar: string;
}

export interface IStudioApiResponse {
  id: string;
  nickName: string;
  studioName: string;
  avatar: string;
  owner: IUserApiResponse;
}
