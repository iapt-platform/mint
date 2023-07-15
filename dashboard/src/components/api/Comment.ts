import { TContentType } from "../discussion/CommentCreate";
import { IUserApiData } from "./Auth";

export interface ICommentRequest {
  id?: string;
  res_id?: string;
  res_type?: string;
  title?: string;
  content?: string;
  content_type?: TContentType;
  parent?: string;
  editor?: IUserApiData;
  created_at?: string;
  updated_at?: string;
}

export interface ICommentApiData {
  id: string;
  res_id: string;
  res_type: string;
  title?: string;
  content?: string;
  content_type?: TContentType;
  parent?: string;
  children_count: number;
  editor: IUserApiData;
  created_at?: string;
  updated_at?: string;
}

export interface ICommentResponse {
  ok: boolean;
  message: string;
  data: ICommentApiData;
}

export interface ICommentListResponse {
  ok: boolean;
  message: string;
  data: { rows: ICommentApiData[]; count: number };
}
export interface ICommentAnchorResponse {
  ok: boolean;
  message: string;
  data: string;
}
