import { IUser } from "../auth/User";
import { TDiscussionType } from "../discussion/Discussion";
import { TContentType } from "../discussion/DiscussionCreate";
import { TResType } from "../discussion/DiscussionListCard";

export interface ICommentRequest {
  id?: string;
  res_id?: string;
  res_type?: string;
  type?: TDiscussionType;
  title?: string;
  content?: string;
  content_type?: TContentType;
  parent?: string;
  tpl_id?: string;
  status?: "active" | "close";
  editor?: IUser;
  created_at?: string;
  updated_at?: string;
}

export interface ICommentApiData {
  id: string;
  res_id: string;
  res_type: TResType;
  type: TDiscussionType;
  title?: string;
  content?: string;
  content_type?: TContentType;
  html?: string;
  summary?: string;
  parent?: string;
  tpl_id?: string;
  status?: "active" | "close";
  children_count?: number;
  editor: IUser;
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
  data: {
    rows: ICommentApiData[];
    count: number;
    active: number;
    close: number;
    can_create: boolean;
    can_reply: boolean;
  };
}
export interface ICommentAnchorResponse {
  ok: boolean;
  message: string;
  data: string;
}
