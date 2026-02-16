import { IUser } from "../auth/User";

export type TLikeType = "like" | "dislike" | "favorite" | "watch";
export interface ILikeData {
  id: string;
  type: TLikeType;
  target_id: string;
  target_type?: string;
  user: IUser;
  context?: string | null;
  selected?: boolean;
  my_id?: string;
  count?: number;
  updated_at?: string;
  created_at?: string;
}
export interface ILikeCount {
  type: TLikeType;
  selected?: boolean;
  my_id?: string;
  count?: number;
  user: IUser;
}

export interface ILikeListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: ILikeData[];
    count: number;
  };
}

export interface ILikeResponse {
  ok: boolean;
  message: string;
  data: ILikeData;
}

export interface ILikeCountListResponse {
  ok: boolean;
  message: string;
  data: ILikeCount[];
}
export interface ILikeCountResponse {
  ok: boolean;
  message: string;
  data: ILikeCount;
}

export interface ILikeRequest {
  type: TLikeType;
  target_id: string;
  target_type: string;
  user_id?: string;
}
